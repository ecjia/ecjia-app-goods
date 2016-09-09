<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 单个商品的信息
 * @author luchongchong
 *
 */
class updateprice_module extends api_admin implements api_interface {
	public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    	$this->authadminSession();
    	
		//请求参数：
       	$goods_id				= $this->requestData('goods_id', 0);
    	if (empty($goods_id)) {
    		return new ecjia_error('has_exist_error', '用户名或email已使用');
    	}
    	//市场价格
    	$shop_price				= $this->requestData('shop_price', 0);
    	$market_price			= $this->requestData('market_price', 0);

    	//积分
    	$give_integral			= $this->requestData('give_integral', 0);
    	$rank_integral			= $this->requestData('rank_integral', 0);
    	$integral				= $this->requestData('integral', 0);
    	
    	//促销信息
    	$promote_price			= $this->requestData('promote_price');
    	$is_promote 			= empty($promote_price) ? 0 : 1;
    	$promote_price      	= !empty($promote_price) ?  $promote_price : 0;
    	$promote_start_date		= $this->requestData('promote_start_date');
    	
    	$promote_end_date  		= $this->requestData('promote_end_date');
    	if (($promote_start_date == $promote_end_date) && !empty($promote_start_date) && !empty($promote_end_date)) {
    		$promote_start_date .= ' 00:00:00';
    		$promote_end_date 	.= ' 23:59:59';
    	}
    	$promote_start_date     = ($is_promote && !empty($promote_start_date)) ? RC_Time::local_strtotime($promote_start_date) : '';
    	$promote_end_date      	= ($is_promote && !empty($promote_end_date)) ? RC_Time::local_strtotime($promote_end_date) : '';
    	
	 	//优惠价格、等级价格
    	$volume_number_list 	= $this->requestData('volume_number');
    	$user_rank_list			= $this->requestData['user_rank'];
    	
    	$db_goods = RC_Model::model('goods/goods_model');
    	
    	RC_Loader::load_app_func('system_goods', 'goods'); 
    	RC_Loader::load_app_func('functions', 'goods');

    	$volume_number = array();
    	$volume_price  = array();
    	foreach ($volume_number_list as $key => $value) {
    		$volume_number[] = $value['number'];
    		$volume_price[] = $value['price'];
    	}
    	
		$user_rank  = array();
		$userprice  = array();
		foreach ($user_rank_list as $key=>$value){
			$user_rank[] = $value['rank_id'];
			$userprice[] = $value['price'];
		}
    	/* 处理会员价格 */
		if (!empty($user_rank) && !empty($userprice)) {
			handle_member_price($goods_id, $user_rank, $userprice);
		}
		
		if (!empty($volume_number) && !empty($volume_price)) {
			handle_volume_price($goods_id, $volume_number, $volume_price);
		}
		
    	$data = array(
    			'shop_price'			=> $shop_price,
    			'market_price'			=> $market_price,
    			'promote_price'			=> $promote_price,
    			'promote_start_date' 	=> $promote_start_date,
    			'promote_end_date'		=> $promote_end_date,
    			'give_integral'			=> $give_integral,
    			'rank_integral'			=> $rank_integral,
    			'integral'				=> $integral,
    			'is_promote'			=> $is_promote,
    			'last_update'			=> RC_Time::gmtime()
    	);
    	$count = $db_goods->where(array('goods_id' => $goods_id))->update($data);
    	if ($count>0) {
    		$goods_name = $db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
    		ecjia_admin::admin_log($goods_name, 'edit', 'goods');
    		return array();
    	}
    }
}