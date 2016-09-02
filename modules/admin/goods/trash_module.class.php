<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 删除(回收站)/从回收站返回商品
 * @author will
 *
 */
class trash_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$result = $ecjia->admin_priv('remove_back');
		if (is_ecjia_error($result)) {
			EM_Api::outPut($result);
		}
		$id = _POST('id');
// 		$type = _POST('type');//remove删除;restore从回收站内返回;
		if (empty($id)) {
			EM_Api::outPut(101);
		}
		$data = array(
				'is_delete' => 1,
				'last_update' => RC_Time::gmtime()
		);
		$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
		
		$where = array('goods_id' => $id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		$db_goods->where($where)->update($data);
		
		$goods_name = $db_goods->where(array('goods_id' => $id))->get_field('goods_name');
		ecjia_admin::admin_log(addslashes($goods_name), 'trash', 'goods'); // 记录日志
		return array();
	}
	
	
}


// end