<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 转移商品分类
 * @author chenzhejun@ecmoban.com
 *
 */
class category_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('goods_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
    	$category_id	= _POST('category_id');//起始分类
    	$goods_id		= _POST('goods_id');
    	$target_category_id	= _POST('target_category_id');//目标分类
    	
    	/* 商品分类不允许为空 */
    	if (empty($category_id) || empty($target_category_id) || empty($goods_id)) {
    		EM_Api::outPut(101);
    	}
    	
    	/* 获取商品*/
    	$goods_id = explode(',', $goods_id);
    	$where = array('goods_id' => $goods_id);
    	if ($_SESSION['ru_id'] > 0) {
    		$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
    	}
    	$goods_ids = RC_Model::model('goods/goods_model')->where($where)->get_field('goods_id', true);
    	
    	/* 更新商品分类 */
    	$data = array('cat_id' => $target_category_id);
    	$new_cat_name = RC_Model::model('goods/category_model')->where(array('cat_id' => $target_category_id))->get_field('cat_name');
    	$old_cat_name = RC_Model::model('goods/category_model')->where(array('cat_id' => $category_id))->get_field('cat_name');
    	$query = RC_Model::model('goods/goods_model')->where(array('goods_id' => $goods_id))->update($data);
    	
    	ecjia_admin::admin_log($old_cat_name.'下商品id为：'.implode(',', $goods_ids).'转移到'.$new_cat_name, 'edit', 'category');
    	
    	return array();
    	
    }
    	 
    
}