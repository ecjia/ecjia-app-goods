<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 搜索商品（搜索商品）
 * @author luchongchong
 *
 */
class product_search_module implements ecjia_interface
{
    public function run(ecjia_api & $api)
    { 
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$goods_sn = _POST('goods_sn');
		$device = _POST('device', array());
    	$device_code = $device['code'];
    	if (empty($goods_sn)) {
    		EM_Api::outPut(101);
    	}
		$db_goods = RC_Loader::load_app_model('goods_viewmodel','goods');
    	 	
		$db_goods->view = array(
			'products' => array(
					'type'		=> Component_Model_View::TYPE_LEFT_JOIN,
					'alias'		=> 'p',
					'on'		=> 'g.goods_sn=p.product_sn'
			)
		);
		
		$field = 'g.goods_id, g.goods_name, g.shop_price, g.shop_price, g.goods_sn, g.goods_img, g.original_img, g.goods_thumb, p.goods_attr, p.product_sn';

    	$where[] = "(goods_sn like '%".$goods_sn."%' OR  product_sn like '%".$goods_sn."%')";
    	if ($device_code == '8001') {
    		$where = array_merge($where, array('is_delete' => 0, 'is_on_sale' => 1, 'is_alone_sale' => 1));
    		if (ecjia::config('review_goods')) {
    			$where['review_status'] = array('gt' => 2);
    		}
    	}
    	
    	$arr = $db_goods->field($field)->join(array('products'))->where($where)->select();
    	$product_search = array();
		if (!empty($arr)) {
			foreach ($arr as $k => $v){
				$product_search[] = array(
					'id'					=> $v['goods_id'],
					'name'					=> $v['goods_name'],
					'shop_price'			=> $v['shop_price'],
					'formatted_shop_price'	=> price_format($v['shop_price']),
					'goods_sn'				=> empty($v['product_sn']) ? $v['goods_sn'] : $v['product_sn'],
					'attribute'				=> $v['goods_attr'],
					'img' => array(
						'thumb'	=> API_DATA('PHOTO', $v['goods_img']),
						'url'	=> API_DATA('PHOTO', $v['original_img']),
						'small'	=> API_DATA('PHOTO', $v['goods_thumb'])
					),
	    	 	);
			}
		}
		return $product_search;
    }
}