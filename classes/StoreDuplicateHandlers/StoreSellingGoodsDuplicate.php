<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
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
    private $handleObj;
    /**
     * 排序RC_Hook::apply_filters(
     * @var int
     */
    protected $sort = 5;

    public function __construct($store_id, $source_store_id)
    {
        $this->name = __('在售商品', 'goods');

        parent::__construct($store_id, $source_store_id);
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count     = $this->handleCount();
        $text      = sprintf(__('店铺内总共有<span class="ecjiafc-red ecjiaf-fs3">%s</span>件在售商品', 'goods'), $count);

        return <<<HTML
<span class="controls-info">{$text}</span>
HTML;
    }

    /**
     * 获取数据统计条数
     *
     * @return mixed
     */
    public function handleCount()
    {
        $a = RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1);
        var_dump(RC_DB::getQueryLog());
        $d = RC_DB::table('store_franchisee')->where('store_id', $this->source_store_id)->get();
        var_dump(RC_DB::getQueryLog());
        $a->limit(10)->select('goods_id');
        $c = $a->lists('goods_id'); //一维数组，包含所有goods_id, get()是二维数组，每个子数组中包含一个goods_id的键值对

        var_dump($c,$d);
        exit;

        var_dump($a->count());
        $a->chunk(10,function($goods){
            foreach ($goods as $v){
                echo $v['store_id'] . '_'.$v['goods_id'];
                echo '<br>';
            }
            echo '<hr>';
        });
        // RC_DB::enableQueryLog();
        //   RC_DB::getQueryLog()
        $count = RC_DB::table('goods')->where('store_id', $this->source_store_id)->where('is_on_sale', 1)->count();

        return $count;
    }


    /**
     * 执行复制操作
     *
     * @return mixed
     */
    public function handleDuplicate()
    {

        return true;
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