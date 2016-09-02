<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 价格范围
 * @author royalwang
 *
 */
class price_range_module extends api_front implements api_interface {

    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();
    	
		$db_goods = RC_Loader::load_app_model('goods_brand_member_viewmodel', 'goods');
		RC_Loader::load_app_func('category', 'goods');
		RC_Loader::load_app_func('global','goods');
		RC_Loader::load_app_func('goods','goods');
		
    	$data = array();
        $cat_id = _POST('category_id', 0);
        
        $brand = _POST('brand',0);
        $filter_attr_str = _POST('filter_attr',0);
        $filter_attr_str = trim(RC_String::unicode2string($filter_attr_str));
        $filter_attr_str = preg_match('/^[\d\.]+$/',$filter_attr_str) ? $filter_attr_str : '';
        $price_min = _POST('price_min',0);
        $price_max = _POST('price_max',0);
        $children = get_children($cat_id);
        
        
        $cat = get_cat_info($cat_id); // 获得分类的相关信息
        
        if ($cat['grade'] == 0 && $cat['parent_id'] != 0) {
            $cat['grade'] = get_parent_grade($cat_id); // 如果当前分类级别为空，取最近的上级分类
        }
        
        if ($cat['grade'] > 1) {
            // 获得当前分类下商品价格的最大值、最小值

            $row = $db_goods->join(null)->field('min(g.shop_price) AS min, max(g.shop_price) as max')->find("($children OR " . get_extension_goods($children) . ") AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1");
            
            // 取得价格分级最小单位级数，比如，千元商品最小以100为级数
            $price_grade = 0.0001;
            for ($i = - 2; $i <= log10($row['max']); $i ++) {
                $price_grade *= 10;
            }
            
            // 跨度
            $dx = ceil(($row['max'] - $row['min']) / ($cat['grade']) / $price_grade) * $price_grade;
            if ($dx == 0) {
                $dx = $price_grade;
            }
            
            for ($i = 1; $row['min'] > $dx * $i; $i ++);
            
            for ($j = 1; $row['min'] > $dx * ($i - 1) + $price_grade * $j; $j ++);
            $row['min'] = $dx * ($i - 1) + $price_grade * ($j - 1);
            
            for (; $row['max'] >= $dx * $i; $i ++);
            $row['max'] = $dx * ($i) + $price_grade * ($j - 1);
            

			$where = array(
				'('.$children.' OR ' . get_extension_goods($children) . ')',
				'g.is_delete' =>0,
				'g.is_on_sale' =>1,
				'g.is_alone_sale' =>1
			);
            $price_grade = $db_goods->join(null)
            						->field("(FLOOR((g.shop_price - $row[min]) / $dx)) AS sn, COUNT(*) AS goods_num")
            						->where($where)
            						->group('sn')
            						->select();
            foreach ($price_grade as $key => $val) {
                $temp_key = $key + 1;
                $price_grade[$temp_key]['goods_num']      = $val['goods_num'];
                $price_grade[$temp_key]['start']          = $row['min'] + round($dx * $val['sn']);
                $price_grade[$temp_key]['end']            = $row['min'] + round($dx * ($val['sn'] + 1));
                $price_grade[$temp_key]['price_range']    = $price_grade[$temp_key]['start'] . '&nbsp;-&nbsp;' . $price_grade[$temp_key]['end'];
                $price_grade[$temp_key]['formated_start'] = price_format($price_grade[$temp_key]['start']);
                $price_grade[$temp_key]['formated_end']   = price_format($price_grade[$temp_key]['end']);
                $price_grade[$temp_key]['url']            = build_uri('category', array(
																'cid' => $cat_id,
																'bid' => $brand,
																'price_min' => $price_grade[$temp_key]['start'],
																'price_max' => $price_grade[$temp_key]['end'],
																'filter_attr' => $filter_attr_str
															), $cat['cat_name']);
                
                /* 判断价格区间是否被选中 */
                if (isset($price_min) && $price_grade[$temp_key]['start'] == $price_min && $price_grade[$temp_key]['end'] == $price_max) {
                    $price_grade[$temp_key]['selected'] = 1;
                } else {
                    $price_grade[$temp_key]['selected'] = 0;
                }
            }
            unset($price_grade[0]);
        }
        
        if (empty($price_grade)) {
            $data = array();
        } else {
            foreach ($price_grade as $key => $val) {
                $data[] = array(
                    'price_min' => $val['start'],
                    'price_max' => $val['end']
                );
            }
        }
        return $data;               
    }
}

// end