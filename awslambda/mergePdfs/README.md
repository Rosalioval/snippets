# Merge Pdfs
This projects goal is simple, merge pdfs!

The pdfs will be pulled from S3 and uploaded to S3. So as an input, it will take in an array of S3 keys (wich should point to pdfs), and as output you will get the key pointing to the new pdf.

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

`upload`: "An s3 file object, you can add any parameters here you would normally add to an S3 upload operation".

`files`: "An array of S3 Objects (bucket and key), to merge into a single pdf".
