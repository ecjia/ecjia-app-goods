<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 18:36
 */

namespace Ecjia\App\Goods\GoodsImage;

use ecjia;
use RC_Image;
use RC_File;

class MakeGoodsThumbImage
{

    protected $path;

    protected $thumb_width;

    protected $thumb_height;

    public function __construct($path)
    {
        $this->path = $path;

        $this->thumb_width = ecjia::config('thumb_width');
        $this->thumb_height = ecjia::config('thumb_height');
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function setSize($width, $height)
    {
        $this->thumb_width = $width;
        $this->thumb_height = $height;

        return $this;
    }

    /**
     * 生成
     */
    public function make()
    {
        // 修改指定图片的大小
        $image = RC_Image::make($this->path)->resize($this->thumb_width, $this->thumb_height);

        $ext = RC_File::extension($this->path);

        $data = $image->encode($ext, 75);

        return $data;
    }


}