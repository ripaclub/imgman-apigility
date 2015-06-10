<?php
namespace ImgManTest\Apigility\Asset;

use Zend\ServiceManager\AbstractPluginManager as ZfAbstractPluginManager;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class AbstractPluginmanager
 */
class TestPluginManager extends ZfAbstractPluginManager
{
    use ServiceLocatorAwareTrait;

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        // TODO: implement validatePlugin() method
    }
}
