<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 团购商品列表
 * @author royalwang
 *
 */
class groupbuygoods_module extends api_front implements api_interface {

    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
    	
    	$groupwhere['act_type']		= GAT_GROUP_BUY;
    	$groupwhere['start_time']	= array('elt' => RC_Time::gmtime());
    	$groupwhere['end_time']		= array('egt' => RC_Time::gmtime());
    	$groupwhere['is_finished'] 	= 0;
    	$groupwhere[] = 'g.goods_id is not null';
    	$groupwhere['g.is_on_sale'] = 1;
    	$groupwhere['g.is_alone_sale'] = 1;
    	if (ecjia::config('review_goods')) {
    		$groupwhere['g.review_status'] = array('gt' => 2);
    	}
    	$location = _POST('location');
    	
    	if (is_array($location) && isset($location['latitude']) && isset($location['longitude'])) {
    		$geohash = RC_Loader::load_app_class('geohash', 'shipping');
    		$geohash_code = $geohash->encode($location['latitude'] , $location['longitude']);
    		$geohash_code = substr($geohash_code, 0, 5);
    		
    		$groupwhere['geohash'] = array('like' => "%".$geohash_code."%");
    		
//     		$msi_dbview = RC_Loader::load_app_model('merchants_shop_information_viewmodel', 'seller');
//     		$ru_id_info = $msi_dbview->join(array('merchants_shop_information', 'seller_shopinfo'))->field(array('msi.user_id', 'msi.shopNameSuffix', 'msi.shoprz_brandName'))->where(array(
//     				'geohash'		=> array('like' => "%$where_geohash%"),
//     				'ssi.status'	=> 1,
//     				'msi.merchants_audit' => 1,
//     		))->select();
    			
//     		if (!empty($ru_id_info)) {

//     			$ru_id = array();
//     			foreach ($ru_id_info as $val) {
//     				$ru_id[] = $val['user_id'];
//     				$seller_info[$val['user_id']]['seller_id'] = $val['user_id'];
//     				$seller_info[$val['user_id']]['seller_name'] = (!empty($val['shoprz_brandName']) && !empty($val['shopNameSuffix'])) ? $val['shoprz_brandName'].$val['shopNameSuffix'] : '';
    					
//     			}
//     			$merchants_shop_information_db = RC_Loader::load_app_model('merchants_shop_information_model', 'seller');
//     			$merchants_shop_information_db->where(array('user_id' => $ru_id))->select();
//     			$groupwhere['g.user_id'] = $ru_id;
//     		} else {
//     			$groupwhere['g.user_id'] = 0;
//     		}
    	}
    	
    	$db_goods_activity = RC_Loader::load_app_model('goods_activity_viewmodel', 'goods');
    	
    	
    	$count = $db_goods_activity->join(array('goods', 'seller_shopinfo'))->where($groupwhere)->count();
    	
		/* 查询总数为0时直接返回  */
		if ($count == 0 || !is_array($location) || empty($location['latitude']) || empty($location['longitude'])) {
			$pager = array(
					'total' => 0,
					'count' => 0,
					'more'	=> 0,
			);
			EM_Api::outPut(array(), $pager);
		}
		
		/* 获取数量 */
    	$size = EM_Api::$pagination['count'];
    	$page = EM_Api::$pagination['page'];
    	
    	//加载分页类
    	RC_Loader::load_sys_class('ecjia_page', false);
    	//实例化分页
    	$page_row = new ecjia_page($count, $size, 6, '', $page);
    	
    	$res = $db_goods_activity->field('ssi.id as seller_id, shop_name as seller_name, ga.act_id, ga.goods_id, ga.goods_name, ga.start_time, ga.end_time, ext_info, shop_price, market_price, goods_brief, goods_thumb, goods_img, original_img')
    							 ->join(array('goods', 'seller_shopinfo'))
    							 ->where($groupwhere)
    							 ->order(array('act_id' => 'DESC'))
    							 ->limit($page_row->limit())
    							 ->select();
    	
    	$list = array();
    	if (!empty($res)) {
    		foreach ($res as $val) {
    			$ext_info = unserialize($val['ext_info']);
    			$price_ladder = $ext_info['price_ladder'];
    			if (!is_array($price_ladder) || empty($price_ladder)) {
    				$price_ladder = array(array('amount' => 0, 'price' => 0));
    			} else {
    				foreach ($price_ladder AS $key => $amount_price) {
    					$price_ladder[$key]['formated_price'] = price_format($amount_price['price']);
    				}
    			}
    				
    			$cur_price  = $price_ladder[0]['price'];
    			$list[] = array(
    					'id'	=> $val['goods_id'],
    					'name'	=> $val['goods_name'],
    					'market_price'	=> price_format($val['market_price'], false),
    					'shop_price'	=> price_format($val['shop_price'], false),
    					'promote_price'	=> price_format($cur_price, false),
    					'promote_start_date'	=> RC_Time::local_date('Y/m/d H:i:s', $val['start_time']),
    					'promote_end_date'		=> RC_Time::local_date('Y/m/d H:i:s', $val['end_time']),
    					'brief' => $val['goods_brief'],
    					'img'	=> array(
    							'small'	=> RC_Upload::upload_url(). DS .$val['goods_thumb'],
    							'thumb'	=> RC_Upload::upload_url(). DS .$val['goods_img'],
    							'url'	=> RC_Upload::upload_url(). DS .$val['original_img']
    					),
    					'object_id'	=> $val['act_id'],
    					'rec_type'	=> 'GROUPBUY_GOODS',
    					'seller_id'		=> $val['seller_id'],
    					'seller_name'	=> isset($val['seller_name']) ? $val['seller_name'] : '',
    			);
    		}
    	}
    	
    	
    	$pager = array(
    			"total" => $page_row->total_records,
    			"count" => $page_row->total_records,
    			"more"	=> $page_row->total_pages <= $page ? 0 : 1,
    	);
    	
    	EM_Api::outPut($list, $pager);
    	
    }
}


// end