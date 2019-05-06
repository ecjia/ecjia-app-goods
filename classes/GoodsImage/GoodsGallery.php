<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 09:28
 */

namespace Ecjia\App\Goods\GoodsImage;


use Ecjia\App\Goods\GoodsImage\Format\GoodsGalleryFormatted;

class GoodsGallery extends GoodsImage
{

    /**
     * 设置是否自动生成缩略图
     * @var bool
     */
    protected $auto_generate_thumb = true;

    public function __construct($id, $fileinfo = null)
    {
        parent::__construct($id, $fileinfo);


        $this->image_format = new GoodsGalleryFormatted($this);
    }



}