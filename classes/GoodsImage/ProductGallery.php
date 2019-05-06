<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 09:28
 */

namespace Ecjia\App\Goods\GoodsImage;


use Ecjia\App\Goods\GoodsImage\Format\ProductGalleryFormatted;

class ProductGallery extends GoodsImage
{


    public function __construct($id, $fileinfo = null)
    {
        parent::__construct($id, $fileinfo);


        $this->image_format = new ProductGalleryFormatted($this);
    }



}