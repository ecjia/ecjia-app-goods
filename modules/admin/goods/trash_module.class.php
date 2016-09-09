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
		if ($_SESSION['admin_id'] <= 0) {
			return new ecjia_error(100, 'Invalid session');
		}
		if (!$this->admin_priv('remove_back')) {
			return new ecjia_error('privilege_error', '对不起，您没有执行此项操作的权限！');
		}
		
		$id = $this->requestData('id');
		if (empty($id)) {
			return new ecjia_error('not_exists_info', '不存在的信息');
		}
		$data = array(
				'is_delete' => 1,
				'last_update' => RC_Time::gmtime()
		);
		$db_goods = RC_Model::model('goods/goods_model');
		
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