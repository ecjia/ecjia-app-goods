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
 * 商品价格大于等于某个值条件
 * @author Administrator
 *
 */
class ShopPriceMoreThan implements FilterInterface
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
//     	if (isset($filter['min']) && $filter['min'] > 0) {
// 			$dbview->where(RC_DB::raw('g.shop_price'), '>=', $filter['min']);
// 		}
    	if ($value && $value > 0) {
    		return $builder->where('shop_price', '>=', $value);
    	}
    }

}