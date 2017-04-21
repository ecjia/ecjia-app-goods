<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品相信信息
 * @author chenzhejun@ecmoban.com
 *
 */
class detail_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('cat_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	$cat_id = _POST('category_id');
    	if (empty($cat_id)) {
    		EM_Api::outPut(101);
    	}
    	$where = array('cat_id' => $cat_id);
    	if (!empty($_SESSION['ru_id'])) {
    		$where['user_id'] = $_SESSION['ru_id'];
    	}
    	$category_info = RC_Model::model('goods/merchants_category_model')->where($where)->find();
    	 
    	if (empty($category_info)) {
    		return new ecjia_error('category_empty', '未找到对应分类！');
    	}
    	
    	RC_Loader::load_app_func('category', 'goods');
    	$category_detail = array(
			'category_id'	=> $category_info['cat_id'],
			'category_name'	=> $category_info['cat_name'],
			'category_image'	=> !empty($category_info['style']) ? RC_Upload::upload_url($category_info['style']) : '',
    	    'category' => get_parent_cats($category_info['cat_id'], 1, $_SESSION['ru_id']),
			'is_show'		=> $category_info['is_show'],
			'goods_count'	=> RC_Model::model('goods/goods_model')->where(array('cat_id' => $cat_id))->count(),
    	);
    	 
    	return $category_detail;
    	
    }
    	 
    
}