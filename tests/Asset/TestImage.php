<?php
/**
 * Created by PhpStorm.
 * User: visa
 * Date: 17/05/16
 * Time: 11.20
 */

namespace ImgManTest\Apigility\Asset;

use ImgMan\Apigility\Entity\ImageEntityInterface;
use ImgMan\Image\ImageTrait;

class TestImage implements ImageEntityInterface
{
    use ImageTrait;

    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}