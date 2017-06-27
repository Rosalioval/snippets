<?php

namespace KonfioSdk\Test\Mock;

use GuzzleHttp\Psr7;

class S3ClientMock
{
    protected $operations = [
        'PutObject'
    ];

    public function getCommand(String $operation, Array $args = [])
    {
        if (!in_array($operation, $this->operations)) {
            throw new \InvalidArgumentException("Unknown operation: $operation");
        }

        if (!array_key_exists('Bucket', $args)) {
            throw new \InvalidArgumentException("Operation argument needs a [Bucket] keyname with a valid value");
        }

        if (!array_key_exists('Key', $args)) {
            throw new \InvalidArgumentException("Operation argument needs a [Key] keyname with a valid value");
        }

        return new AwsSdkCommandMock;
    }

    public function createPresignedRequest(AwsSdkCommandMock $cmd, $expire)
    {
        return new Psr7\Request('PUT', 'awssdk://put/uri');
    }
}
