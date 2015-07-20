<?php
namespace ImgMan\Apigility\Entity;

use ImgMan\Image\ImageInterface;

/**
 * Interface ImageEntityInterface
 */
interface ImageEntityInterface extends ImageInterface
{
    /**
     * @param $id string
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getId();
}