<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 删除商品分类
 * @author chenzhejun@ecmoban.com
 *
 */
class delete_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('cat_drop');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	$cat_id = _POST('category_id');
    	if (empty($cat_id)) {
    		EM_Api::outPut(101);
    	}
    	
		/* 删除入驻商分类*/
		$category	= RC_Model::model('goods/merchants_category_model')->where(array('cat_id' => $cat_id, 'user_id' => $_SESSION['ru_id']))->find();
		if (empty($category)) {
			return new ecjia_error('priv_error', '您无权对此分类进行操作！');
		}
		$cat_count	= RC_Model::model('goods/merchants_category_model')->where(array('parent_id' => $cat_id))->count();
		$goods_count = RC_Model::model('goods/goods_model')->where(array('user_cat' => $cat_id))->count();
		if ($cat_count == 0 && $goods_count == 0) {
			$del_result = RC_Model::model('goods/merchants_category_model')->where(array('cat_id'	=> $cat_id, 'user_id' => $_SESSION['ru_id']))->delete();
			ecjia_admin::admin_log($category['cat_name'], 'remove', 'category');
		} else {
			return new ecjia_error('category_delete_error','该分类下有商品或非末级分类！');
		}
    	
    	return array();
    	
    }
    	 
    
}