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
class MerchantCatIdUndefinedAndStoreId implements FilterInterface
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
		$merchant_cat_id = $value['0'];
		return	$builder->where('merchant_cat_id', $merchant_cat_id);
    }

}