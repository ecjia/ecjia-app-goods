<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 删除商品相册图片
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
    	
    	$goods_id		= _POST('goods_id');
    	$img_id			= _POST('img_id');
    	if (empty($goods_id) || empty($img_id)) {
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
		
		/* 删除图片文件 */
		$row = RC_Model::model('goods/goods_gallery_model')->field('img_url, thumb_url, img_original')->find(array('img_id' => $img_id));
		strrpos($row['img_original'], '?') && $row['img_original'] = substr($row['img_original'], 0, strrpos($row['img_original'], '?'));
		
		$disk = RC_Filesystem::disk();
		if ($row['img_url'] != '' && is_file(RC_Upload::upload_path() . '/' . $row['img_url'])) {
			$disk->delete(RC_Upload::upload_path() . $row['img_url']);
		}
		if ($row['thumb_url'] != '' && is_file(RC_Upload::upload_path() . '/' . $row['thumb_url'])) {
			$disk->delete(RC_Upload::upload_path() . $row['thumb_url']);
		}
		if ($row['img_original'] != '' && is_file(RC_Upload::upload_path() . '/' . $row['img_original'])) {
			$disk->delete(RC_Upload::upload_path() . $row['img_original']);
		}
		
		/* 删除数据 */
		RC_Model::model('goods/goods_gallery_model')->where(array('img_id' => $img_id))->delete();
    	return array();
    	
    }
    	 
    
}