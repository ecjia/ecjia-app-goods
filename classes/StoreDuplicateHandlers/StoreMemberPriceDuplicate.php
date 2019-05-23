<?php

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

//use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
use ecjia_error;
use RC_DB;
use RC_Api;
use ecjia_admin;

/**
 * 复制店铺中商品会员价格数据
 *
 * Class StoreMemberPriceDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreMemberPriceDuplicate extends StoreProcessAfterDuplicateGoodsAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_member_price_duplicate';

    private $replacement_member_price = [];

    protected $rank_order = 8;

    public function __construct($store_id, $source_store_id, $sort = 18)
    {
        parent::__construct($store_id, $source_store_id, '商品会员价格', $sort);
    }

    protected function getTableName()
    {
        return 'member_price';
    }

    /**
     * 统计数据条数并获取
     *
     * @return mixed
     */
    public function handleCount()
    {
        //如果已经统计过，直接返回统计过的条数
        if ($this->count) {
            return $this->count;
        }

        // 统计数据条数
        $old_goods_id = $this->getOldGoodsId();
        if (!empty($old_goods_id)) {
            $this->count = RC_DB::table($this->getTableName())->whereIn('goods_id', $old_goods_id)->count();
        }
        return $this->count;
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
                //将数据同步到 member_price
                $this->duplicateMemberPrice($old_goods_id);

                //存储 member_price 相关替换数据
                $this->setReplacementData($this->getCode(), $this->replacement_member_price);
            }

            return true;
        } catch (\Royalcms\Component\Repository\Exceptions\RepositoryException $e) {
            return new ecjia_error('duplicate_data_error', $e->getMessage());
        }
    }

    /**
     * 复制 member_price 数据
     * @param $old_goods_id
     */
    private function duplicateMemberPrice($old_goods_id)
    {
        RC_DB::table($this->getTableName())->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) {
            foreach ($items as $item) {
                $price_id = $item['price_id'];
                unset($item['price_id']);

                //通过 goods 替换数据设置新店铺的 goods_id
                $item['goods_id'] = array_get($this->replacement_goods, $item['goods_id'], $item['goods_id']);

                //将数据插入到新店铺
                //$new_price_id = $price_id + 1;
                $new_price_id = RC_DB::table($this->getTableName())->insertGetId($item);

                //存储替换记录
                $this->replacement_member_price[$price_id] = $new_price_id;
            }
            //dd($replacement_member_price, $items);
        });
    }

}