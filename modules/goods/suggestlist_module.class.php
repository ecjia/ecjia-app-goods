<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品推荐列表
 * @author royalwang
 *
 */
class suggestlist_module extends api_front implements api_interface {

    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    	$this->authSession();	
    	
    	$location = $this->requestdata('loaction', array());
    	/*经纬度为空判断*/
    	if (!is_array($location) || empty($location['longitude']) || empty($location['latitude'])) {
    		$data = array();
    		$data['list'] = array();
    		$data['pager'] = array(
    				"total" => '0',
    				"count" => '0',
    				"more"	=> '0'
    		);
    		return array('data' => $data['list'], 'pager' => $data['pager']);
    	}
    	RC_Loader::load_app_func('common', 'goods');
    	$action_type = $this->requestdata('action_type', '');
    	$sort_type = $this->requestdata('sort_by', '');
    	$type = array('new', 'best', 'hot', 'promotion');//推荐类型
    	
    	if (!in_array($action_type, $type)) {
    		return new ecjia_error('invalid_parameter', RC_Lang::get('system::system.invalid_parameter'));
    	}
    	
    	$size = $this->requestData('pagination.count', 15);
    	$page = $this->requestData('pagination.page', 1);
    	
    	$cache_id = sprintf('%X', crc32($action_type . '-' . $sort_type  .'-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' .
    			ecjia::config('lang')));
    	
    	$cache_key = 'api_goods_suggestlist_'.$cache_id;
//        	$data = RC_Cache::app_cache_get($cache_key, 'goods');
		$data = null;
       	if (empty($data)) {
	    	switch ($sort_type) {
	    		case 'goods_id' :
	    			$order_by = array('goods_id' => 'desc');
	    			break;
	    		case 'shop_price_desc' :
	    			$order_by = array('shop_price' => 'desc', 'sort_order' => 'asc');
	    			break;
	    		case 'shop_price_asc' :
	    			$order_by = array('shop_price' => 'asc', 'sort_order' => 'asc');
	    			break;
	    		case 'last_update' :
	    			$order_by = array('last_update' => 'desc');
	    			break;
	    		default :
	    			$order_by = array('sort_order' => 'asc', 'goods_id' => 'desc');
	    			break;
	    	}
	    	
	    	$options = array(
	    			'intro'		=> $action_type,
	    			'sort'		=> $order_by,
	    			'page'		=> $page,
	    			'size'		=> $size,
	    			'location'	=> $location,
	    	);
	    	$result = RC_Api::api('goods', 'goods_list', $options);
	    	
	    	$data['pager'] = array(
						"total" => $result['page']->total_records,
						"count" => $result['page']->total_records,
						"more" => $result['page']->total_pages <= $page ? 0 : 1,
			);
	    	$data['list'] = array();
	    	if (!empty($result['list'])) {
	    		$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
	    		/* 手机专享*/
	    		$result_mobilebuy = ecjia_app::validate_application('mobilebuy');
	    		$is_active = ecjia_app::is_active('ecjia.mobilebuy');
	    		foreach ($result['list'] as $val) {
	    			/* 判断是否有促销价格*/
	    			$price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_promote_price'] : $val['unformatted_shop_price'];
	    			$activity_type = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
	    			/* 计算节约价格*/
	    			$saving_price = ($val['unformatted_shop_price'] > $val['unformatted_promote_price'] && $val['unformatted_promote_price'] > 0) ? $val['unformatted_shop_price'] - $val['unformatted_promote_price'] : (($val['unformatted_market_price'] > 0 && $val['unformatted_market_price'] > $val['unformatted_shop_price']) ? $val['unformatted_market_price'] - $val['unformatted_shop_price'] : 0);
	    			 
	    			$mobilebuy_price = $object_id = 0;
	    			if (!is_ecjia_error($result_mobilebuy) && $is_active) {
	    				$mobilebuy = $mobilebuy_db->find(array(
	    						'goods_id'	 => $val['goods_id'],
	    						'start_time' => array('elt' => RC_Time::gmtime()),
	    						'end_time'	 => array('egt' => RC_Time::gmtime()),
	    						'act_type'	 => GAT_MOBILE_BUY,
	    				));
	    				if (!empty($mobilebuy)) {
	    					$ext_info = unserialize($mobilebuy['ext_info']);
	    					$mobilebuy_price = $ext_info['price'];
	    					if ($mobilebuy_price < $price) {
	    						$val['promote_price'] = price_format($mobilebuy_price);
	    						$object_id		= $mobilebuy['act_id'];
	    						$activity_type	= 'MOBILEBUY_GOODS';
	    						$saving_price = ($val['unformatted_shop_price'] - $mobilebuy_price) > 0 ? $val['unformatted_shop_price'] - $mobilebuy_price : 0;
	    					}
	    				}
	    			}
	    				
	    			$data['list'][] = array(
	    					'goods_id'		=> $val['goods_id'],
	    					'id'			=> $val['goods_id'],
	    					'name'			=> $val['name'],
	    					'market_price'	=> $val['market_price'],
	    					'shop_price'	=> $val['shop_price'],
	    					'promote_price'	=> $val['promote_price'],
	    					'img' => array(
	    							'thumb'	=> $val['goods_img'],
	    							'url'	=> $val['original_img'],
	    							'small'	=> $val['goods_thumb']
	    					),
	    					'activity_type' => $activity_type,
	    					'object_id'		=> $object_id,
	    					'saving_price'	=>	$saving_price,
	    					'formatted_saving_price' => $saving_price > 0 ? '已省'.$saving_price.'元' : '',
	    					'seller_id'		=> $val['seller_id'],
	    					'seller_name'	=> $val['seller_name'],
	    			);
	    		}
	    	}
	    	RC_Cache::app_cache_set($cache_key, $data, 'goods', 60);
       	}
    	return array('data' => $data['list'], 'pager' => $data['pager']);
    }
}


// end