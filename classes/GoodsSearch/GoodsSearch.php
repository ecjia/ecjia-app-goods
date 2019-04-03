<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-01
 * Time: 18:20
 */

namespace Ecjia\App\Goods\GoodsSearch;

use Ecjia\App\Goods\Models\GoodsModel;
use Royalcms\Component\Database\Eloquent\Builder;
use Royalcms\Component\Http\Request;
use \Ecjia\App\Goods\GoodsSearch\MerchantGoodsCategory;

class GoodsSearch
{

    public static function test()
    {

//     	$aa = MerchantGoodsCategory::getChildrenCategoryId('16', '63');
    	
        $request = Request::create('', 'GET', [
//             'store_id' => 63,
//             'keywords' => '开心果210g',
//         	'store_unclosed' => 0,
//         	'is_delete'		=> 0,
//         	'is_on_sale'	=> 1,
//         	'is_alone_sale'	=> 1,
//         	'review_status' => 2,
//         	'merchant_cat_id_and_store_id' => [$aa, '63'],
//         	'merchant_cat_id_undefined_and_store_id' => ['0', '63'],
// 			'store_best' => 1,
//         	'store_hot' => 1,
//         	'store_new' => 1,
//         	'promotion' => 0,
//         	'is_best'		=> 1,
//         	'is_hot'		=> 1,
//         	'is_new'		=> 1,
//            'cat_id' => 1032,
//            'shop_price_less_than' => 10,
//            'shop_price_more_than' => 5, 
// 			'no_need_cashier_goods' => 'bulk',
			
        ]);

        $page = 1;
        $size = 15;
        return static::apply($request, $page, $size);
    }


    public static function apply(Request $filters, $page=1, $size=15)
    {

        $query = (new GoodsModel())->newQuery();

        $query = static::applyDecoratorsFromRequest($filters, $query);

        // 返回搜索结果
        return static::getResults($query, $page, $size);
    }


    private static function applyDecoratorsFromRequest(Request $request, Builder $query)
    {
        foreach ($request->all() as $filterName => $value) {
            $decorator = static::createFilterDecorator($filterName);

            if (static::isValidDecorator($decorator)) {
                $query = $decorator::apply($query, $value);
            }

        }

        return $query;
    }

    private static function createFilterDecorator($name)
    {
        return __NAMESPACE__ . '\\Filters\\' .
        str_replace(' ', '',
            ucwords(str_replace('_', ' ', $name)));
    }

    private static function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }

    private static function getResults(Builder $query, $page, $size)
    {
//     	return $query->get();
    	$count = $query->count();
    	$page_row = new \ecjia_page($count, 15, 6);
    	
    	$data = $query->take($size)->skip($page_row->start_id - 1)->get();
    	
    	$pager = array(
    			'total' => $page_row->total_records,
    			'count' => $page_row->total_records,
    			'more'  => $page_row->total_pages <= $page ? 0 : 1,
    	);
    	
    	return ['goods_list' => $data, 'pager' => $pager, 'page' => $page_row->show(2)];
    }

}