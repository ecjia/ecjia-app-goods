<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 14:35
 */

namespace Ecjia\App\Goods\GoodsImage\Format;


class ProductImageFormatted extends GoodsImageFormatted
{

    protected $type = 'product';

    public function __construct($goods_image)
    {
        parent::__construct($goods_image);

        $this->goods_source_postion = $this->filePathPrefix('product_source_img/') . $this->spliceFileName();
        $this->goods_img_postion = $this->filePathPrefix('product_img/') . $this->spliceFileName();
        $this->goods_thumb_postion = $this->filePathPrefix('product_thumb_img/') . $this->spliceFileName(true);

    }


}