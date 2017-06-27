const aws = require('aws-sdk');
const s3 = new aws.S3({apiVersion: '2006-03-01'}); 
const exec = require('child_process').exec;
const path = require('path');
const fs = require('fs');
const defaultParams = require('./defaultParams.json');
const FileReference = require('./FileReference.js');
const tmpdir = require('os').tmpdir;

/*
 *
 *  parseCmdString
 *
 *  Extract from the event(the passed in params) all the
 *  relevant parameters to run the ghostschript command
 *
 * */
function parseCmdString(cmdParams) {
  var params = cmdParams.params;
  var outPutFileParam = cmdParams.outFileCmd.concat(path.join(tmpdir(),cmdParams.outFileName));

  // Set the commands binary name as the first param
  params.unshift(cmdParams.name);
  params.push(outPutFileParam);
  return params.join(" "); 
}

function getFilePaths(writeStreams) {
  return writeStreams.map(console.log);
}

/*
 *  prepExecEnv
 *
 *  prepares the command to execute and returns
 *  a function where all it needs to execute the command
 *  is the array of filepaths of pdfs to merge
 *
 *  @param cmdParams array of pds files to merge
 *
 *  @return String the path of where the output pdf was saved
 * */
function prepExecEnv(cmdParams, callback) {
  const cmdString = parseCmdString(cmdParams);

  return function(filePaths) {
    var inputFileParams = filePaths.join(" ");
    var completeCmdString = `${cmdString} ${inputFileParams}`;

    console.log("Complete command string: ");
    console.log(completeCmdString);

    const gs = exec(completeCmdString);
    gs.stdout.on('data', console.log);
    gs.stderr.on('data', console.error);

    return new Promise( (resolve, reject) => {
      gs.on('exit', (err) => {
        if(err) {
          return reject(err);
        }
        var outputFilePath = path.join(tmpdir(), cmdParams.outFileName);
        return resolve(outputFilePath);
      });
    });
  }
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
  return function(filePath) {
    uploadParams.Body = fs.createReadStream(filePath);
    return s3.putObject(uploadParams).promise();
  }
}

exports.handler = (event, context, callback) => {
  var cmdParams = Object.assign(defaultParams.cmd, event.cmd);
  var uploadParams = Object.assign(defaultParams.upload, event.upload);
  var createPdf = prepExecEnv(cmdParams, callback);
  var uploadToS3 = prepS3UploadFromParams(uploadParams);

  // get Files references
  const files = event.files;
  
  var getFiles = files.map((ref) => {
    return FileReference.getReadStream(ref);
  });

  Promise.all(getFiles)
    .then(function(responses){
      return responses.map((res) => res.Body);
    })
    .then(FileReference.bufferToReadStream)
    .then(FileReference.writeToTmp)
    .then((p) => Promise.all(p))
    .then(createPdf)
    .then(uploadToS3)
    .then( (uploadResult) => {
      callback(null, uploadResult);
    })
    .catch(callback);
};
