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
use RC_DB;
use RC_Api;
use ecjia_admin;
use RC_Time;

/**
 * 复制店铺中的在售商品
 *
 * Class StoreSellingGoodsDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreSellingGoodsDuplicate extends StoreDuplicateAbstract
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_selling_goods_duplicate';

    /**
     * 排序RC_Hook::apply_filters(
     * @var int
     */
    protected $sort = 14;

    protected $dependents = [
        'store_goods_merchant_category_duplicate',
    ];

    public function __construct($store_id, $source_store_id)
    {
        $this->name = __('在售普通商品', 'goods');
        parent::__construct($store_id, $source_store_id);
    }

    /**
     * 获取源店铺数据操作对象
     */
    public function getSourceStoreDataHandler()
    {
        return RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->where('is_delete', '!=', 1);
        //->select('goods_id', 'store_id', 'merchant_cat_id', 'bonus_type_id', 'goods_type', 'specification_id', 'parameter_id');
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count = $this->handleCount();
        $text = sprintf(__('店铺内总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>件在售商品', 'goods'), $count);

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
        $this->count = $this->getSourceStoreDataHandler()->count();
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
     */
    protected function startDuplicateProcedure()
    {
        try {
            $progress_data = (new \Ecjia\App\Store\StoreDuplicate\ProgressDataStorage($this->store_id))->getDuplicateProgressData();

            $merchant_category_replacement = $progress_data->getReplacementDataByCode('store_goods_merchant_category_duplicate');

            $store_bonus_replacement = $progress_data->getReplacementDataByCode('store_bonus_duplicate');

            $goods_specification_replacement = $progress_data->getReplacementDataByCode('store_goods_specification_duplicate.goods_type');

            $goods_parameter_duplicate_replacement = $progress_data->getReplacementDataByCode('store_goods_parameter_duplicate.goods_type');

            $goods_type_replacement = $goods_specification_replacement + $goods_parameter_duplicate_replacement;

            $replacement_goods = [];

            $this->getSourceStoreDataHandler()->chunk(50, function ($items) use (
                &$replacement_goods,
                $merchant_category_replacement,
                $store_bonus_replacement,
                $goods_specification_replacement,
                $goods_parameter_duplicate_replacement,
                $goods_type_replacement
            ) {

                //从过程数据中提取需要用到的替换数据

                //dd($items);
                //构造可用于复制的数据
                //$this->buildDuplicateData($items);

                foreach ($items as &$item) {
                    $goods_id = $item['goods_id'];
                    unset($item['goods_id']);

                    //将源店铺ID设为新店铺的ID
                    $item['store_id'] = $this->store_id;

                    //设置新店铺 merchat_cat_level1_id
                    $item['merchat_cat_level1_id'] = array_get($merchant_category_replacement, $item['merchat_cat_level1_id'], $item['merchat_cat_level1_id']);

                    //设置新店铺 merchat_cat_level2_id
                    $item['merchat_cat_level2_id'] = array_get($merchant_category_replacement, $item['merchat_cat_level2_id'], $item['merchat_cat_level2_id']);

                    //设置新店铺 merchant_cat_id
                    $item['merchant_cat_id'] = array_get($merchant_category_replacement, $item['merchant_cat_id'], $item['merchant_cat_id']);

                    //设置新店铺 bonus_type_id
                    $item['bonus_type_id'] = array_get($store_bonus_replacement, $item['bonus_type_id'], $item['bonus_type_id']);

                    //设置新店铺 goods_type
                    $item['goods_type'] = array_get($goods_type_replacement, $item['goods_type'], $item['goods_type']);

                    //设置新店铺 specification_id
                    $item['specification_id'] = array_get($goods_specification_replacement, $item['specification_id'], $item['specification_id']);

                    //设置新店铺 parameter_id
                    $item['parameter_id'] = array_get($goods_parameter_duplicate_replacement, $item['parameter_id'], $item['parameter_id']);

                    //goods_sn，商品唯一货号是需要重新生成，生成规则是什么

                    //click_count，商品点击数是否设为0
                    $item['click_count'] = 0;

                    //goods_number 商品库存数量设为1000
                    $item['click_count'] = 1000;

                    $time = RC_Time::gmtime();
                    //add_time  商品添加时间设为当前时间
                    $item['add_time'] = $time;

                    //last_update  最近一次更新商品配置的时间设为当前时间
                    $item['last_update'] = $time;

                    //comments_number 评论设置为0
                    $item['comments_number'] = 0;

                    //sales_volume 销量设置为0
                    $item['sales_volume'] = 0;


                    //@todo 图片字段的处理  goods_desc goods_thumb goods_img original_img


                    //插入数据到新店铺
                    $new_goods_id = RC_DB::table('goods')->insertGetId($item);
                    $replacement_goods[$goods_id] = $new_goods_id;

                }

            });

            $this->setReplacementData($this->getCode(), $replacement_goods);

            return true;
        } catch (\Royalcms\Component\Repository\Exceptions\RepositoryException $e) {
            return new ecjia_error('duplicate_data_error', $e->getMessage());
        }


    }

    protected function buildDuplicateData(&$items)
    {
        foreach ($items as &$item) {
            unset($item['goods_id']);

            $item['merchant_cat_id'] =
                //将源店铺ID设为新店铺的ID
            $item['store_id'] = $this->store_id;


        }


        //解决外键带来的问题数据


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

        ecjia_admin::admin_log($merchants_name, 'clean', 'store_goods');
    }

}