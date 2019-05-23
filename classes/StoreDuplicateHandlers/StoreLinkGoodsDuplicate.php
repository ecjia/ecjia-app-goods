<?php

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use ecjia_error;
use RC_DB;
use Royalcms\Component\Database\QueryException;

/**
 * 复制店铺中商品关联商品数据（无图片字段）
 *
 * Class StoreLinkGoodsDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreLinkGoodsDuplicate extends StoreProcessAfterDuplicateGoodsAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_link_goods_duplicate';

    protected $rank_order = 9;

    public function __construct($store_id, $source_store_id, $sort = 19)
    {
        parent::__construct($store_id, $source_store_id, '商品关联商品', $sort);
    }

    protected function getTableName()
    {
        return 'link_goods';
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
                //将数据同步到 link_goods  商品关联商品数据
                $this->duplicateLinkGoods($old_goods_id);
            }
            return true;
        } catch (QueryException $e) {
            ecjia_log_warning($e->getMessage());
            return new ecjia_error('duplicate_data_error', $e->getMessage());
        }
    }

    /**
     * 复制 link_goods 数据
     * @param $old_goods_id
     */
    private function duplicateLinkGoods($old_goods_id)
    {
        RC_DB::table($this->getTableName())->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) {
            foreach ($items as &$item) {
                //通过 goods 替换数据设置新店铺的 link_goods_id
                $item['link_goods_id'] = array_get($this->replacement_goods, $item['link_goods_id'], $item['link_goods_id']);

                //通过 goods 替换数据设置新店铺的 goods_id
                $item['goods_id'] = array_get($this->replacement_goods, $item['goods_id'], $item['goods_id']);
            }

            //将数据插入到新店铺
            RC_DB::table($this->getTableName())->insert($items);
            //dd($items);
        });
    }

}