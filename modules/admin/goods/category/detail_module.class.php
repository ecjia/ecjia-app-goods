<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品分类详情信息
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
    	if (!empty($_SESSION['ru_id'])) {
    		return new ecjia_error('priv_error', '您无权对此分类进行操作！');
    	}
    	
    	$category_info = RC_Model::model('goods/category_model')->where(array('cat_id' => $cat_id))->find();
    	 
    	if (empty($category_info)) {
    		return new ecjia_error('category_empty', '未找到对应分类！');
    	}
	    RC_Loader::load_app_func('category', 'goods');
	    RC_Loader::load_app_func('goods', 'goods');
	    $children = get_children($cat_id);
	    $where[] = "(".$children ." OR ".get_extension_goods($children).")";
	    $where['is_delete'] = 0;
    	 
    	$category_detail = array(
			'category_id'	=> $category_info['cat_id'],
			'category_name'	=> $category_info['cat_name'],
			'category_image'	=> !empty($category_info['style']) ? RC_Upload::upload_url($category_info['style']) : '',
	        'category' => get_parent_cats($category_info['cat_id']), 
			'is_show'		=> $category_info['is_show'],
			'goods_count'	=> RC_Model::model('goods/goods_viewmodel')->join(null)->where($where)->count(),
    	);
    	 
    	return $category_detail;
    	
    }
    	 
    
}