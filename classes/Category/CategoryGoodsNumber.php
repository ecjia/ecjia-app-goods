<?php
/**
 * Created by PhpStorm.
 * User: royalwang
 * Date: 2019-04-25
 * Time: 18:26
 */

namespace Ecjia\App\Goods\Category;


use Ecjia\App\Goods\Models\GoodsCatModel;
use Ecjia\App\Goods\Models\GoodsModel;

class CategoryGoodsNumber
{

    /**
     * 获取每个分类下的商品数量
     *
     * @return \Royalcms\Component\Support\Collection
     */
    public static function getGoodsNumberWithCatId()
    {
        $cache_key = 'query_all_categories_every_goods_count';

        $collection = ecjia_cache('goods')->get($cache_key);

        if (empty($collection)) {

            /**
             * @var $collection1 \Royalcms\Component\Database\Eloquent\Collection
             * @var $collection2 \Royalcms\Component\Database\Eloquent\Collection
             */
            $collection1 = GoodsModel::select('cat_id', 'goods_id')
                ->where('is_delete', 0)
                ->get()
                ->groupBy('cat_id');


            $collection2 = GoodsCatModel::select('goods_cat.cat_id', 'goods_cat.goods_id')
                ->leftJoin('goods', 'goods.goods_id', '=', 'goods_cat.goods_id')
                ->where('goods.is_delete', 0)
                ->get()
                ->groupBy('cat_id');


            $collection = self::countGoodsNumber($collection1, $collection2);

            ecjia_cache('goods')->put($cache_key, $collection, 60);
        }

        return $collection;
    }

    /**
     * 获取在销售的每个分类下的商品数量
     *
     * @return \Royalcms\Component\Support\Collection
     */
    public static function getOnsaleGoodsNumberWithCatId()
    {
        $cache_key = 'query_onsale_categories_every_goods_count';

        $collection = ecjia_cache('goods')->get($cache_key);

        if (empty($collection)) {
            /**
             * @var $collection1 \Royalcms\Component\Database\Eloquent\Collection
             * @var $collection2 \Royalcms\Component\Database\Eloquent\Collection
             */
            $collection1 = GoodsModel::select('cat_id', 'goods_id')
                ->where('is_delete', 0)
                ->where('is_on_sale', 1)
                ->get()
                ->groupBy('cat_id');

            $collection2 = GoodsCatModel::select('goods_cat.cat_id', 'goods_cat.goods_id')
                ->leftJoin('goods', 'goods.goods_id', '=', 'goods_cat.goods_id')
                ->where('goods.is_delete', 0)
                ->where('goods.is_on_sale', 1)
                ->get()
                ->groupBy('cat_id');

            $collection = self::countGoodsNumber($collection1, $collection2);

            ecjia_cache('goods')->put($cache_key, $collection, 60);
        }

        return $collection;
    }

    /**
     * 计数分类商品数量
     * @param \Royalcms\Component\Support\Collection $collection1
     * @param \Royalcms\Component\Support\Collection $collection2
     */
    public static function countGoodsNumber($collection1, $collection2)
    {
        $keys = array_merge($collection1->keys()->all(), $collection2->keys()->all());

        $collection = collect($keys)->mapWithKeys(function($item) use ($collection1, $collection2) {
            if ($item > 0) {
                if ($collection1->get($item)) {
                    $value1 = $collection1->get($item);
                    $value1 = $value1->pluck('goods_id')->all();
                }

                if ($collection2->get($item)) {
                    $value2 = $collection2->get($item);
                    $value2 = $value2->pluck('goods_id')->all();
                }

                return [
                    $item => collect(array_merge($value1, $value2))->unique()->count()
                ];
            }
        });

        return $collection;
    }

}