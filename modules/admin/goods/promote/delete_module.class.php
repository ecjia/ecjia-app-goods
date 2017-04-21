<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 删除商品促销价格
 * @author chenzhejun@ecmoban.com
 *
 */
class delete_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('goods_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	$goods_id		= _POST('id');
    	if (empty($goods_id)) {
    		EM_Api::outPut(101);
    	}
    	
    	$where = array('goods_id' => $goods_id);
    	if ($_SESSION['ru_id'] > 0) {
    		$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
    	}
    	
    	RC_Model::model('goods/goods_model')->where($where)->update(array(
    															'is_promote'	=> 0,
    															'promote_price'	=> 0,
    															'promote_start_date'	=> 0,
    															'promote_end_date'		=> 0,
    	));
    	
    	$goods_name = RC_Model::model('goods/goods_model')->where($where)->get_field('goods_name');
    	ecjia_admin::admin_log('删除商品促销价格：'.addslashes($goods_name), 'edit', 'goods');
    	return array();
    	
    }
    	 
    
}