<?php

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use ecjia_error;
use RC_DB;
use RC_Api;
use ecjia_admin;
use Royalcms\Component\Database\QueryException;

/**
 * 复制店铺中商品相册数据
 *
 * @todo 图片字段的处理 img_url img_desc thumb_url img_original
 *
 * Class StoreGoodsGalleryDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreGoodsGalleryDuplicate extends StoreProcessAfterDuplicateGoodsAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_goods_gallery_duplicate';

    private $replacement_goods_gallery = [];

    protected $rank_order = 7;

    public function __construct($store_id, $source_store_id, $sort = 17)
    {
        parent::__construct($store_id, $source_store_id, '商品相册', $sort);
    }

    protected function getTableName()
    {
        return 'goods_gallery';
    }

    /**
     * 店铺复制操作的具体过程
     * @return bool|ecjia_error
     */
    protected function startDuplicateProcedure()
    {
        try {
            $this->setProgressData();

            //设置 goods 相关替换数据
            $this->setReplacementGoodsAfterSetProgressData();

            //取出源店铺 goods_id
            $old_goods_id = $this->getOldGoodsId();

            if (!empty($old_goods_id)) {
                //获取商家货品的替换数据
                $replacement_products = [];
                foreach ($this->dependents as $code) {
                    $replacement_products += $this->progress_data->getReplacementDataByCode($code . '.products');
                }

                //将数据同步到 goods_gallery 商品相册数据
                RC_DB::table($this->getTableName())->whereIn('goods_id', $old_goods_id)->chunk(20, function ($items) use ($replacement_products) {
                    foreach ($items as &$item) {
                        $img_id = $item['img_id'];
                        unset($item['img_id']);

                        //通过 goods 替换数据设置新店铺的 goods_id
                        $item['goods_id'] = array_get($this->replacement_goods, $item['goods_id'], $item['goods_id']);

                        //通过 products 替换数据设置新店铺的 product_id
                        $item['product_id'] = array_get($replacement_products, $item['product_id'], $item['product_id']);

                        //@todo 图片字段的处理 img_url img_desc thumb_url img_original
                        $item['img_url'] = $this->copyImage($item['img_url']);
                        $item['img_desc'] = $this->copyImage($item['img_desc']);
                        $item['thumb_url'] = $this->copyImage($item['thumb_url']);
                        $item['img_original'] = $this->copyImage($item['img_original']);

                        try {
                            //将数据插入到新店铺
                            //$new_img_id = $img_id + 1;
                            $new_img_id = RC_DB::table($this->getTableName())->insertGetId($item);

                            //存储替换记录
                            $this->replacement_goods_gallery[$img_id] = $new_img_id;
                        } catch (QueryException $e) {
                            ecjia_log_warning($e->getMessage());
                        }
                    }
                    //dd($this->replacement_goods_gallery, $replacement_products, $items);
                });

                //存储 goods_gallery 相关替换数据
                $this->setReplacementData($this->getCode(), $this->replacement_goods_gallery);
            }
            return true;
        } catch (QueryException $e) {
            ecjia_log_warning($e->getMessage());
        }
    }

    /**
     * 复制单张图片
     *
     * @param $path
     *
     * @return string
     */
    protected function copyImage($path)
    {

        return $path;
    }

    /**
     * 复制缩编器内容中的图片
     *
     * @param $content
     *
     * @return string
     */
    protected function copyImageForContent($content)
    {

        return $content;
    }

}