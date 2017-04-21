<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 切换是否为普通商品或配件赠品
 * @author will
 *
 */
class gifts_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$result = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		
		$goods_id	= _POST('id');
		$is_alone	= _POST('is_gift', 0);
		if (empty($goods_id)) {
			EM_Api::outPut(101);
		}
		
		$is_alone = $is_alone == 1 ? 0 : 1;
		
		$data = array(
			'is_alone_sale'	=> $is_alone,
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
		
		if ($is_alone == '1') {
			ecjia_admin::admin_log('设为普通商品销售，'.$goods_name, 'setup', 'goods');
		} else {
			ecjia_admin::admin_log('设为配件或赠品销售，'.$goods_name, 'setup', 'goods');
		}
		
		return array();
	}
	
	
}


// end