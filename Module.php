<?php
namespace ImgMan\Apigility;

use Solo\Api\Cors\CorsRouteListener;
use Solo\Api\Error\UnhandledExceptionListener;
use Solo\Api\MvcAuth\Authentication\IdentityPostAuthenticationListener;
use Solo\Api\MvcAuth\Authentication\UnauthenticatedListener;
use Solo\Api\MvcAuth\Authorization\DefaultAuthorizationListener;
use Solo\Api\MvcAuth\Authorization\UnauthorizedListener;
use Zend\Console\Console;
use Zend\Http\Header\Accept\FieldValuePart\LanguageFieldValuePart;
use Zend\Http\Header\AcceptLanguage;
use Zend\Http\Response;
use Zend\ModuleManager\Feature\HydratorProviderInterface;
use Zend\ModuleManager\Feature\InputFilterProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;
use Zend\View\Model\JsonModel;
use ZF\Apigility\Provider\ApigilityProviderInterface;
use ZF\Hal\View\HalJsonModel;
use ZF\MvcAuth\MvcAuthEvent;

/**
 * Class Module
 */
class Module
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }
}
