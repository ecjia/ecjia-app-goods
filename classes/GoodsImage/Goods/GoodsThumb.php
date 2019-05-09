<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 09:28
 */

namespace Ecjia\App\Goods\GoodsImage\Goods;


use Ecjia\App\Goods\Models\GoodsModel;
use ecjia_error;

class GoodsThumb extends GoodsImage
{

    /**
     *  保存图片到磁盘
     */
    public function saveImageToDisk()
    {
        $thumb_path = $this->disk->getPath($this->image_format->getThumbPostion());

        $original_path = $img_path = null;

        //返回 [原图，处理过的图片，缩略图]
        return [$original_path, $img_path, $thumb_path];
    }

    /**
     * 更新图片到数据库
     */
    public function updateToDatabase($img_desc = null)
    {
        //$img_desc 用不到，不需要存储

        list($original_path, $img_path, $thumb_path) = $this->saveImageToDisk();

        if (!empty($thumb_path)) {
            return new ecjia_error('upload_goods_thumb_error', __('商品缩略图路径无效', 'goods'));
        }

        //存入数据库中
        $data = array(
            'thumb_url' 	=> $thumb_path,
        );

        $model = GoodsModel::where('goods_id', $this->goods_id)->update($data);
        if (! empty($model)) {
            return new ecjia_error('upload_thumb_image_fail', __('商品缩略图上传失败', 'goods'));
        }

        return true;
    }

}