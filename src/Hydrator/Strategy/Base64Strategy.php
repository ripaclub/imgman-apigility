<?php
namespace ImgMan\Apigility\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Created by PhpStorm.
 * User: visa
 * Date: 18/05/16
 * Time: 18.35
 */
class Base64Strategy implements StrategyInterface
{
    public function extract($value)
    {
        return base64_encode($value);
    }

    public function hydrate($value)
    {
        return base64_decode($value);
    }

}