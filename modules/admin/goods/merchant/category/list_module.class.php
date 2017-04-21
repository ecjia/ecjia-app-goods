<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 入驻商店铺商品分类
 * @author chenzhejun@ecmoban.com
 *
 */
class list_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    
    	
    	RC_Loader::load_app_class('merchants_category', 'goods', false);
    	$data = merchants_category::cat_list(0, 0, false);
    		
    	$outdata = array();
    	if (!empty($data)) {
    		foreach ($data as $key => $value) {
      	    	$outdata[] = array(
      	    		'cat_id' 	=> $value['cat_id'],
      	    		'cat_name'	=> $value['cat_name'],
      	    		'parent_id'	=> $value['parent_id'],
      	    		'level'		=> empty($value['level']) ? 0 : $value['level']		
      	    	);
			}
    	}
      	  		
    	return $outdata;
    	
    }
    	 
    
}