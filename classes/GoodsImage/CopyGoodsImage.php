<?php


namespace Ecjia\App\Goods\GoodsImage;

use Ecjia\App\Goods\GoodsImage\Format\GoodsImageFormatted;
use RC_File;
use RC_Storage;
use League\Flysystem\FileNotFoundException;

class CopyGoodsImage implements GoodsImageFormattedInterface
{
    protected $goods_id;
    protected $product_id;

    protected $extension_name;

    public function __construct($goods_id, $product_id = 0)
    {
        $this->goods_id = $goods_id;
        $this->product_id = $product_id;
    }


    public function getGoodsId()
    {
        return $this->goods_id;
    }

    public function getProductId()
    {
        return $this->product_id;
    }

    public function getExtensionName()
    {
        return $this->extension_name;
    }

    /**
     * 复制商品图片，三张一起操作
     *
     * @param string $original_path    原图
     * @param string $img_path         商品图片
     * @param string $thumb_path       缩略图片
     *
     * @return []
     */
    protected function copyGoodsImage($original_path, $img_path, $thumb_path)
    {
        $this->extension_name = RC_File::extension($original_path);

        $image_format = new GoodsImageFormatted($this);

        $new_original_path = $image_format->getSourcePostion();
        $new_img_path = $image_format->getGoodsimgPostion();
        $new_thumb_path = $image_format->getThumbPostion();

        $disk = RC_Storage::disk();

        if (!empty($original_path)) {
            try {
                $disk->copy($original_path, $new_original_path);
            }
            catch (FileNotFoundException $e) {
                ecjia_log_warning($e->getMessage());
            }
        } else {
            $new_original_path = '';
        }

        if (!empty($img_path)) {
            try {
                $disk->copy($original_path, $new_img_path);
            }
            catch (FileNotFoundException $e) {
                ecjia_log_warning($e->getMessage());
            }
        } else {
            $new_img_path = '';
        }

        if (!empty($thumb_path)) {
            try {
                $disk->copy($original_path, $new_thumb_path);
            }
            catch (FileNotFoundException $e) {
                ecjia_log_warning($e->getMessage());
            }
        } else {
            $new_thumb_path = '';
        }

        return [$new_original_path, $new_img_path, $new_thumb_path];
    }



}