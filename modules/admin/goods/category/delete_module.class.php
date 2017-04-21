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
    	$result = $ecjia->admin_priv('cat_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	$cat_id = _POST('category_id');
    	if (empty($cat_id) || !empty($_SESSION['ru_id'])) {
    		EM_Api::outPut(101);
    	}
    	
		$category	= RC_Model::model('goods/category_model')->where(array('cat_id' => $cat_id))->find();
		$cat_count	= RC_Model::model('goods/category_model')->where(array('parent_id' => $cat_id))->count();
		$goods_count = RC_Model::model('goods/goods_model')->where(array('cat_id' => $cat_id))->count();
		if ($cat_count == 0 && $goods_count == 0) {
			$old_logo = $category['style'];
			if (!empty($old_logo)) {
				$disk = RC_Filesystem::disk();
				$disk->delete(RC_Upload::upload_path() . $old_logo);
			}
			$query = RC_Model::model('goods/category_model')->where(array('cat_id' => $cat_id))->delete();
			if ($query) {
				$db_nav = RC_Loader::load_model('nav_model');
				$db_nav->where(array('ctype' => 'c', 'cid' => $cat_id, 'type' => 'middle'))->delete();
				ecjia_admin::admin_log($category['cat_name'], 'remove', 'category');
			}
		} else {
			return new ecjia_error('category_delete_error','该分类下有商品或非末级分类！');
		}
    	
    	
    	return array();
    	
    }
    	 
    
}