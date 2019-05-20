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

        $this->source_store_data_handler = RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->where('is_delete', '!=', 1);
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

        $progress_data = (new \Ecjia\App\Store\StoreDuplicate\ProgressDataStorage($this->store_id))->getDuplicateProgressData();

        $replacement_data = $progress_data->getReplacementDataByCode();
        //$merchant_category_replacement = $progress_data->getReplacementDataByCode('store_goods_merchant_category_duplicate');


        $replacement_goods = [];
        //$merchant_category_replacement = isset($replacement_data['store_goods_merchant_category_duplicate']) ? $replacement_data['store_goods_merchant_category_duplicate'] : [];
        $this->source_store_data_handler->chunk(50, function ($items) use ($replacement_data, &$replacement_goods) {

            //从过程数据中提取需要用到的替换数据
            $merchant_category_replacement = array_get($replacement_data, 'store_goods_merchant_category_duplicate', []);
            $store_bonus_replacement = array_get($replacement_data, 'store_bonus_duplicate', []);
            $goods_specification_replacement = array_get($replacement_data, 'store_goods_specification_duplicate', []);
            $goods_parameter_duplicate_replacement = array_get($replacement_data, 'store_goods_parameter_duplicate', []);
            $goods_type_replacement = array_get($goods_specification_replacement, 'goods_type', []) + array_get($goods_parameter_duplicate_replacement, 'goods_type', []);

            //dd($items);
            //构造可用于复制的数据
            //$this->buildDuplicateData($items);

            foreach ($items as &$item) {
                $goods_id = $item['goods_id'];
                unset($item['goods_id']);

                //将源店铺ID设为新店铺的ID
                $item['store_id'] = $this->store_id;


                //设置新店铺 merchant_cat_id
                if (isset($merchant_category_replacement[$item['merchant_cat_id']])) {
                    $item['merchant_cat_id'] = $merchant_category_replacement[$item['merchant_cat_id']];
                }

                //设置新店铺 bonus_type_id
                if (isset($store_bonus_replacement[$item['bonus_type_id']])) {
                    $item['bonus_type_id'] = $store_bonus_replacement[$item['bonus_type_id']];
                }

                //设置新店铺 goods_type
                if (isset($goods_type_replacement[$item['goods_type']])) {
                    $item['goods_type'] = $goods_type_replacement[$item['goods_type']];
                }

                //设置新店铺 specification_id
                if (isset($goods_specification_replacement[$item['specification_id']])) {
                    $item['specification_id'] = $goods_specification_replacement[$item['specification_id']];
                }

                //设置新店铺 parameter_id
                if (isset($goods_parameter_duplicate_replacement[$item['parameter_id']])) {
                    $item['parameter_id'] = $goods_parameter_duplicate_replacement[$item['parameter_id']];
                }

                //goods_sn，商品唯一货号是需要重新生成，生成规则是什么

                //click_count，商品点击数是否设为0

                //goods_number 商品库存数量是否设为0

                //shop_price 本店售价是否重新设置

                //cost_price 成本价是否重新设置

                //promote_* 促销相关字段，不一定在新店铺适用

                //add_time  商品添加时间是否设为当前时间

                //comments_number 评论是否设置为0

                //sales_volume 销量是否设置为0


                //图片字段的处理



                //插入数据到新店铺
                //$new_goods_id = $goods_id + 1;
                $new_goods_id = RC_DB::table('goods')->insertGetId($item);
                $replacement_goods[$goods_id] = $new_goods_id;

            }


            //dd($items,$replacement_goods);
        });

        $this->setReplacementData($this->getCode(), $replacement_goods);


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