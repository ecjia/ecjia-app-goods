<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-03
 * Time: 19:52
 */

namespace Ecjia\App\Goods\GoodsSearch;


use Ecjia\App\Goods\GoodsSearch\Formats\GoodsApiFormatted;
use Royalcms\Component\Http\Request;

class GoodsApiCollection
{
    protected $input = [];

    protected $request;

    public function __construct(array $input)
    {
        $this->input = $input;

        $this->request = Request::create('', 'GET', $input);
    }

    public function getData()
    {

        $count = GoodsSearch::applyCount($this->request);

        $page = $this->request->input('page');
        $size = 15;

        $ecjia_page = new \ecjia_page($count, $size, $page);
        $start = $ecjia_page->start_id - 1;

        $input['page'] = [$start, $size];

        $this->request->replace($input);

        $collection = GoodsSearch::apply($this->request);
        $collection = $collection->map(function($item) {
            return (new GoodsApiFormatted($item))->toArray();
        });
        $data = $collection->toArray();


        $pager = array(
            'total' => $ecjia_page->total_records,
            'count' => $ecjia_page->total_records,
            'more'  => $ecjia_page->total_pages <= $page ? 0 : 1,
        );

        return ['goods_list' => $data, 'pager' => $pager];

    }


    public static function test()
    {

//     	$aa = MerchantGoodsCategory::getChildrenCategoryId('16', '63');

        $input = [
            'store_id' => 62,
            'product' => true,
            'page' => 1,

//             'keywords' => '开心果',
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

        ];

        $collection = (new GoodsApiCollection($input))->getData();

        return $collection;
    }

}