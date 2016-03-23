<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 商品推荐列表
 * @author royalwang
 *
 */
class suggestlist_module implements ecjia_interface {

    public function run(ecjia_api & $api) {
    	//如果用户登录获取其session
    	if (EM_Api::$session['sid']) {
    		EM_Api::authSession();
    	}
    	RC_Loader::load_app_func('common', 'goods');
    	$action_type = _POST('action_type', '');
    	$sort_type = _POST('sort_by', '');
    	$type = array('new', 'best', 'hot', 'promotion');//推荐类型
    	
    	if (!in_array($action_type, $type)) {
    		EM_Api::outPut(101);
    	}
    	
    	$size = EM_Api::$pagination['count'];
    	$page = EM_Api::$pagination['page'];
    	
    	$where = array();
    	
    	if (!empty($action_type)) {
    		switch ($action_type) {
    			case 'best':
    				$where = array('is_best' => 1);
    				break;
    			case 'new':
    				$where = array('is_new' => 1);
    				break;
    			case 'hot':
    				$where = array('is_hot' => 1);
    				break;
    			case 'promotion':
    				$time    = RC_Time::gmtime();
    				$where = array(
    					'promote_price'			=> array('gt' => 0),
    					'promote_start_date'	=> array('elt' => $time),
    					'promote_end_date'		=> array('egt' => $time)
    				);
    				break;
    			default:
    				$intro   = '';
    		}
    	}
    	
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
    			$order_by = array('last_update' => 'desc', 'sort_order' => 'asc');
    			break;
    		default :
    			$order_by = array('sort_order' => 'asc', 'goods_id' => 'desc');
    			break;
    	}
    	
    	$where = array_merge($where, array('is_delete' => 0, 'is_on_sale' => 1, 'is_alone_sale' => 1));
    	 
    	if(ecjia::config('review_goods') == 1){
    		$where['review_status'] = array('gt' => 2);
    	}
    	
    	$db = RC_Loader::load_app_model('goods_model', 'goods');
    	/* 获得符合条件的商品总数 */
    	$count = $db->where($where)->count();
    	
    	//加载分页类
    	RC_Loader::load_sys_class('ecjia_page', false);
    	//实例化分页
    	$page_row = new ecjia_page($count, $size, 6, '', $page);
    	
    	/* 查询商品 */
    	$dbveiw = RC_Loader::load_app_model('goods_member_viewmodel', 'goods');
    	
    	$field = "g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price,".
    			"IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
    			"g.promote_price, g.promote_start_date, g.promote_end_date, g.goods_thumb, g.goods_img, g.goods_brief, g.goods_type ";
    	$dbview->view = array(
    			'member_price' => array(
    					'type'  => Component_Model_View::TYPE_LEFT_JOIN,
    					'alias' => 'mp',
    					'field' => $field,
    					'on'   	=> "mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'"
    			)
    	);
    	
    	$data = $dbveiw->field($field)->where($where)->order($order_by)->limit($page_row->limit())->select();
    	
    	$pager = array(
    			"total" => $page_row->total_records,
    			"count" => $page_row->total_records,
    			"more" => $page_row->total_pages <= $page ? 0 : 1,
    	);
    	
    	if (!empty($data) && is_array($data)) {
    		$list = array();
    		RC_Loader::load_sys_func('global');
    		RC_Loader::load_app_func('goods', 'goods');
    		$mobilebuy_db = RC_Loader::load_app_model('goods_activity_model', 'goods');
    		foreach ($data as $key => $val) {
    			if ($val['promote_price'] > 0) {
    				$promote_price = bargain_price($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']);
    			} else {
    				$promote_price = '0';
    			}
    			
    			$groupbuy = $mobilebuy_db->find(array(
    					'goods_id'	 => $val['goods_id'],
    					'start_time' => array('elt' => RC_Time::gmtime()),
    					'end_time'	 => array('egt' => RC_Time::gmtime()),
    					'act_type'	 => GAT_GROUP_BUY,
    			));
    			$mobilebuy = $mobilebuy_db->find(array(
    					'goods_id'	 => $val['goods_id'],
    					'start_time' => array('elt' => RC_Time::gmtime()),
    					'end_time'	 => array('egt' => RC_Time::gmtime()),
    					'act_type'	 => GAT_MOBILE_BUY,
    			));
    			/* 判断是否有促销价格*/
    			$price = ($val['shop_price'] > $promote_price && $promote_price > 0) ? $promote_price : $val['shop_price'];
    			$activity_type = ($val['shop_price'] > $promote_price && $promote_price > 0) ? 'PROMOTE_GOODS' : 'GENERAL_GOODS';
    			
    			
    			$mobilebuy_price = $groupbuy_price = $object_id = 0;
    			if (!empty($mobilebuy)) {
    				$ext_info = unserialize($mobilebuy['ext_info']);
    				$mobilebuy_price = $ext_info['price'];
   					$price = $mobilebuy_price > $price ? $price : $mobilebuy_price;
   					$activity_type = $mobilebuy_price > $price ? $activity_type : 'MOBILEBUY_GOODS';
   				}
//     				if (!empty($groupbuy)) {
//     					$ext_info = unserialize($groupbuy['ext_info']);
//     					$price_ladder = $ext_info['price_ladder'];
//     					$groupbuy_price  = $price_ladder[0]['price'];
//     					$price = $groupbuy_price > $price ? $price : $groupbuy_price;
//     					$activity_type = $groupbuy_price > $price ? $activity_type : 'GROUPBUY_GOODS';
//     				}
    			
				/* 计算节约价格*/
    			$saving_price = ($val['shop_price'] - $price) > 0 ? $val['shop_price'] - $price : 0;
    			$list[] = array(
    				'goods_id'		=> $val['goods_id'],//后期去除
    				'id'			=> $val['goods_id'],
    				'name'			=> $val['goods_name'],
    				'goods_name'	=> $val['goods_name'],//后期去除
    				'market_price'	=> price_format($val['market_price']),
    				'format_market_price'	=> price_format($val['market_price']),//后期去除
    				'shop_price'			=> price_format($val['shop_price']),
    				'format_shop_price'		=> price_format($val['shop_price']),//后期去除
    				'promote_price'			=> ($price < $val['shop_price'] && $price > 0) ? price_format($price) : '',
    				'brief'					=> $val['goods_brief'],
    				'img' => array(
    						'small' => get_image_path($val['goods_id'], $val['goods_thumb'], true),
    						'url' => get_image_path($val['goods_id'], $val['original_img'], true),
    						'thumb' => get_image_path($val['goods_id'], $val['goods_img'], true),
    				),
					'activity_type' => $activity_type,
    				'object_id'		=> $object_id,
					'saving_price'	=> $saving_price,
					'formatted_saving_price' => '已省'.$saving_price.'元'
    			);
    		}
    		
    		EM_Api::outPut($list, $pager);
    	} else {
    		EM_Api::outPut('', $pager);
    	}

    }
}


// end