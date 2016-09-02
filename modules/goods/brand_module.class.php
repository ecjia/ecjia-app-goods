<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 某一分类的品牌列表
 * @author royalwang
 *
 */
class brand_module extends api_front implements api_interface {
	
	public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
		$this->authSession();
		
		$data = array();
		$cat_id = $this->requestData('category_id', 0);
		
		$dbview = RC_Loader::load_app_model('brand_goods_goods_cat_viewmodel', 'goods');
		RC_Loader::load_app_func('category', 'goods');
		/*
		    SELECT b.brand_id,b.brand_name,b.brand_logo,COUNT(*) AS goods_num 
            FROM  ecs_goods AS g 
            LEFT JOIN ecs_brand AS b  ON g.brand_id = b.brand_id 
            LEFT JOIN ecs_goods_cat AS gc  ON g.goods_id = gc.goods_id  
            WHERE  gc.cat_id IN(1)   
            AND (g.cat_id  IN ('1')  OR gc.cat_id  IN ('1') ) 
            AND  b.is_show=1  
            AND  g.is_on_sale=1  
            AND  g.is_alone_sale=1  
            AND  g.is_delete=0   
            GROUP BY b.brand_id 
            HAVING goods_num > 0 
            ORDER BY  b.sort_order ASC, b.brand_id ASC 
		 */
		
		if (!empty($cat_id)) {
			$children = get_children($cat_id);
			$cat_list = cat_list($cat_id, 0, false);
			$cat_list_keys = array_merge(array($cat_id), array_keys($cat_list));
			$where = array(
			    '(' . $children . ' OR gc.cat_id ' . db_create_in(array_unique($cat_list_keys)) . ')',
				'b.is_show' => 1,
			    'g.is_on_sale' => 1,
			    'g.is_alone_sale' => 1,
			    'g.is_delete' => 0
			);
			$brand_list = $dbview->join(array('brand' , 'goods_cat'))
			                     ->in(array('gc.cat_id' => array_unique($cat_list_keys)))
			                     ->where($where)
			                     ->group('b.brand_id')
			                     ->having('goods_num > 0')
			                     ->order(array('b.sort_order' => 'ASC', 'b.brand_id' => 'ASC'))
			                     ->select();
		} else {
			RC_Loader::load_app_func('common', 'goods');
			$brand_list = get_brands();
		}
		
		foreach ($brand_list as $key => $val) {
			$data[] = array(
				'url' => RC_Config::system('CUSTOM_UPLOAD_SITE_URL') . '/data/brandlogo/' . $val['brand_logo'],
				'brand_name' => $val['brand_name'],
				'brand_id' => $val['brand_id']
			);
		}
		
		return $data;
	}
}


// end