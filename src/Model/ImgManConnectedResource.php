<?php
namespace ImgMan\Apigility\Model;

use ImgMan\Core\Blob\Blob;
use ImgMan\Core\CoreInterface;
use ImgMan\Service\ImageService as ImageManager;
use Zend\Http\Response;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\ResourceInterface;

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
        $resource = $this->event->getTarget();
        if ($resource instanceof ResourceInterface) {
            return $resource;
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
        $rendition = $this->event->getQueryParam('rendition', CoreInterface::RENDITION_ORIGINAL);

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

    public function update($id, $data)
    {
        $blob = $this->retriveBlob($data);

        if (!$blob instanceof Blob) {
            return new ApiProblem(400, 'File not found in upload request');
        }

        $this->imageManager->grab($blob, $id);
    }


    protected function retriveBlob($data)
    {
        $data = $this->retrieveData($data);

        foreach ($data as $key => $file) {
            if (!isset($file['blob'])) {
                return new ApiProblem(400, 'File not found in upload request');
            }

            $blob = new Blob();
            $blob->setBlob($file['blob']);
            return $blob;
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
