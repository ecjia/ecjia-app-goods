<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 09:27
 */

namespace Ecjia\App\Goods\GoodsImage;


use Ecjia\App\Goods\GoodsImage\Format\ProductImageFormatted;

class ProductImage
{
    /**
     * @var int goods_id or product_id
     */
    protected $id;

    /**
     * @var array 上传成功后的信息
     */
    protected $fileinfo;

    /**
     * @var ProductImageFormatted
     */
    protected $image_format;


    public function __construct($id, $fileinfo = null)
    {
        $this->id = $id;

        $this->fileinfo = $fileinfo;

        $this->image_format = new ProductImageFormatted($this);

    }

    /**
     * 获取商品ID 或者 货品ID
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取上传文件的扩展名
     * @return string
     */
    public function getExtensionName()
    {
        return $this->fileinfo['ext'];
    }

    /**
     *  保存图片到磁盘
     */
    public function saveImageToDisk()
    {

    }

    /**
     * 更新图片到数据库
     */
    public function updateImageToDatabase()
    {

    }

    /**
     * 保存缩略图到磁盘
     */
    public function saveThumbImageToDisk()
    {

    }

    /**
     * 更新缩略图到数据库
     */
    public function updateThumbImageToDatabase()
    {

    }


}