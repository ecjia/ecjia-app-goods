<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 添加商品促销价格
 * @author chenzhejun@ecmoban.com
 *
 */
class add_module implements ecjia_interface
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
    	$is_promote		= _POST('is_promote', 1);
    	$promote_price	= _POST('promote_price', 0.00);
    	$start_date		= _POST('start_date', '');
    	$end_date		= _POST('end_date', '');
    	
    	
    	$where = array('goods_id' => $goods_id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
    	
		$start_date = RC_Time::local_strtotime($start_date);
		$end_date	= RC_Time::local_strtotime($end_date);
		if ($start_date >= $end_date) {
		    return new ecjia_error('time_error', '促销开始时间不能大于结束时间');
		}
    	
    	$rs = RC_Model::model('goods/goods_model')->where($where)->update(array(
    															'is_promote'	=> 1,
    															'promote_price'	=> $promote_price,
    															'promote_start_date'	=> $start_date,
    															'promote_end_date'		=> $end_date,
    	));
    	
    	if (! $rs) {
    	    EM_Api::outPut(8);
    	}
    	
    	$goods_name = RC_Model::model('goods/goods_model')->where($where)->get_field('goods_name');
    	ecjia_admin::admin_log('更新商品促销价格：'.addslashes($goods_name), 'edit', 'goods');
    	return array();
    	
    }
    	 
    
}