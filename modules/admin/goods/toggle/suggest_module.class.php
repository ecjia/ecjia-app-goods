<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 热销推荐切换
 * @author will
 *
 */
class suggest_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		$result = $ecjia->admin_priv('goods_manage');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		
		$goods_id	= _POST('id');
		$type		= _POST('type');//best 精品，new 新品，hot 热销
		$is_suggest	= _POST('is_suggest', 0);
		if (empty($goods_id) || empty($type)) {
			EM_Api::outPut(101);
		}
		
		$data = array(
			'last_update' => RC_Time::gmtime()
		);
		
		if ($type == 'best') {
			$data['is_best'] = $is_suggest;
			$log_label = '精品';
		} elseif ($type == 'new') {
			$data['is_new'] = $is_suggest;
			$log_label = '新品';
		} elseif ($type == 'hot') {
			$data['is_hot'] = $is_suggest;
			$log_label = '热销';
		}
		
		
		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		
		$where = array('goods_id' => $goods_id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		$db_goods->where($where)->update($data);
		
		/* 记录日志 */
		$goods_name = $db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
		
		if ($is_suggest == '1') {
			ecjia_admin::admin_log('设为' . $log_label . '，'.$goods_name, 'setup', 'goods');
		} else {
			ecjia_admin::admin_log('取消' . $log_label . '，'.$goods_name, 'setup', 'goods');
		}
		
		return array();
	}
	
	
}


// end