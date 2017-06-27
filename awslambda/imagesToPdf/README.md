# imagesToPdfs
Merges images into a single pdf. The output of this function will be the paht of where the file was saved in s3. If
you try to merge a pdf with this funcion, that pdf will not be merged but instead its S3 included as part of the output.

## Parameters

```json
{
  "ext": ".pdf",
  "upload": {
    "Bucket": "somebucket",
    "Key": "key/where/to/save/output/file.pdf"
  },
  "files": [
    {
      "Bucket": "tempBucket",
      "Key": "/path/to/some/file"
    },
    {
      "Bucket": "tempBucket",
      "Key": "/path/to/some/file"
    },
    {
      "Bucket": "tempBucket",
      "Key": "/path/to/some/file"
    }
  ]
}  
```

`ext`: "The extension to give to the file during the processinf phase.".

`upload`: "An s3 file object, you can add any parameters here you would normally add to an S3 upload operation".

`files`: "An array of S3 Objects (bucket and key), to merge into a single pdf".

### Testing: 

`npm install node-lambda`

`imagemagick` bins are required in your OS