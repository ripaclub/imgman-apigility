<?php
namespace ImgMan\Apigility\Model;

use ImgMan\Core\Blob\Blob;
use ImgMan\Core\CoreInterface;
use ImgMan\Service\ImageService as ImageManager;
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


    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }


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

        $image = $this->imageManager->get($id, $rendition);

        if ($image) {
            $data = $image->getBlob();

            // TODO: temporary workaround

            header('Content-Type: ' . $image->getMimeType());
            header('Content-Length: ' . strlen($data));
            echo $data;
            exit;
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
        return (array) $data;
    }
}
