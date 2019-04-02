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
 * 平台推荐商品
 * @author Administrator
 *
 */
class PlatformRecommendGoods implements FilterInterface
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
    	if (!empty($filter['intro'])) {
			switch ($filter['intro']) {
				case 'best':
					//$where['g.is_best'] = 1;
					$dbview->where(RC_DB::raw('g.is_best'), 1);
					break;
				case 'new':
					//$where['g.is_new'] = 1;
					$dbview->where(RC_DB::raw('g.is_new'), 1);
					break;
				case 'hot':
					//$where['g.is_hot'] = 1;
					$dbview->where(RC_DB::raw('g.is_hot'), 1);
					break;
				case 'promotion':
					$time    = RC_Time::gmtime();
					
					$dbview->where(RC_DB::raw('g.promote_price'), '>', 0);
					$dbview->where(RC_DB::raw('g.promote_start_date'), '<=', $time);
					$dbview->where(RC_DB::raw('g.promote_end_date'), '>=', $time);
					
					break;
				default:
			}
		}
    }

}