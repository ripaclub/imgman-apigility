<?php
namespace ImgMan\Apigility\Entity;

use ImgMan\Image\ImageTrait;
use ImgMan\Image\SrcAwareInterface;
use ImgMan\Image\SrcAwareTrait;

/**
 * Class ImageEntity
 */
class ImageEntity implements ImageEntityInterface, SrcAwareInterface
{
    use ImageTrait;
    use SrcAwareTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}