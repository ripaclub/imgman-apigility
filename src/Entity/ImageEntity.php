<?php
namespace ImgMan\Apigility\Entity;

use ImgMan\Image\ImageInterface;
use ImgMan\Image\ImageTrait;
use ImgMan\Image\SrcAwareInterface;
use ImgMan\Image\SrcAwareTrait;
use Matryoshka\Model\Object\IdentityAwareInterface;
use Matryoshka\Model\Object\IdentityAwareTrait;

/**
 * Class ImageEntity
 */
class ImageEntity implements ImageInterface, SrcAwareInterface, IdentityAwareInterface
{
    use ImageTrait;
    use SrcAwareTrait;
    use IdentityAwareTrait;
}