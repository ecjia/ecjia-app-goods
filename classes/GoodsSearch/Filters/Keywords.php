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
 * 商品关键字条件
 * @author Administrator
 *
 */
class Keywords implements FilterInterface
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
    	if (isset(self::$keywords_where['keywords']) && !empty(self::$keywords_where['keywords']) && isset($filter['keywords']) && !empty($filter['keywords'])) {
			//$where[] = self::$keywords_where['keywords'];
			$where_keywords = self::$keywords_where['keywords'];
			$dbview->whereRaw($where_keywords);
		}
		
    }

}