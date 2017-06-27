<?php

namespace KonfioSdk\Test\Mock;

class AwsSdkMock
{
    public function sdk()
    {
        return $this;
    }

    public function createS3()
    {
        return new S3ClientMock;
    }

    public function createLambda()
    {
        return new LambdaClientMock;
    }
}
