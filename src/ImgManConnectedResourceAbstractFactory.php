<?php
namespace ImgMan\Apigility;

use ImgMan\Service\ImageService;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ImgManConnectedResourceAbstractFactory
 */
class ImgManConnectedResourceAbstractFactory implements AbstractFactoryInterface
{

    /**
     * Config
     *
     * @var array
     */
    protected $config;

    /**
     * Config Key
     *
     * @var string
     */
    protected $moduleConfigKey = 'imgman-apigility';

    /**
     * Config Key
     *
     * @var string
     */
    protected $configKey = 'imgman-connected';

    /**
     * Default model class name
     *
     * @var string
     */
    protected $resourceClass = '\ImgMan\Apigility\Model\ImgManConnectedResource';

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        return (
            isset($config[$requestedName]) &&
            is_array($config[$requestedName]) &&
            !empty($config[$requestedName]) &&
            isset($config[$requestedName]['service']) &&
            is_string($config[$requestedName]['service']) &&
            $serviceLocator->has($config[$requestedName]['service'])
        );
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $this->getConfig($serviceLocator)[$requestedName];
        $imgManService = $serviceLocator->get($config['service']);

        if (!$imgManService instanceof ImageService) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'service must be a instance of ImgMan\Service\ImageService given: %s',
                    is_object($imgManService) ? get_class($imgManService) : gettype($imgManService)
                )
            );
        }

        $resourceClass = $this->getResourceClassFromConfig($config, $requestedName);

        /* @var $resource \ImgMan\Apigility\Model\ImgManConnectedResource */
        $resource = new $resourceClass($imgManService);

        if (isset($config['idName'])) {
            $resource->setIdName($config['idName']);
        }

        if (isset($config['blobName'])) {
            $resource->setBlobName($config['blobName']);
        }

        if (isset($config['renderBlob'])) {
            $resource->setRenderBlob($config['renderBlob']);
        }

        return $resource;
    }


    /**
     * @param array $config
     * @param $requestedName
     * @return string
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function getResourceClassFromConfig(array $config, $requestedName)
    {
        $resourceClass = isset($config['resource_class']) ? $config['resource_class'] : $this->resourceClass;
        if (
            $resourceClass !== $this->resourceClass &&
            (
                !class_exists($resourceClass) ||
                !is_subclass_of($resourceClass, 'ZF\Rest\AbstractResourceListener')
            )
        ) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'Unable to create instance for service "%s"; '
                    . 'resource class "%s" cannot be found or does not extend "%s"',
                    $requestedName,
                    $resourceClass,
                    $this->resourceClass
                )
            );
        }
        return $resourceClass;
    }


    /**
     * Get model configuration, if any
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (
            !isset($config[$this->moduleConfigKey]) ||
            !isset($config[$this->moduleConfigKey][$this->configKey]) ||
            !is_array($config[$this->moduleConfigKey][$this->configKey])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->moduleConfigKey][$this->configKey];
        return $this->config;
    }
}
