<?php
namespace ImgManTest\Apigility\Model;

use ImgMan\Apigility\Model\ImgManConnectedResource;
use ImgMan\Core\CoreInterface;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;
use ZF\Rest\Resource;
use ZF\Rest\ResourceEvent;

/**
 * Class ImgManConnectedResourceTest
 */
class ImgManConnectedResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $imgManMock;

    /**
     *
     */
    public function testConstruct()
    {
        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->getMock();

        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertInstanceOf('ImgMan\Apigility\Model\ImgManConnectedResource', $resource);
    }

    /**
     * @depends testConstruct
     */
    public function testUpdate()
    {
        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->getMock();

        $resource = new ImgManConnectedResource($this->imgManMock);
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $event = new ResourceEvent();
        $event->setName('update');
        $event->setParam('id', 'idtest');
        $event->setRequest(new Request());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $resource->dispatch($event));

        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['update', 'grab', 'get'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('update')
            ->with($this->equalTo('id'), $this->equalTo(['blob' => ['data']]))
            ->will($this->returnValue("blob"));

        $this->imgManMock->expects($this->any())
            ->method('grab')
            ->will($this->returnValue("identifier"));

        $this->imgManMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('idtest'))
            ->will($this->returnValue($this->getMock('ImgManTest\Apigility\Asset\TestImage')));

        $resource = new ImgManConnectedResource($this->imgManMock);
        $resource->setEntityClass('stdClass');
        $event = new ResourceEvent();
        $event->setName('update');
        $event->setParam('id', 'idtest');
        $event->setParam('data', ['blob' => 'test']);
        $event->setRequest(new Request());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $resource->dispatch($event));


        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['update', 'grab', 'get'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('update')
            ->with($this->equalTo('id'), $this->equalTo(['blob' => ['data']]))
            ->will($this->returnValue("blob"));

        $this->imgManMock->expects($this->any())
            ->method('grab')
            ->will($this->returnValue("identifier"));

        $this->imgManMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('idtest'))
            ->will($this->returnValue($this->getMock('ImgManTest\Apigility\Asset\TestImage')));

        $resource = new ImgManConnectedResource($this->imgManMock);
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $event = new ResourceEvent();
        $event->setName('update');
        $event->setParam('id', 'idtest');
        $event->setParam('data', ['blob' => 'test']);
        $event->setRequest(new Request());
        $this->assertInstanceOf('ImgMan\Apigility\Entity\ImageEntity', $resource->dispatch($event));
    }

    /**
     * @depends testUpdate
     */
    public function testCreate()
    {
        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['update', 'grab', 'get'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('update')
            ->with($this->equalTo('id'), $this->equalTo(['blob' => ['data']]))
            ->will($this->returnValue("blob"));

        $this->imgManMock->expects($this->any())
            ->method('grab')
            ->will($this->returnValue("identifier"));

        $this->imgManMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('idtest'))
            ->will($this->returnValue($this->getMock('ImgManTest\Apigility\Asset\TestImage')));

        $resource = new ImgManConnectedResource($this->imgManMock);
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $event = new ResourceEvent();
        $event->setName('create');
        $event->setParam('data', ['id' => 'idtest', 'blob' => 'test']);
        $event->setRequest(new Request());
        $this->assertInstanceOf('ImgMan\Apigility\Entity\ImageEntity', $resource->dispatch($event));
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('delete')
            ->with($this->equalTo('testId'))
            ->will($this->returnValue(false));

        $resource = new ImgManConnectedResource($this->imgManMock);
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $event = new ResourceEvent();
        $event->setName('delete');
        $event->setParam('id', 'testId');
        $event->setRequest(new Request());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $resource->dispatch($event));

        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'getRenditions'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('getRenditions')
            ->will($this->returnValue(['testRentdition1' => [], 'testRentdition2' => []]));

        $this->imgManMock->expects($this->any())
            ->method('delete')
            ->with(
                $this->equalTo('testId'),
                $this->logicalOr(
                    CoreInterface::RENDITION_ORIGINAL,
                    $this->equalTo('testRentdition1'),
                    $this->equalTo('testRentdition2')
                )
            )
            ->will($this->returnValue(true));


        $resource = new ImgManConnectedResource($this->imgManMock);
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $event = new ResourceEvent();
        $event->setName('delete');
        $event->setParam('id', 'testId');
        $event->setRequest(new Request());
        $this->assertTrue($resource->dispatch($event));
    }

    /**
     * @depends testConstruct
     */
    public function testFetch()
    {
        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->getMock();

        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $resource->fetch('testId'));

        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getSrc'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('has')
            ->with($this->equalTo('testId'),$this->equalTo(CoreInterface::RENDITION_ORIGINAL))
            ->will($this->returnValue(true));

        $this->imgManMock->expects($this->any())
            ->method('getSrc')
            ->with($this->equalTo('testId'),$this->equalTo(CoreInterface::RENDITION_ORIGINAL))
            ->will($this->returnValue(null));


        $this->imgManMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('testId'))
            ->will($this->returnValue($this->getMock('ImgManTest\Apigility\Asset\TestImage')));

        $resource = new ImgManConnectedResource($this->imgManMock);
        $event = new ResourceEvent();
        $event->setName('fetch');
        $event->setParam('id', 'testId');
        $event->setRequest(new Request());
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $this->assertInstanceOf('ImgMan\Apigility\Entity\ImageEntity', $resource->dispatch($event));
    }


    /**
     * @depends testFetch
     */
    public function _testGetResource()
    {
        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->getMock();

        $resource = new ImgManConnectedResource($this->imgManMock);

        $this->assertNull($resource->getResource());

        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getSrc'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('has')
            ->with($this->equalTo('testId'),$this->equalTo(CoreInterface::RENDITION_ORIGINAL))
            ->will($this->returnValue(true));

        $this->imgManMock->expects($this->any())
            ->method('getSrc')
            ->with($this->equalTo('testId'),$this->equalTo(CoreInterface::RENDITION_ORIGINAL))
            ->will($this->returnValue(null));


        $this->imgManMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo('testId'))
            ->will($this->returnValue($this->getMock('ImgManTest\Apigility\Asset\TestImage')));

        $resource = new ImgManConnectedResource($this->imgManMock);
        $event = new ResourceEvent();
        $event->setTarget(new Resource());
        $event->setName('fetch');
        $event->setParam('id', 'testId');
        $event->setRequest(new Request());
        $resource->setEntityClass('ImgManTest\Apigility\Asset\TestImage');
        $resource->dispatch($event);
        $this->assertInstanceOf('ZF\Rest\ResourceInterface', $resource->getResource());
    }
}



