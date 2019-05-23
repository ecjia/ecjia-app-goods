<?php

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
use ecjia_admin;
use ecjia_error;
use RC_Api;
use RC_DB;
use Royalcms\Component\Database\QueryException;

/**
 * 主商品数据复制后的后续操作行为抽象
 *
 * Class StoreProcessAfterDuplicateGoodsAbstract
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
abstract class StoreProcessAfterDuplicateGoodsAbstract extends StoreDuplicateAbstract
{
    protected $dependents = [
        'store_selling_goods_duplicate',
        'store_cashier_goods_duplicate',
        'store_bulk_goods_duplicate'
    ];

    /**
     * 过程数据对象
     * @var null|object
     */
    protected $progress_data;

    /**
     * goods 表中的替换数据
     * @var array
     */
    protected $replacement_goods = [];

    protected $rank_order = 1;

    protected $rank_total = 11;

    public function __construct($store_id, $source_store_id, $name, $sort = 15)
    {
        parent::__construct($store_id, $source_store_id, $sort);
        $this->name = __($name, 'goods');
    }

    abstract protected function getTableName();

    public function getName()
    {
        return $this->name . sprintf('(%d/%d)', $this->rank_order, $this->rank_total);
    }

    /**
     * 获取源店铺数据操作对象
     */
    public function getSourceStoreDataHandler()
    {
        return RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->where('is_delete', 0);
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $text = sprintf(__('店铺内总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>个%s', 'goods'), $this->handleCount(), $this->name);
        return <<<HTML
<span class="controls-info">{$text}</span>
HTML;
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

    abstract protected function startDuplicateProcedure();

    /**
     * 设置 goods 替换数据
     * @return $this
     */
    protected function setReplacementGoodsAfterSetProgressData()
    {
        if (empty($this->replacement_goods)) {
            //获取当前依赖下所有商品替换数据
            foreach ($this->dependents as $code) {
                $this->replacement_goods += $this->progress_data->getReplacementDataByCode($code . '.goods');
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
            $this->progress_data = $this->handleDuplicateProgressData();
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
            try {
                return $this->getSourceStoreDataHandler()->lists('goods_id');
            } catch (QueryException $e) {
                ecjia_log_warning($e->getMessage());
            }
        }
        return array_keys($this->replacement_goods);
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
            try {
                $this->count = RC_DB::table($this->getTableName())->whereIn('goods_id', $old_goods_id)->count();
            } catch (QueryException $e) {
                ecjia_log_warning($e->getMessage());
            }
        }
        return $this->count;
    }

    /**
     * 返回操作日志编写
     *
     * @return mixed
     */
    public function handleAdminLog()
    {
        \Ecjia\App\Store\Helper::assign_adminlog_content();

        static $store_merchant_name, $source_store_merchant_name;

        if (empty($store_merchant_name)) {
            $store_info = RC_Api::api('store', 'store_info', ['store_id' => $this->store_id]);
            $store_merchant_name = array_get(empty($store_info) ? [] : $store_info, 'merchants_name');
        }

        if (empty($source_store_merchant_name)) {
            $source_store_info = RC_Api::api('store', 'store_info', ['store_id' => $this->source_store_id]);
            $source_store_merchant_name = array_get(empty($source_store_info) ? [] : $source_store_info, 'merchants_name');
        }

        $content = sprintf(__('录入：将【%s】店铺所有%s复制到【%s】店铺中', 'goods'), $source_store_merchant_name, $this->name, $store_merchant_name);
        ecjia_admin::admin_log($content, 'duplicate', 'store_goods');
    }
}