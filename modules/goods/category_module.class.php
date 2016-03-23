<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获取所有商品分类
 * @author royalwang
 *
 */
class category_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$categoryGoods = array();
		RC_Loader::load_app_func('goods', 'goods');
		$category = get_categories_tree();
		$category = array_merge($category);
		if (!empty($category)) {
			foreach($category as $key=>$val) {
				$categoryGoods[$key]['id'] = $val['id'];
				$categoryGoods[$key]['name'] = $val['name'];
				$categoryGoods[$key]['image'] = $val['img'];
				if(!empty($val['cat_id'])){
					foreach($val['cat_id'] as $k=>$v){
						$categoryGoods[$key]['children'][] = array(
							'id'     => $v['id'],
						    'name'   => $v['name'],	
							'image'	 => $v['img'],	
						);
					}
				} else {
					$categoryGoods[$key]['children'] = array();
				}
			}
		}
		return $categoryGoods;

	}
}


// end