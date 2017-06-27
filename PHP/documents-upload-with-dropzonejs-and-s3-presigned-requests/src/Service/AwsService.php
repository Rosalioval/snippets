<?php

namespace KonfioSdk\Services;

class AwsService extends ServiceProvider
{
    private $sdk = null;

    private $sdkConfig = [];

    public function setSdkConfig(Array $sdkConfig)
    {
        $this->sdkConfig = $sdkConfig;

        return $this;
    }

    public function getSdkConfig()
    {
        return $this->sdkConfig;
    }

    /**
     * getSdk
     *
     * Get an Aws\Sdk instance
     *
     * @return Aws\Sdk::class
     */
    public function getSdk()
    {
        return $this->sdk;
    }

    /**
     * setSdk
     *
     * Get an Aws\Sdk instance
     *
     * @param String Aws\Sdk::class || String KonfioSdk\Test\Mock\AwsSdkMock::class
     *
     * @return self
     */
    public function setSdk(String $class)
    {
        $sdkConfig = $this->getSdkConfig();

        $this->sdk = new $class($sdkConfig);

        return $this;
    }
}
