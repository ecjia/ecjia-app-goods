<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 店铺商品分类
 * @author zrl
 *
 */
class category_module implements ecjia_interface
{
 	
    public function run(ecjia_api & $api)
    {  	
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    
    	//RC_Loader::load_app_func('category','goods');
    	//$data=cat_list(0,0,false);
    	$seller_id = empty($_SESSION['seller_id']) ? 0 : $_SESSION['seller_id'];
    	$data = RC_Api::api('goods', 'seller_goods_category', array('type' => 'seller_goods_cat_list_api', 'seller_id' => $seller_id));
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