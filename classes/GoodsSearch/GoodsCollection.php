<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-25
 * Time: 09:10
 */

namespace Ecjia\App\Goods\GoodsSearch;


use Ecjia\App\Goods\GoodsSearch\Formats\GoodsAdminFormatted;
use Royalcms\Component\Database\Eloquent\Builder;
use Royalcms\Component\Http\Request;

class GoodsCollection
{

    protected $input = [];

    protected $request;

    /**
     * @var \ecjia_page
     */
    protected $ecjia_page;

    public function __construct(array $input)
    {
        $this->input = $input;

        $this->request = Request::create('', 'GET', $input);
    }

    public function getCollection()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 15);

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
            $count = GoodsSearch::singleton()->applyCount($this->request);

            $this->ecjia_page = new \ecjia_page($count, $size, null, '', $page);

            $start = $this->ecjia_page->start_id - 1;

            $input['current_page'] = [$start, $size];

            $this->request->replace($input);
        }

        $collection = GoodsSearch::singleton()->apply($this->request, function($query) {
            /**
             * @var Builder $query
             */
            $query->with('store_franchisee_model');
        });

        return $collection;
    }

    public function getData()
    {

        $collection = $this->getCollection();

        $user_rank_discount = $this->request->input('user_rank_discount', 1);
        $user_rank = $this->request->input('user_rank', 0);

        $collection = $collection->map(function($item) use ($user_rank_discount, $user_rank) {
            return (new GoodsAdminFormatted($item, $user_rank_discount, $user_rank))->toArray();
        });

        $data = $collection->toArray();

        return [
            'goods'     => $data,
            'filter'	=> $this->request->all(),
            'page'		=> $this->ecjia_page->show(2),
            'desc'		=> $this->ecjia_page->page_desc()
        ];
    }

    public static function test()
    {

         $input = [
             'store_id'             => 63,
             'product'              => true,
             'keywords'             => '西马渔场东星斑',
             'store_unclosed'       => 0,
         	 'is_delete'		    => 0,
         	 'is_on_sale'	        => 1,
         	 'is_alone_sale'	    => 1,
         	 'review_status'        => 2,
 			 'store_best'           => 1,
         	 'store_hot'            => 1,
         	 'store_new'            => 1,
         	 'promotion'            => 0,
         	 'is_best'		        => 1,
         	 'is_hot'		        => 1,
         	 'is_new'		        => 1,
             'cat_id'               => 1036,
             'shop_price_less_than' => 10,
             'shop_price_more_than' => 5,
 			 'no_need_cashier_goods' => true,
         	 'page'                 => 1,
         ];

         $collection = (new GoodsCollection($input))->getData();

         return $collection;
    }

}