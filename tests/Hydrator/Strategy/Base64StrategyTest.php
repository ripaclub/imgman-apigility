<?php
namespace ImgManTest\Apigility\Hydrator\Strategy;

use ImgMan\Apigility\Hydrator\Strategy\Base64Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Created by PhpStorm.
 * User: visa
 * Date: 18/05/16
 * Time: 18.35
 */
class Base64StrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testConstruct()
    {
        $strategy = new Base64Strategy();
        $value = 'test';
        $base64 = $strategy->hydrate($value);
        $this->assertSame($value, $strategy->extract($base64));
    }
}