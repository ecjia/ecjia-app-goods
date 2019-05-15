<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use Ecjia\App\Store\StoreDuplicate\StoreDuplicateAbstract;
use RC_Uri;
use RC_DB;
use RC_Api;
use ecjia_admin;

class StorePromotionDuplicate extends StoreDuplicateAbstract
{

    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_promotion_duplicate';

    /**
     * 排序
     * @var int
     */
    protected $sort = 9;

    public function __construct($store_id, $source_store_id)
    {
        $this->name = __('促销活动', 'promotion');

        parent::__construct($store_id, $source_store_id);
    }

    /**
     * 数据描述及输出显示内容
     */
    public function handlePrintData()
    {
        $count     = $this->handleCount();
        $text      = sprintf(__('店铺促销活动总共<span class="ecjiafc-red ecjiaf-fs3">%s</span>个', 'promotion'), $count);

        return <<<HTML
<span class="controls-info w300">{$text}</span>
HTML;
    }

    /**
     * 获取数据统计条数
     *
     * @return mixed
     */
    public function handleCount()
    {
        $count = RC_DB::table('goods')->where('is_promote', 1)->where('is_delete', '!=', 1)->where('store_id', $this->source_store_id)->count();

        return $count;
    }


    /**
     * 执行清除操作
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

        $merchants_name = !empty($store_info) ? sprintf(__('店铺名是%s', 'promotion'), $store_info['merchants_name']) : sprintf(__('店铺ID是%s', 'promotion'), $this->store_id);

        ecjia_admin::admin_log($merchants_name, 'clean', 'store_promotion_activity');

    }

}