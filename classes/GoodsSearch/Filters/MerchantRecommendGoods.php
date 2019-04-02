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
 * 商家推荐商品
 * @author Administrator
 *
 */
class MerchantRecommendGoods implements FilterInterface
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
    	if (isset($filter['store_intro']) && !empty($filter['store_intro'])) {
			switch ($filter['store_intro']) {
				case 'best':
					//$where['g.store_best'] = 1;
					$dbview->where(RC_DB::raw('g.store_best'), 1);
					break;
				case 'new':
					//$where['g.store_new'] = 1;
					$dbview->where(RC_DB::raw('g.store_new'), 1);
					break;
				case 'hot':
					//$where['g.store_hot'] = 1;
					$dbview->where(RC_DB::raw('g.store_hot'), 1);
					break;
				case 'promotion':
					$time    = RC_Time::gmtime();
					//$where['g.promote_price']		= array('gt' => 0);
					//$where['g.promote_start_date']	= array('elt' => $time);
					//$where['g.promote_end_date']	= array('egt' => $time);
					$dbview->where(RC_DB::raw('g.promote_price'), '>', 0);
					$dbview->where(RC_DB::raw('g.promote_start_date'), '<=', $time);
					$dbview->where(RC_DB::raw('g.promote_end_date'), '>=', $time);
					break;
				default:
			}
		}
    }

}