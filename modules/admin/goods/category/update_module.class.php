<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 编辑商品分类
 * @author chenzhejun@ecmoban.com
 *
 */
class update_module implements ecjia_interface
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
    	
    	$cat_id			= _POST('category_id');
    	$parent_id		= _POST('parent_id', 0);
    	$category_name	= _POST('category_name');
    	$is_show		= _POST('is_show', 1);
    	
    	if (empty($cat_id)) {
    		EM_Api::outPut(101);
    	}
    	
    	$cat = array(
    			'cat_name'	=> $category_name,
    			'parent_id'	=> $parent_id,
    			'is_show'	=> $is_show,
    	);
    	//判断上级分类是否正确
    	if ($parent_id) {
    	    $children = RC_Model::model('goods/category_model')->where(array('parent_id' => $cat_id))->get_field('cat_id', true);
    	    if (is_array($children) && in_array($parent_id, $children)) {
    	        return new ecjia_error('category_error', '上级分类不能为自己的子类');
    	    }
    	}
    	
    	/* 上传分类图片 */
    	$upload = RC_Upload::uploader('image', array('save_path' => 'data/category', 'auto_sub_dirs' => true));
    	if (isset($_FILES['category_image']) && $upload->check_upload_file($_FILES['category_image'])) {
    		$image_info = $upload->upload($_FILES['category_image']);
    		if (!empty($image_info)) {
    			$file_name = RC_Model::model('goods/category_model')->where(array('cat_id' => $cat_id))->get_field('style');
    			$upload->remove($file_name);
    			$cat['style'] = $upload->get_position($image_info);
    		}
    	}
    	 
    	$cat_id = RC_Model::model('goods/category_model')->where(array('cat_id' => $cat_id))->update($cat);
    	 
    	ecjia_admin::admin_log($category_name, 'edit', 'category');   // 记录管理员操作
    	RC_Cache::app_cache_delete('cat_list', 'goods');
    	 
    	$category_info = RC_Model::model('goods/category_model')->where(array('cat_id' => $cat_id))->find();
    	 
    	if (empty($category_info)) {
    		return new ecjia_error('category_empty', '未找到对应分类！');
    	}
    	 
    	$category_detail = array(
    			'category_id'	=> $category_info['cat_id'],
    			'category_name'	=> $category_info['cat_name'],
    			'category_image'	=> !empty($category_info['style']) ? RC_Upload::upload_url($category_info['style']) : '',
    			'is_show'		=> $category_info['is_show'],
    			'goods_count'	=> RC_Model::model('goods/goods_model')->where(array('cat_id' => $cat_id))->count(),
    	);
    	return $category_detail;
    	
    	
    }
    	 
    
}