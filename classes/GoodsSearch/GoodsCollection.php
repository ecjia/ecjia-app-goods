<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-25
 * Time: 09:10
 */

namespace Ecjia\App\Goods\GoodsSearch;


use Ecjia\App\Goods\GoodsSearch\Formats\GoodsAdminFormatted;
use Royalcms\Component\Http\Request;

class GoodsCollection
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
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 15);

        $user_rank_discount = $this->request->input('user_rank_discount', 1);
        $user_rank = $this->request->input('user_rank', 0);

        if ($page) {
            $input = $this->request->input();
            if (array_key_exists('size', $input)) {
                unset($input['size']);
            }
            if (array_key_exists('user_rank_discount', $input)) {
                unset($input['user_rank_discount']);
            }
            if (array_key_exists('user_rank', $input)) {
                unset($input['user_rank']);
            }
            if (array_key_exists('page', $input)) {
                unset($input['page']);
            }
            $count = GoodsSearch::applyCount($this->request);

            $ecjia_page = new \ecjia_page($count, $size, 6, '', $page);

            $start = $ecjia_page->start_id - 1;

            $input['current_page'] = [$start, $size];

            $this->request->replace($input);

            $pager = array(
                'total' => $ecjia_page->total_records,
                'count' => $ecjia_page->total_records,
                'more'  => $ecjia_page->total_pages <= $page ? 0 : 1,
            );
        }

        $collection = GoodsSearch::apply($this->request, function($query) {
            $query->with('store');
        });
        $collection = $collection->map(function($item) use ($user_rank_discount, $user_rank) {
            return (new GoodsAdminFormatted($item, $user_rank_discount, $user_rank))->toArray();
        });

        $data = $collection->toArray();

        return ['goods' => $data, 'pager' => $pager];
    }

    public static function test()
    {


         $input = [
//             'store_id' => 63,
//             'product' => true,
//             'keywords' => '西马渔场东星斑',
//             'store_unclosed' => 0,
         	 'is_delete'		=> 0,
//         	 'is_on_sale'	=> 1,
//         	 'is_alone_sale'	=> 1,
//         	 'review_status' => 2,
// 			 'store_best' => 1,
//         	 'store_hot' => 1,
//         	 'store_new' => 1,
//         	 'promotion' => 0,
//         	 'is_best'		=> 1,
//         	 'is_hot'		=> 1,
//         	 'is_new'		=> 1,
             'cat_id' => 1036,
//             'shop_price_less_than' => 10,
//             'shop_price_more_than' => 5,
// 			 'no_need_cashier_goods' => true,
         	 'page' => 1,

         ];

         $collection = (new GoodsCollection($input))->getData();

//         dd($collection);
         return $collection;
    }

}