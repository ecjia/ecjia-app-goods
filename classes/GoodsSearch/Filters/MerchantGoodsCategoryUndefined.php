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
 * 商家商品分类（未分类的），兼容PC店铺未分类筛选
 * @author Administrator
 *
 */
class MerchantGoodsCategoryUndefined implements FilterInterface
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
    	
    	if (!empty($filter['merchant_cat_id']) && $filter['merchant_cat_id'] == 'undefined' && !empty($filter['store_id'])){  //未分类，兼容pc未分类
			$children_cat = 0;
			//$where[] = "merchant_cat_id IN (" . $children_cat.")";
			$dbview->where(RC_DB::raw('g.merchant_cat_id'), $children_cat);
		}
    	
    }

}