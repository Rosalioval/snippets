const path = require('path');
const uid = require('uid2');
const tmpdir = require('os').tmpdir;
const fs = require('fs');
const aws = require('aws-sdk');
const s3 = new aws.S3({apiVersion: '2006-03-01'}); 

const self = module.exports = {
  "getReadStream": function(reference) {
    var reference = reference;
    return s3.getObject(reference).promise();
  },
  "writeToTmp": function(buffer){
    if(Array.isArray(buffer)){
      var buffers = buffer;
      var results = buffers.map(self.writeToTmp);

      return results;
    }

    var fileName = uid(5);
    var filePath = path.join(tmpdir(), fileName);
    return new Promise(function(resolve, reject){
      fs.writeFile(filePath, buffer, (err) => {
        if(err) {
          return reject(err);
        }
        return resolve(filePath);
      });
    });
  }
}

