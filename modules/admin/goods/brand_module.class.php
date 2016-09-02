<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 品牌列表
 * @author royalwang
 *
 */
class brand_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	
		$size = EM_Api::$pagination['count'];
		$page = EM_Api::$pagination['page'];
		$options = array(
			'keywords'	=> _POST('keywords'),
			'size'		=> $size,
			'page'		=> $page
		);
		$result = RC_Api::api('goods', 'goods_brand_list', $options);
		$brand = array();
		if (!empty($result['brand'])) {
			foreach ($result['brand'] as $val) {
				$brand[] = array(
					'brand_id'		=> $val['brand_id'],
					'brand_name'	=> $val['brand_name'],
					'brand_logo'	=> !empty($val['brand_logo']) ? RC_Upload::upload_url($val['brand_logo']) : '',
					'is_show'		=> $val['is_show'],
				);
			}
		}
		
        $pager = array(
				"total" => $result['page']->total_records,
				"count" => $result['page']->total_records,
				"more" 	=> $result['page']->total_pages <= $page ? 0 : 1,
		);
		
		EM_Api::outPut($brand, $pager);
		
	}
}


// end