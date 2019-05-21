<?php

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
use ecjia_error;
use RC_DB;
use RC_Api;
use ecjia_admin;

/**
 * 复制店铺中商品会员价格数据
 * Class StoreMemberPriceDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreMemberPriceDuplicate extends StoreDuplicateAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_member_price_duplicate';

    /**
     * 排序RC_Hook::apply_filters(
     * @var int
     */
    protected $sort = 18;

    protected $dependents = [
        'store_selling_goods_duplicate',
        'store_cashier_goods_duplicate'
    ];

    /**
     * goods 表中的替换数据
     * @var array
     */
    private $replacement_goods = [];

    private $replacement_member_price = [];

    private $progress_data;

    private $table = 'member_price';

    public function __construct($store_id, $source_store_id)
    {
        $this->name = __('商品会员价格', 'goods');
        parent::__construct($store_id, $source_store_id);
    }

    /**
     * 获取源店铺数据操作对象
     */
    public function getSourceStoreDataHandler()
    {
        return RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->where('is_delete', '!=', 1);
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count = $this->handleCount();
        $text = sprintf(__('店铺内总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>%s', 'goods'), $count, $this->name);

        return <<<HTML
<span class="controls-info">{$text}</span>
HTML;
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
            $this->count = RC_DB::table($this->table)->whereIn('goods_id', $old_goods_id)->count();
        }
        return $this->count;
    }

    /**
     * 执行复制操作
     *
     * @return mixed
     */
    public function handleDuplicate()
    {
        //检测当前对象是否已复制完成
        if ($this->isCheckFinished()) {
            return true;
        }

        //如果当前对象复制前仍存在依赖，则需要先复制依赖对象才能继续复制
        if (!empty($this->dependents)) { //如果设有依赖对象
            //检测依赖
            $items = $this->dependentCheck();
            if (!empty($items)) {
                return new ecjia_error('handle_duplicate_error', __('复制依赖检测失败！', 'store'), $items);
            }
        }

        //执行具体任务
        $result = $this->startDuplicateProcedure();
        if (is_ecjia_error($result)) {
            return $result;
        }

        //标记处理完成
        $this->markDuplicateFinished();

        //记录日志
        $this->handleAdminLog();

        return true;
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
        RC_DB::table($this->table)->whereIn('goods_id', $old_goods_id)->chunk(50, function ($items) {
            foreach ($items as $item) {
                $price_id = $item['price_id'];
                unset($item['price_id']);

                //通过 goods 替换数据设置新店铺的 goods_id
                $item['goods_id'] = array_get($this->replacement_goods, $item['goods_id'], $item['goods_id']);

                //将数据插入到新店铺
                //$new_price_id = $price_id + 1;
                $new_price_id = RC_DB::table($this->table)->insertGetId($item);

                //存储替换记录
                $this->replacement_member_price[$price_id] = $new_price_id;
            }
            //dd($replacement_member_price, $items);
        });
    }

    /**
     * 设置 goods 替换数据
     * @return $this
     */
    protected function setReplacementGoodsAfterSetProgressData()
    {
        if (empty($this->replacement_goods)) {
            //获取当前依赖下所有商品替换数据
            foreach ($this->dependents as $code) {
                $this->replacement_goods += $this->progress_data->getReplacementDataByCode($code);
            }
        }
        return $this;
    }

    /**
     * 设置过程数据
     * @return $this
     */
    protected function setProgressData()
    {
        if (empty($this->progress_data)) {
            //从过程数据中提取需要用到的替换数据
            $this->progress_data = (new \Ecjia\App\Store\StoreDuplicate\ProgressDataStorage($this->store_id))->getDuplicateProgressData();
        }
        return $this;
    }

    /**
     * 获取源店铺中的 goods_id
     * @return array
     */
    protected function getOldGoodsId()
    {
        if (empty($this->replacement_goods)) {
            return $this->getSourceStoreDataHandler()->lists('goods_id');
        }
        return array_keys($this->replacement_goods);
    }

    /**
     * 返回操作日志编写
     *
     * @return mixed
     */
    public function handleAdminLog()
    {
        \Ecjia\App\Store\Helper::assign_adminlog_content();

        $store_info = RC_Api::api('store', 'store_info', array('store_id' => $this->store_id));

        $merchants_name = !empty($store_info) ? sprintf(__('店铺名是%s', 'goods'), $store_info['merchants_name']) : sprintf(__('店铺ID是%s', 'goods'), $this->store_id);

        ecjia_admin::admin_log($merchants_name, 'duplicate', 'store_goods');
    }

}