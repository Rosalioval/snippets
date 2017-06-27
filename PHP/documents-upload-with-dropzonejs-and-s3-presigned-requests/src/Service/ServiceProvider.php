<?php

namespace KonfioSdk\Services;

use KonfioSdk\Exceptions\ServiceException;

class ServiceProvider
{
    protected $configPath = null;

    protected $config = null;

    protected $services = [
        'aws' => 'KonfioSdk\Services\AwsService',
        'documents' => 'KonfioSdk\Services\DocumentsService',
    ];

    static $_instances = [];

    static $_instance = null;

    /**
     * __construct
     *
     * In a singleton, the __construct must return void, a child __construct can be used though
     *
     * @return void
     */
    public function __construct()
    {
        $env = getenv('ENV');

        if (get_class($this) == ServiceProvider::class) {
            $this->setInstance();
            $this->registerInstances();
            $this->setConfigPath(dirname(__DIR__, 2) . "/config/serviceprovider.{$env}.json");
        }
    }

    /**
     * addService
     *
     * Adds a new service to the services alias => class property
     *
     * @param String $name; the alias of the service
     *
     * @param String $class; the class of the service
     *
     * @return self
     */
    final public function addService(String $name, String $class)
    {
        $this->services[$name] = $class;

        return $this;
    }

    /**
     * getServices
     *
     * Returns the default services array
     *
     * @return Array $services
     */
    public function getServices(): Array
    {
        return $this->services;
    }

    /**
     * getService
     *
     * Returns a ServiceProvider child that uses the parent singleton configuration
     *
     * @param String <Namespace>Service
     *
     * @param Array $args = []; optional arguments passed to the child __construct
     *
     * @return ServiceProvider::child || null
     */
    final public function getService(String $name, Array $args = [])
    {
        $services = $this->getServices();

        if (!in_array($name, array_keys($services))) {
            throw new ServiceException("Service [$name] is not an instance of KonfioSdk\Services\ServiceProvider", 0);
        }

        if (self::$_instances[$name] != null) {
            return self::$_instances[$name];
        }

        $class = $services[$name];

        self::$_instances[$name] = new $class($args);

        return self::$_instances[$name];
    }

    /**
     * setEmptyInstance
     *
     * Sets the $_instances key with a null value
     *
     * @param String $name; the service alias
     *
     * @return ServiceProvider
     */
    public function setEmptyInstance(String $name)
    {
        self::$_instances[$name] = null;

        return $this;
    }

    /**
     * registerInstances
     *
     * Initializes the $_instances array with null service names that will become instantiated singletons on $this->getService($name)
     *
     * @return ServiceProvider
     */
    public function registerInstances()
    {
        $services = $this->getServices();

        foreach ($services as $name => $class) {
            if (!$this->instanceIsSet($name)) {
                $this->setEmptyInstance($name);
            }
        }

        return $this;
    }

    /**
     * instanceIsSet
     *
     * Returns a ServiceProvider child instance
     *
     * @param String $name; the alias of the service
     *
     * @return ServiceProvider
     */
    final public function instanceIsSet(String $name): bool
    {
        return isset(self::$_instances[$name]);
    }

    /**
     * getInstance
     *
     * Returns a ServiceProvider child instance
     *
     * @param String $name; the alias of the service
     *
     * @return ServiceProvider
     */
    final public function getInstance(String $name)
    {
        return self::$_instances[$name];
    }

    /**
     * getInstances
     *
     * Returns the ServiceProvider registered instances
     *
     * @return ServiceProvider
     */
    final public function getInstances()
    {
        return self::$_instances;
    }

    /**
     * setInstance
     *
     * Sets a new ServiceProvider singleton instance if null
     *
     * @return ServiceProvider
     */
    final public function setInstance()
    {
        if (!self::$_instance) {
            self::$_instance = $this;
        }

        return self::$_instance;
    }

    /**
     * setConfigPath
     *
     * Defines the path of the config file
     *
     * @param String $path
     *
     * @return self
     */
    public function setConfigPath(String $path)
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            throw new \Exception("No content for path: $path");
        }

        self::$_instance->configPath = $path;

        return self::$_instance;
    }

    /**
     * getServiceConfig
     *
     * Returns a config array from a JSON or PHP file stored in a framework using the SDK
     *
     * NOTE: The file must be called serviceprovider.ENV.*
     *
     * See ./tests/config/serviceprovider.dev.json for an example file structure
     *
     * @return Array $config
     */
    public function getServiceConfig(String $path): Array
    {
        $path = self::$_instance->configPath;

        return $this->getConfigByFileExtension($path);
    }

    /**
     * getConfigByFileExtension
     *
     * Returns a config array depending on the file extension. Either .json or .php
     *
     * @param String $path; the config file path
     *
     * @return Array $config;
     */
    public function getConfigByFileExtension(String $path = null): Array
    {
        $config = [];

        if (substr($path, -5) == '.json') {
            return json_decode(file_get_contents($path), true);
        }

        if (substr($path, -4) == '.php') {
            return require $path;
        }

        return $config;
    }

    /**
     * setConfig
     *
     * Sets a config array
     *
     * @return self
     */
    public function setConfig($value): ServiceProvider
    {
        self::$_instance->config = $value;

        return self::$_instance;
    }

    /**
     * getConfig
     *
     * Returns a config array stored in a framework using the SDK
     *
     * NOTE: If the config file is called serviceprovider.*.* see getServiceConfig
     *
     * @return Array $config
     */
    public function getConfig(): Array
    {
        if (self::$_instance->config != null) {
            return self::$_instance->config;
        }

        $path = self::$_instance->configPath;

        if (preg_match('/serviceprovider/', $path)) {
            return self::$_instance->getServiceConfig($path);
        }

        return $this->getConfigByFileExtension($path);
    }
}
