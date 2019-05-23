<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2018/12/12
 * Time: 14:04
 */

namespace Ecjia\App\Goods\StoreDuplicateHandlers;

use RC_DB;

/**
 * 复制店铺中商品规格
 *
 * Class StoreGoodsSpecificationDuplicate
 * @package Ecjia\App\Goods\StoreDuplicateHandlers
 */
class StoreGoodsSpecificationDuplicate extends StoreGoodsParameterDuplicate
{
    /**
     * 代号标识
     * @var string
     */
    protected $code = 'store_goods_specification_duplicate';

    protected $rank_order = 1;

    protected $rank_total = 11;

    protected $rank = '';

    public function __construct($store_id, $source_store_id, $sort = 11)
    {
        parent::__construct($store_id, $source_store_id, $sort);
        $this->name = __('店铺商品规格', 'goods');
    }

    /**
     * 获取源店铺数据操作对象
     */
    public function getSourceStoreDataHandler()
    {
        return RC_DB::table('goods_type')->where('store_id', $this->source_store_id)->where('enabled', 1)->where('cat_type', 'specification');
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


}