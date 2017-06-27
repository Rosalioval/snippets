const aws = require('aws-sdk');
const defaultParams = require('./defaultParams.json');
const s3 = new aws.S3({apiVersion: '2006-03-01'}); 
const s3Get = require('./getS3Files');
const tmpdir = require('os').tmpdir;
const uid2 = require('uid2');
const path = require('path');
const im = require('imagemagick');
const fs = require('fs');
const readChunk = require('read-chunk');
const fileType = require('file-type');

function convertImagesToPdf(filePaths) {
  const outFile = path.format({
    "dir": tmpdir(),
    "base": uid2(5)+".pdf"
  });

  if(filePaths.length === 0) {
    return Promise.resolve([]);
  }

  /*
   * imagemagick wants to have the outfile as the last parameter in the array so we push it in
   *  see the documentation at https://github.com/rsms/node-imagemagick#convertargs-callbackerr-stdout-stderr
   */
  filePaths.push(outFile);

  return new Promise( function(resolve, reject){
    im.convert(filePaths, (error) => {
      if(error){
        console.error(`Error returned by imagemagick: ${error}`);
        return reject(error);
      }
      return resolve([outFile]);
    });
  })
}

function getFileBuffers(s3Responses){
  return s3Responses.map((response) => response.Body);
}

function waitForAll(p){
  return Promise.all(p);
}

/*
 *  prepS3UploadFromParams
 *
 *  prepares the upload to s3 function from the
 *  parameters passed initially to the function by creating
 *  a function to use later in the promise chain
 *
 *  @param uploadParams parameters defining where to upload the resulting pdf
 *
 *  @return Promise returned by the native aws sdk
 * */
function prepS3UploadFromParams(uploadParams) {
  /*
   *  Takes in an array of the files to upload to S3
   * */
  return function(filePaths) {
    if(filePaths.length === 0) {
      console.log(`No filepaths passed to upload to S3`);
      return Promise.resolve([]);
    }
    
    return filePaths.map( (filePath) => {
      var params = Object.assign({}, uploadParams, {Body: fs.createReadStream(filePath) });
      return s3.putObject(params).promise();
    });
  }
}

function isPdf(filePath) {
  const buffer = readChunk.sync(filePath, 0, 262);
  var fileInfo = fileType(buffer);
  return fileInfo.ext === 'pdf';
}

/*
 *  prepZipS3ObjectsToPaths
 * */
prepZipS3ObjectsToPaths = function(s3Objects){
  return function(paths) {
    return s3Objects.map( (s3Object, i) => {
      s3Object.localPath = paths[i];
      return s3Object;
    })
  }
};

exports.handler = (event, context, callback) => {
  console.log("Sent params");
  console.log(event);

  var params = Object.assign(defaultParams, event);

  console.log("Calculated params (default + sent)");
  console.log(params);

  var s3FileObjects = params.files;
  var downloadPromises = s3FileObjects.map(s3Get.getReadStream);
  var uploadToS3 = prepS3UploadFromParams(params.upload);
  var zipS3ObjectsToPaths = prepZipS3ObjectsToPaths(s3FileObjects);
  var s3Objects = [];

  Promise.all(downloadPromises)
    .then(getFileBuffers)
    .then(s3Get.writeToTmp)
    .then(waitForAll)
    .then(zipS3ObjectsToPaths)
    .then( (downloadedFiles) => {

      // Here we'll add to each object if they're a pdf or not
      s3Objects = downloadedFiles.map( (s3Object) => {
        s3Object.isPdf = isPdf(s3Object.localPath);
        return s3Object;
      });

      return s3Objects;
    })
    .then((s3Objects) => {
      var filesToMerge = s3Objects
        .filter((s3Obj) => !s3Obj.isPdf)
        .map((s3Obj) => s3Obj.localPath);

      return filesToMerge;      
    })
    .then(convertImagesToPdf)
    .then(uploadToS3)
    .then(waitForAll)
    .then( (uploadResult) => {
      var pdfS3Objects = s3Objects.filter((x) => x.isPdf);
      pdfS3Objects = pdfS3Objects.map((x) => {
        return {
          "Bucket": x.Bucket,
          "Key": x.Key
        }
      });

      /*
       *  If a file was uploaded, add its S3 path to the results
       * */
      if(uploadResult.length > 0) {
        pdfS3Objects.push(params.upload);
      }

      var result = pdfS3Objects;
      console.log(`Returning result: ${result}`);

      callback(null, result);
    })
    .catch(callback)
};

