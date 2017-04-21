<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 添加商品相册图片
 * @author chenzhejun@ecmoban.com
 * 添加和编辑商品相册图片，此接口只追加图片，删除图片用delete接口
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
    	
    	$goods_id		= _POST('goods_id');
    	if (empty($goods_id)) {
    		EM_Api::outPut(101);
    	}
    	
    	$where = array('goods_id' => $goods_id);
		if ($_SESSION['ru_id'] > 0) {
			$where = array_merge($where, array('user_id' => $_SESSION['ru_id']));
		}
		
		$goods_info = RC_Model::model('goods/goods_model')->where($where)->find();
		
		if (empty($goods_info)) {
			return new ecjia_error('goods_empty', '未找到对应商品');
		}
		if (empty($_FILES)) {
		    return new ecjia_error('upload_empty', '请选择您要上传的图片');
		}
		
		RC_Loader::load_app_class('goods_image', 'goods', false);
		
		$goods_gallery_number = ecjia::config('goods_gallery_number');
		if ($goods_gallery_number != 0) {
		    $db_goods_gallery = RC_Loader::load_app_model('goods_gallery_model', 'goods');
		    $count = $db_goods_gallery->where(array('goods_id' => $goods_id))->count();
		    
		    if (count($_FILES['image']['name']) > $goods_gallery_number - $count) {
		        return new ecjia_error('upload_counts_error', '商品相册图片不能超过'.$goods_gallery_number.'张');
		    }
		}
		
		$upload = RC_Upload::uploader('image', array('save_path' => 'images', 'auto_sub_dirs' => true));
		$count = count($_FILES['image']['name']);
		for ($i = 0; $i < $count; $i++) {
		    $picture = array(
		        'name' 		=> 	$_FILES['image']['name'][$i],
		        'type' 		=> 	$_FILES['image']['type'][$i],
		        'tmp_name' 	=> 	$_FILES['image']['tmp_name'][$i],
		        'error'		=> 	$_FILES['image']['error'][$i],
		        'size'		=> 	$_FILES['image']['size'][$i],
		    );
		    if (!empty($picture['name'])) {
		        if (!$upload->check_upload_file($picture)) {
		            return new ecjia_error('upload_error'. __LINE__, $upload->error());
		        }
		    }
		}
		
		$image_info = $upload->batch_upload($_FILES);
		if (empty($image_info)) {
			return new ecjia_error('upload_error'. __LINE__, $upload->error());
		}
		
		foreach ($image_info as $image) {
		    $goods_image = new goods_image($image);
		    $goods_image->update_gallery($goods_id);
		}
		
    	return array();
    	
    }
    	 
    
}