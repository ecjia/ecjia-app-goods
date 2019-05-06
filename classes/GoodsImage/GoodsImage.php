<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 09:27
 */

namespace Ecjia\App\Goods\GoodsImage;


use Ecjia\App\Goods\GoodsImage\Format\GoodsImageFormatted;

class GoodsImage
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
     * @var GoodsImageFormatted
     */
    protected $image_format;


    protected $disk;

    /**
     * 设置是否自动生成缩略图
     * @var bool
     */
    protected $auto_generate_thumb = false;


    public function __construct($id, $fileinfo = null)
    {
        $this->id = $id;

        $this->fileinfo = $fileinfo;

        $this->image_format = new GoodsImageFormatted($this);

        $this->disk = new StorageDisk();
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
     * 获取上传文件的原始文件名
     * @return string
     */
    public function getFileName()
    {
        return $this->fileinfo['name'];
    }

    /**
     * 获取上传后的文件原始路径
     * @return string
     */
    public function getFilePath()
    {
        return $this->fileinfo['tmpname'];
    }

    /**
     *  保存图片到磁盘
     */
    public function saveImageToDisk()
    {
        /* 重新格式化图片名称 */
        $img_path = $this->disk->getPath($this->image_format->getGoodsimgPostion());
        $original_path = $this->disk->getPath($this->image_format->getSourcePostion());

        // 生成缩略图
        $thumb_path = '';
        if ($this->auto_generate_thumb) {
            $thumb_path = $this->saveThumbImageToDisk();
        }

        // 添加水印
        $this->disk->addWatermark($this->getFilePath(), $img_path);

        // 保存原图
        $this->disk->writeForSourcePath($this->getFilePath(), $original_path);

        //返回 [原图，处理过的图片，缩略图]
        return [$original_path, $img_path, $thumb_path];
    }

    /**
     * 更新图片到数据库
     */
    public function updateToDatabase($img_desc = null)
    {
        if (is_null($img_desc)) {
            $img_desc = $this->getFileName();
        }

        list($original_path, $img_path, $thumb_path) = $this->saveImageToDisk();

        //存入数据库中




    }

    /**
     * 保存缩略图到磁盘
     * 返回缩略图路径
     * @return string
     */
    public function saveThumbImageToDisk()
    {
        $thumb_path = $this->disk->getPath($this->image_format->getThumbPostion());

        $this->disk->makeThumb($this->getFilePath(), $thumb_path);

        return $thumb_path;
    }

    /**
     * 设置是否需要自动生成缩略图，默认为自动生成缩略图
     * @param bool $bool
     */
    public function setAutoGenerateThumb($bool)
    {
        if (is_bool($bool)) {
            $this->auto_generate_thumb = $bool;
        }
    }

}