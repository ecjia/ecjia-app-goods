<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-05-06
 * Time: 15:34
 */

namespace Ecjia\App\Goods\GoodsImage;

use RC_Storage;

class StorageDisk
{
    /**
     * @var \Royalcms\Component\Contracts\Filesystem\Filesystem | \Royalcms\Component\Storage\Contracts\StorageInterface
     */
    protected $disk;

    public function __construct()
    {
        $this->disk = RC_Storage::disk();
    }

    /**
     * 写入文件
     */
    public function wirte($path, $content)
    {
        return $this->disk->put_contents($path, $content);
    }

    /**
     * 写入文件，来自源文件
     * @param $source_path
     * @param $path
     */
    public function writeForSourcePath($source_path, $path)
    {
        $content = file_get_contents($source_path);
        $this->wirte($path, $content);
    }

    /**
     * 删除文件
     */
    public function delete($path)
    {
        return $this->disk->delete($path);
    }

    /**
     * 生成缩略图
     */
    public function makeThumb()
    {

    }

    /**
     * 添加水印
     */
    public function addWatermark()
    {

    }

    /**
     * 获取上传图片的绝对路径
     * @param string $path 数据库中存储的地址
     * @return string|boolean
     */
    public function getPath($path)
    {

    }

    /**
     * 获取上传图片的绝对地址
     * @param string $path 数据库中存储的地址
     * @return string|boolean
     */
    public function getUrl($path)
    {

    }


}