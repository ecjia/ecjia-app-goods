<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-01
 * Time: 18:24
 */

namespace Ecjia\App\Goods\GoodsSearch\Filters;


use Ecjia\App\Goods\GoodsSearch\FilterInterface;
use Royalcms\Component\Database\Eloquent\Builder;

/**
 * 商家商品分类条件
 * @author Administrator
 *
 */
class MerchantGoodsCategory implements FilterInterface
{

    /**
     * 把过滤条件附加到 builder 的实例上
     *
     * @param Builder $builder
     * @param mixed $value
     * @return Builder $builder
     */
    public static function apply(Builder $builder, $value)
    {
    	//return $builder->where('city', $value);
    	
    	if (isset($filter['merchant_cat_id']) && !empty($filter['merchant_cat_id']) && isset($filter['store_id']) && !empty($filter['store_id']) ) {
    	
    		$children_cat = self::get_children_cat($filter['merchant_cat_id'], $filter['store_id']);
    		//$where[] = "merchant_cat_id IN (" . $children_cat.")";
    		$dbview->whereIn(RC_DB::raw('g.merchant_cat_id'), $children_cat);
    	}
    	
    }

}