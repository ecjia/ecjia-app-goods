<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
use ecjia_error;
use RC_Uri;
use RC_DB;
use RC_Api;
use ecjia_admin;

/**
 * 复制店铺中商品规格
 *
 * Class StoreGoodsSpecificationDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreGoodsSpecificationDuplicate extends StoreDuplicateAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_goods_specification_duplicate';

    /**
     * 排序
     * @var int
     */
    protected $sort = 11;

    public function __construct($store_id, $source_store_id)
    {
        $this->name = __('店铺商品规格', 'goods');

        parent::__construct($store_id, $source_store_id);

        $this->source_store_data_handler = RC_DB::table('goods_type')->where('store_id', $this->source_store_id)->where('enabled', 1)->where('cat_type', 'specification');
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count = $this->handleCount();
        $text = sprintf(__('店铺内总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>个规格模板', 'goods'), $count);

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
        if (!empty($this->source_store_data_handler)) {
            $this->count = $this->source_store_data_handler->count();
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
        $this->startDuplicateProcedure();

        //标记处理完成
        $this->markDuplicateFinished();

        //记录日志
        $this->handleAdminLog();

        return true;
    }

    /**
     * 店铺复制操作的具体过程
     */
    protected function startDuplicateProcedure()
    {
        $replacement_goods_type = [];

        $this->source_store_data_handler->chunk(50, function ($items) use (& $replacement_goods_type) {
//            dd($items);

            //构造可用于复制的数据
            foreach ($items as $item) {

                $cat_id = $item['cat_id'];

                unset($item['cat_id']);

                //将源店铺ID设为新店铺的ID
                $item['store_id'] = $this->store_id;

                $new_cat_id = RC_DB::table('goods_type')->insertGetId($item);

                $replacement_goods_type[$cat_id] = $new_cat_id;
            }

        });

        $cat_id_keys = array_keys($replacement_goods_type);
        if (!empty($cat_id_keys)) {

            $replacement_attribute = [];

            //通过源店铺的cat_id查询出在attribute中的关联数据
            RC_DB::table('attribute')->whereIn('cat_id', $cat_id_keys)->chunk(50, function($items) use ($replacement_goods_type, & $replacement_attribute) {

                //构造可用于复制的数据
                foreach ($items as & $item) {
                    $attr_id = $item['attr_id'];
                    unset($item['attr_id']);

                    //将cat_id替换成新店铺的cat_id
                    $item['cat_id'] = array_get($replacement_goods_type, $item['cat_id'], 0);

                    //为新店铺插入这些数据
                    $new_attr_id = RC_DB::table('attribute')->insertGetId($item);

                    $replacement_attribute[$attr_id] = $new_attr_id;

                }
            });
        }

        $replacement_data = [
            'goods_type' => $replacement_goods_type,
            'attribute' => $replacement_attribute,
        ];

        $this->setReplacementData($this->getCode(), $replacement_data);

    }

    /**
     * 返回操作日志编写
     *
     * @return mixed
     */
    public function handleAdminLog()
    {

    }

}