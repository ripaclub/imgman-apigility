<?php
namespace ImgManTest\Apigility\Model;

use ImgMan\Apigility\Model\ImgManConnectedResource;
use PHPUnit_Framework_TestCase;

/**
 * Class ImgManConnectedResourceTest
 */
class ImgManConnectedResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $imgManMock;

    protected $imageMock;

    public function setUp()
    {
        $this->imageMock = $this->getMockBuilder('ImgMan\Image\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getBlob', 'getMimeType'])
            ->getMock();

        $this->imageMock->expects($this->any())
            ->method('getBlob')
            ->will($this->returnValue('test'));

        $this->imageMock->expects($this->any())
            ->method('getMimeType')
            ->will($this->returnValue('image/jpeg'));

        $this->imgManMock = $this->getMockBuilder('ImgMan\Service\ImageService')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'grab'])
            ->getMock();

        $this->imgManMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(null));

        $this->imgManMock->expects($this->any())
            ->method('grab')
            ->will($this->returnValue('12345'));
    }

    /**
     *
     */
    public function testConstruct()
    {
        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertInstanceOf('ImgMan\Apigility\Model\ImgManConnectedResource', $resource);
    }

    /**
     * @depends testConstruct
     */
    public function testGetResource()
    {
        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertNull($resource->getResource());
    }

    /**
     * @depends testConstruct
     */
    public function testUpdate()
    {
        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertSame('12345', $resource->update('12', [['blob' => 'test']]));
    }

    /**
     * @depends testUpdate
     */
    public function testUpdateApiProblem()
    {
        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $resource->update('12', []));
    }

    /**
     * @depends testConstruct
     */
    public function testFetch()
    {
        $resource = new ImgManConnectedResource($this->imgManMock);
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $resource->fetch('12'));

        $this->imgManMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->imageMock));
    }
}
