<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 是否包邮
 * @author will
 *
 */
class free_shipping_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$result = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		
		$goods_id	= _POST('id');
		$is_shipping	= _POST('is_free', 0);
		if (empty($goods_id)) {
			EM_Api::outPut(101);
		}
		
		$data = array(
			'is_shipping'	=> $is_shipping,
			'last_update'	=> RC_Time::gmtime()
		);
		
		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		
		$where = array('goods_id' => $goods_id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		$db_goods->where($where)->update($data);
		
		/* 记录日志 */
		$goods_name = $db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		
		if ($is_shipping == '1') {
			ecjia_admin::admin_log('设为包邮，'.$goods_name, 'setup', 'goods');
		} else {
			ecjia_admin::admin_log('取消包邮，'.$goods_name, 'setup', 'goods');
		}
		
		return array();
	}
	
	
}


// end