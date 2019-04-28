<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-26
 * Time: 14:45
 */

namespace Ecjia\App\Goods\Collections;


use Ecjia\App\Goods\GoodsSearch\GoodsSearch;
use Royalcms\Component\Http\Request;
use RC_DB;

class GoodsCountable
{

    protected $input = [];

    protected $request;

    public function __construct(array $input)
    {
        if (isset($input['page'])) {
            unset($input['page']);
        }

        $this->input = $input;

        $this->request = Request::create('', 'GET', $input);
    }

    public function getData()
    {
        $model = GoodsSearch::singleton()->applyFirst($this->request, function($query) {
            /**
             * @var \Royalcms\Component\Database\Schema\Builder $query
             */
            $query->select(RC_DB::raw('count(`goods_id`) as `count_goods_num`'),
                RC_DB::raw('SUM(IF(`is_on_sale` = 1, 1, 0)) as `count_on_sale`'),
                RC_DB::raw('SUM(IF(`is_on_sale` = 0, 1, 0)) as `count_not_sale`')
            );
        });

        return collect($model->toArray());
    }

    public static function test()
    {
        $input = [
            'is_delete'		=> 0,
        ];

        $collection = (new static($input))->getData();

        return $collection;
    }

}