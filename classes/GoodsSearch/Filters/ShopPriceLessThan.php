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
 * 商品价格小于等于某个值条件
 * @author Administrator
 *
 */
class ShopPriceLessThan implements FilterInterface
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
//     	if (isset($filter['max']) && $filter['max'] > 0) {
// 			$dbview->where(RC_DB::raw('g.shop_price'), '<=', $filter['max']);
// 		}
		if ($value && $value > 0) {
			return $builder->where('shop_price', '<=', $value);
		}
    }

}