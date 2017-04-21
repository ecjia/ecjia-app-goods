<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 切换商品分类是否显示
 * @author chenzhejun@ecmoban.com
 *
 */
class show_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('cat_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	if (!empty($_SESSION['ru_id'])) {
    		return new ecjia_error('priv_error', '您无权对此分类进行操作！');
    	}
    	
    	$category_id = _POST('category_id');
    	$is_show	 = _POST('is_show', 1);
    	if (empty($category_id)) {
    		EM_Api::outPut(101);
    	}
    	
    	$name = RC_Model::model('goods/category_model')->where(array('cat_id' => $category_id))->get_field('cat_name');
    	RC_Model::model('goods/category_model')->where(array('cat_id' => $category_id))->update(array('is_show' => $is_show));
    	ecjia_admin::admin_log($name."切换显示状态", 'edit', 'category');
    	RC_Cache::app_cache_delete('cat_list', 'goods');

    	return array();
    	
    }
    	 
    
}