<?php
namespace ImgMan\Apigility\Model;

use ImgMan\Apigility\Entity\ImageEntityInterface;
use ImgMan\Core\Blob\Blob;
use ImgMan\Core\CoreInterface;
use ImgMan\Image\ImageInterface;
use ImgMan\Service\ImageService as ImageManager;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentLength;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Http\Response;
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

        /** @var $image ImageInterface */
        $image = $this->imageManager->get($id, $rendition);
        if ($image) {

            if ($this->hasToBeRendered($image)) {
                return $this->getHttpImageResponse($image);
            } else {
                $entity = $this->getEntityClassInstance();
                if (!$entity instanceof ImageEntityInterface) {
                    return new ApiProblem(500, 'Entity class must be configured');
                }

                $entity->setId($id);
                $entity->setSize($image->getSize());
                $entity->setMimeType($image->getMimeType());
                $entity->setBlob(base64_encode($image->getBlob()));
                return $entity;
            }
        }

        return new ApiProblem(404, 'Image not found');
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return string|null
     */
    public function update($id, $data)
    {
        $blob = $this->searchBlob($data);
        if ($blob instanceof ApiProblem) {
            return $blob;
        }
        return $this->imageManager->grab($blob, $id);
    }

    /**
     * @param mixed $id
     * @return ApiProblem
     */
    public function delete($id)
    {
        $image = $this->imageManager->get($id, $rendition);

        /** @var $image ImageInterface */
        if ($image) {
            $this->imageManager->delete($id);
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
            if ($key === 'blob') {
                $blob = new Blob();
                $blob->setBlob($value);
                return $blob;
            }
        }

        return new ApiProblem(400, 'File not found in upload request');
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
    protected function getHttpImageResponse(ImageInterface $image)
    {
        $response = new Response();
        $response->setContent($image->getBlob());
        $response->getHeaders()->addHeader(new ContentLength(strlen($image->getBlob())));
        $response->getHeaders()->addHeader(new ContentType($image->getMimeType()));
        return $response;
    }

    /**
     * @return mixed
     */
    protected function getEntityClassInstance()
    {
        $entityClass = $this->getEntityClass();
        return new $entityClass;
    }
}
