<?php
return [
    'service_manager' => [
        'abstract_factories' => [
            'ImgMan\Apigility\ImgManConnectedResourceAbstractFactory',
            'ImgMan\Service\ImageServiceAbstractFactory',
            'ImgMan\Storage\Adapter\Mongo\MongoDbAbstractServiceFactory',
            'ImgMan\Storage\Adapter\Mongo\MongoAdapterAbstractServiceFactory'
        ],
        'factories' => [
            'ImgMan\PluginManager' => 'ImgMan\Operation\HelperPluginManagerFactory',
        ],
        'invokables' => [
            'ImgMan\Adapter\Imagick'  => 'ImgMan\Core\Adapter\ImagickAdapter',
        ]
    ]
];