<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 单个商品的信息
 * @author luchongchong
 *
 */
class updateprice_module implements ecjia_interface
{
	private $db_goods;
	
    public function run(ecjia_api & $api)
    { 
    	$ecjia = RC_Loader::load_app_class('api_admin', 'api');
    	$ecjia->authadminSession();
    	$result = $ecjia->admin_priv('goods_manage');
    	if (is_ecjia_error($result)) {
    		EM_Api::outPut($result);
    	}
    	
		//请求参数：
       	$goods_id				= _POST('goods_id', 0);
    	if (empty($goods_id)) {
    		EM_Api::outPut(11);
    	}
    	//市场价格
    	$shop_price				= _POST('shop_price');
    	$shop_price     		= !empty($shop_price)? $shop_price : '0';
    	$market_price			= _POST('market_price');
    	$market_price  	   		= !empty($market_price)? $market_price : '0';

    	//积分
    	$give_integral			= _POST('give_integral');
    	$give_integral      	= !empty($give_integral)?  $give_integral : '0';
    	$rank_integral			= _POST('rank_integral');
    	$rank_integral      	= !empty($rank_integral)?  $rank_integral : '0';
    	$integral				= _POST('integral');
    	$integral      			= !empty($integral)?  $integral : '0';
    	
    	//促销信息
    	$promote_price			= _POST('promote_price');
    	$is_promote 			= empty($promote_price) ? 0 : 1;
    	$promote_price      	= !empty($promote_price) ?  $promote_price : 0;
    	$promote_start_date		= _POST('promote_start_date');
    	
    	$promote_end_date  		= _POST('promote_end_date');
    	if (($promote_start_date == $promote_end_date) && !empty($promote_start_date) && !empty($promote_end_date)) {
    		$promote_start_date .= ' 00:00:00';
    		$promote_end_date 	.= ' 23:59:59';
    	}
    	$promote_start_date     = ($is_promote && !empty($promote_start_date)) ? RC_Time::local_strtotime($promote_start_date) : '';
    	$promote_end_date      	= ($is_promote && !empty($promote_end_date)) ? RC_Time::local_strtotime($promote_end_date) : '';
    	
	 	//优惠价格、等级价格
    	$volume_number_list 	= _POST('volume_number');
    	$user_rank_list			= $_POST['user_rank'];
    	
    	$this->db_goods = RC_Loader::load_app_model('goods_model','goods');
    	
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
    	$count = $this->db_goods->where(array('goods_id' => $goods_id))->update($data);
    	if ($count>0) {
    		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id))->get_field('goods_name');
    		ecjia_admin::admin_log($goods_name, 'edit', 'goods');
    		return array();
    	}
    	
    }
}