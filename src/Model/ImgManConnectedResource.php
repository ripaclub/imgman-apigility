<?php
namespace ImgMan\Apigility\Model;

use ImgMan\Apigility\Entity\ImageEntityInterface;
use ImgMan\Core\Blob\Blob;
use ImgMan\Core\CoreInterface;
use ImgMan\Image\ImageInterface;
use ImgMan\Image\Image;
use ImgMan\Image\SrcAwareInterface;
use ImgMan\Service\ImageService as ImageManager;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentLength;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\Hydrator\ClassMethods;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\ResourceInterface;

/**
 * Class ImgManConnectedResource
 */
class ImgManConnectedResource extends AbstractResourceListener
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var string
     */
    protected $idName = 'id';

    /**
     * @var string
     */
    protected $blobName = 'blob';


    /**
     * Ctor
     *
     * @param ImageManager $imageManager
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * Retrieve resource
     *
     * @return object|string|null
     */
    public function getResource()
    {
        if ($this->getEvent() && $this->event->getTarget() instanceof ResourceInterface) {
            return $this->event->getTarget();
        }

        return null;
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $rendition = CoreInterface::RENDITION_ORIGINAL;
        if ($this->getEvent()) {
            $rendition = $this->getEvent()->getQueryParam('rendition', CoreInterface::RENDITION_ORIGINAL);
        }

        $src = $this->imageManager->getSrc($id, $rendition);
        if ($src) {
            return $this->getRedirectResponse($src);
        }

        $hasImage = $this->imageManager->has($id, $rendition);
        if ($hasImage) {

            $image = $this->imageManager->get($id, $rendition);
            if ($this->hasToBeRendered($image)) {
                return $this->getHttpResponse($image);
            } else {

                return $this->getApigilityResponse($image, $id);
            }
        }

        return new ApiProblem(404, 'Image not found');
    }

    /**
     * @param mixed $data
     * @return ImageInterface
     */
    public function create($data)
    {
        $id = $this->searchId($data);
        if ($id instanceof ApiProblem) {
            return $id;
        }
        return $this->update($id, $data);
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return ImageInterface
     */
    public function update($id, $data)
    {
        $blob = $this->searchBlob($data);
        if ($blob instanceof ApiProblem) {
            return $blob;
        }

        $this->imageManager->grab($blob, $id);

        $image = $this->imageManager->get($id);

        $entity = $this->getEntityClassInstance();
        if (!$entity instanceof ImageEntityInterface)
        {
            return new ApiProblem(500, 'Entity class must be configured');
        }

        /** @var $entity ImageEntityInterface */
        $entity->setId($id);
        $hydrator = new ClassMethods();
        $data = $hydrator->extract($image);
        $hydrator->hydrate($data, $entity);

        return $entity;
    }

    /**
     * @param mixed $id
     * @return ApiProblem|boolean
     */
    public function delete($id)
    {
        $renditions = $this->imageManager->getRenditions();
        $result = $this->imageManager->delete($id, CoreInterface::RENDITION_ORIGINAL);
        foreach ($renditions as $rendition => $options) {
            $deleteRendition = $this->imageManager->delete($id, $rendition);
            $result = $result || $deleteRendition;
        }

        if ($result) {
            return $result;
        }

        return new ApiProblem(404, 'Image not found');  
    }


    /**
     * @param $data
     * @return \ImgMan\Core\Blob\Blob|\ZF\ApiProblem\ApiProblem
     */
    protected function searchBlob($data)
    {
        $data = $this->retrieveData($data);

        $iterator = new \RecursiveArrayIterator($data);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($recursive as $key => $value) {
            if ($key === $this->getBlobName()) {
                switch (true) {
                    case is_array($value)  && isset($value['tmp_name']) :
                        return new Image($value['tmp_name']);
                    case is_string($value) :
                        return (new Blob())->setBlob($value);
                }
            }
        }
        return new ApiProblem(400, 'File not found in upload request');
    }

    protected function searchId($data)
    {
        if (is_array($data) && isset($data[$this->getIdName()])) {
            return $data[$this->getIdName()];
        }

        if (is_object($data) && property_exists($data, $this->getIdName()) ) {
            return $data->{$this->getIdName()};
        }

        $routerMatch = $this->getResource()->getRouteMatch();
        if ($routerMatch && $routerMatch instanceof RouteMatch && $routerMatch->getParam($this->getIdName())) {
            return $routerMatch->getParam($this->getIdName());
        }

        return new ApiProblem(400, sprintf('%s not found in upload request', $this->getIdName()));
    }

    /**
     * Retrieve data
     *
     * Retrieve data from composed input filter, if any; if none, cast the data
     * passed to the method to an array.
     *
     * @param mixed $data
     * @return array
     */
    protected function retrieveData($data)
    {
        $filter = $this->getInputFilter();
        if (null !== $filter) {
            return $filter->getValues();
        }
        return (array)$data;
    }

    /**
     * Wheter the image has to be rendered or not
     *
     * This choice depends on headers
     *
     * @param ImageInterface $image
     * @return bool|Response
     */
    protected function hasToBeRendered(ImageInterface $image)
    {
        $request = $this->getEvent()->getRequest();
        if ($request instanceof Request) {
            $headers = $request->getHeaders();
            if (
                $headers->has('Accept') &&
                ($accept = $headers->get('Accept')) &&
                $accept instanceof Accept &&
                $accept->match($image->getMimeType())
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ImageInterface $image
     * @return Response
     */
    protected function getHttpResponse(ImageInterface $image)
    {
        $response = new Response();
        $response->setContent($image->getBlob());
        $response->getHeaders()->addHeader(new ContentLength(strlen($image->getBlob())));
        $response->getHeaders()->addHeader(new ContentType($image->getMimeType()));
        return $response;
    }

    /**
     * @param $src
     * @return Response
     */
    protected function getRedirectResponse($src)
    {
        $response = new Response();
        $response->getHeaders()->addHeaderLine('Location', $src);
        $response->setStatusCode(302);
        return $response;
    }

    /**
     * @param ImageInterface $image
     * @param $id
     * @return mixed|ApiProblem
     */
    protected function getApigilityResponse(ImageInterface $image, $id)
    {
        $entity = $this->getEntityClassInstance();
        if (!$entity instanceof ImageEntityInterface) {
            return new ApiProblem(500, 'Entity class must be configured');
        }
        /** @var $image ImageInterface */
        $entity->setId($id);
        $entity->setSize($image->getSize());
        $entity->setMimeType($image->getMimeType());
        $entity->setBlob(base64_encode($image->getBlob()));
        return $entity;
    }

    /**
     * @return mixed
     */
    protected function getEntityClassInstance()
    {
        $entityClass = $this->getEntityClass();
        return new $entityClass;
    }

    /**
     * @return string
     */
    public function getIdName()
    {
        return $this->idName;
    }

    /**
     * @param string $idName
     * @return $this
     */
    public function setIdName($idName)
    {
        $this->idName = $idName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlobName()
    {
        return $this->blobName;
    }

    /**
     * @param string $blobName
     * @return $this
     */
    public function setBlobName($blobName)
    {
        $this->blobName = $blobName;
        return $this;
    }
}
