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
use goods_category;


/**
 * 平台商品分类条件
 * @author Administrator
 *
 */
class PlatformGoodsCategory implements FilterInterface
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
    	/* 分类条件*/
//     	if (isset($filter['cat_id']) && !empty($filter['cat_id'])) {
//     		$children = goods_category::get_children($filter['cat_id']);
//     	}
//     	if (!empty($children)) {
//     		//$where[] = "(". $children ." OR ".goods_category::get_extension_goods($children).")";
//     		$get_extension_goods = goods_category::get_extension_goods($children);
//     		if (!empty($get_extension_goods)) {
//     			$dbview->whereRaw(" (". $children ." OR ".$get_extension_goods.") ");
//     		} else {
//     			$dbview->whereRaw($children);
//     		}
//     	}

    	/* 分类条件*/
    	\RC_Loader::load_app_class('goods_category', 'goods', false);
    	if ($value && !empty($value)) {
    		$children = goods_category::get_children($value);
    	}
    	if (!empty($children)) {
    		$get_extension_goods = goods_category::get_extension_goods($children);
    		if (!empty($get_extension_goods)) {
    			return $builder->whereRaw(" (". $children ." OR ".$get_extension_goods.") ");
    		} else {
    			return $builder->whereRaw($children);
    		}
    	}
    }

}