<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 从回收站返回商品
 * @author will
 *
 */
class restore_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$result = $ecjia->admin_priv('remove_back');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		$id		= _POST('id');
		$id 	= explode(',', $id);
		if (empty($id)) {
			EM_Api::outPut(101);
		}
		$data = array(
				'is_delete' => 0,
				'last_update' => RC_Time::gmtime()
		);
		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		
		$where = array('goods_id' => $id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		$db_goods->where($where)->update($data);
		
		foreach ($id as $val) {
			$goods_name = $db_goods->where(array('goods_id' => $val))->get_field('goods_name');
			ecjia_admin::admin_log(addslashes($goods_name), 'restore', 'goods'); // 记录日志
		}
		
		return array();
	}
	
	
}


// end