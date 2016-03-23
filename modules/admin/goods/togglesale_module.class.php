<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 上下架商品
 * @author will
 *
 */
class togglesale_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$result = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		
		RC_Loader::load_app_func('global', 'goods');
		$id = _POST('id');
		$type = _POST('type');//online 上架;offline下架
		if (empty($id) || empty($type)) {
			EM_Api::outPut(101);
		}
		$on_sale = $type == 'online' ? 1 : 0;
		
		
		
		$data = array(
				'is_on_sale' => $on_sale,
				'last_update' => RC_Time::gmtime()
		);
		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		
		
		
		$where = array('goods_id' => $id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		$db_goods->where($where)->update($data);
		
		$goods_name = $db_goods->where(array('goods_id' => $id))->get_field('goods_name');
		if($on_sale == '1') {
			ecjia_admin::admin_log('上架商品，'.$goods_name, 'setup', 'goods');
		}else{
			ecjia_admin::admin_log('下架商品，'.$goods_name, 'setup', 'goods');
		}
		return array();
	}
	
	
}


// end