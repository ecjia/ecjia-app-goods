<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 单个商品的信息
 * @author luchongchong
 *
 */
class category_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    
    	RC_Loader::load_app_func('category','goods');
    	
    	$data=cat_list(0,0,false);
      	 foreach ($data as $key=>$value)
      	 {
      	    $outdata[]=array(
      	    	'cat_id' 	=>$value['cat_id'],
      	    	'cat_name'	=>$value['cat_name'],
      	    	'parent_id'	=>$value['parent_id'],
      	    	'level'		=>$value['level']		
      	    );
      	 }  		
    	return $outdata;
    	
    }
    	 
    
}