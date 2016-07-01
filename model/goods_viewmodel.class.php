<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods';
		$this->table_alias_name = 'g';
		$this->view = array(
				'auto_manage' => array(
						'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
						'alias'	=> 'a',
// 						'field'	=> 'g.*,a.starttime,a.endtime',
						'on'	=> "g.goods_id = a.item_id AND a.type='goods'"
				),
				'category' => array(
						'type'     => Component_Model_View::TYPE_LEFT_JOIN,
						'alias'    => 'c',
// 						'field'    => "g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, m.type_money AS bonus_money,IFNULL(AVG(r.comment_rank), 0) AS comment_rank,IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS rank_price",
						'on'       => 'g.cat_id = c.cat_id'
				),
				'brand' => array(
						'type'     => Component_Model_View::TYPE_LEFT_JOIN,
						'alias'    => 'b',
						'on'       => 'g.brand_id = b.brand_id '
				),
				'comment' => array(
						'type' => Component_Model_View::TYPE_LEFT_JOIN,
						'alias' => 'r',
						'on' => 'r.id_value = g.goods_id AND comment_type = 0 AND r.parent_id = 0 AND r.status = 1'
				),
				'bonus_type' => array(
						'type' => Component_Model_View::TYPE_LEFT_JOIN,
						'alias' => 'm',
						'on' => 'g.bonus_type_id = m.type_id AND m.send_start_date <= "' . RC_Time::gmtime () . '" AND m.send_end_date >= "' . RC_Time::gmtime () . '"'
				),
				'goods_attr' => array (
					'type' => Component_Model_View::TYPE_LEFT_JOIN,
					'alias' => 'a',
// 					'field' => "g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price,IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price,g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date",
					'on' => 'g.goods_id = a.goods_id' 
					),
				'member_price'   => array(
						'type'     => Component_Model_View::TYPE_LEFT_JOIN,
						'alias'    => 'mp',
						'on'       => 'mp.goods_id = g.goods_id AND mp.user_rank = "' . $_SESSION ['user_rank'] . '"'
				),
				'link_goods'   => array(
						'type'     => Component_Model_View::TYPE_RIGHT_JOIN,
						'alias'    => 'lg',
						'on'       => 'g.goods_id = lg.link_goods_id'
				),
		 		'package_goods' => array(
		 				'type'  => Component_Model_View::TYPE_RIGHT_JOIN,
		 				'alias' => 'pg',
// 		 				'field' => 'pg.goods_id, g.goods_name, (CASE WHEN pg.product_id > 0 THEN p.product_number ELSE g.goods_number END) AS goods_number, p.goods_attr, p.product_id, pg.goods_number AS order_goods_number, g.goods_sn, g.is_real, p.product_sn',
		 				'on'    => 'pg.goods_id = g.goods_id ',
		 		),
	    		'products' => array(
	    				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
	    				'alias' => 'p',
	    				'on'    => 'pg.product_id = p.product_id',
	    		),
				'collect_goods' => array(
						'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,  
						'alias'	=> 'cg',
// 				        'field' => "g.goods_id, g.goods_name, g.market_price, g.goods_thumb, IF(g.is_promote = 1 AND ".RC_Time::gmtime()." >= g.promote_start_date AND ".RC_Time::gmtime()." <= g.promote_end_date, g.promote_price, g.shop_price) AS goods_price",
						'on' 	=> 'g.goods_id = cg.goods_id', 
				),
				'cart' => array(
						'type'     => Component_Model_View::TYPE_LEFT_JOIN,
						'alias'    => 'c',
						'on'       => 'g.goods_id =c.goods_id'
				),
				'seller_shopinfo' => array(
						'type'     => Component_Model_View::TYPE_LEFT_JOIN,
						'alias'    => 'ssi',
						'on'       => 'ssi.id=g.seller_id'
				),
		);
		parent::__construct();
	}
	
	
	/**
	 * 取得促销商品列表
	 * @param array $filter
	 * @return  array
	 */
	public function promotion_list($filter) {
		/* 过滤条件 */
		$filter['keywords'] = empty($filter['keywords']) ? '' : trim($filter['keywords']);
		$where = array();
		$where = array('is_promote' => 1);
		$where['is_delete'] = array('neq' => 1);
		/* 多商户处理*/
		if (isset($_SESSION['ru_id']) && $_SESSION['ru_id'] > 0 ) {
			$where['user_id'] = $_SESSION['ru_id'];
		}
	
		if (!empty($filter['keywords'])) {
			$where['goods_name'] = array('like' => '%'.$filter['keywords'].'%');
		}
	
		$time = RC_Time::gmtime();
		if ($filter['status'] == 'going') {
			$where['promote_start_date'] = array('elt' => $time);
			$where['promote_end_date'] = array('egt' => $time);
		}
	
		if ($filter['status'] == 'coming') {
			$where['promote_start_date'] = array('egt' => $time);
		}
	
		if ($filter['status'] == 'finished') {
			$where['promote_end_date'] = array('elt' => $time);
		}
	
		$join = null;
		/* 判断是否是b2b2c*/
		$result_app = ecjia_app::validate_application('seller');
		$is_active = ecjia_app::is_active('ecjia.seller');
		if (!is_ecjia_error($result_app) && $is_active) {
			$join = array('seller_shopinfo');
		}
		
		$filter['record_count'] = $this->join(null)->where($where)->count();
		$field = 'seller_id, shop_name as seller_name, goods_id, goods_name, shop_price, market_price, promote_price, promote_start_date, promote_end_date, goods_thumb, original_img, goods_img';
		//实例化分页
		$page_row = new ecjia_page($filter['record_count'], $filter['size'], 6, '', $filter['page']);
	
		$res = $this->join($join)->field($field)->where($where)->order('sort_order asc')->limit($page_row->limit())->select();
	
		$list = array();
		if (!empty($res)) {
			foreach ($res as $row) {
				$row['promote_start_date']  = RC_Time::local_date('Y-m-d H:i:s', $row['promote_start_date']);
				$row['promote_end_date']    = RC_Time::local_date('Y-m-d H:i:s', $row['promote_end_date']);
				$row['goods_thumb']			= !empty($row['goods_thumb']) ? RC_Upload::upload_url($row['goods_thumb']) : RC_Uri::admin_url('statics/images/nopic.png');
				$row['original_img']		= !empty($row['original_img']) ? RC_Upload::upload_url($row['original_img']) : RC_Uri::admin_url('statics/images/nopic.png');
				$row['goods_img']			= !empty($row['goods_img']) ? RC_Upload::upload_url($row['goods_img']) : RC_Uri::admin_url('statics/images/nopic.png');
				$list[] = $row;
			}
		}
		return array('item' => $list, 'filter' => $filter, 'page' => $page_row);
	}
	
	/**
	 * 促销的商品信息
	 * @param int $goods_id
	 * @return array
	 */
	function promote_goods_info($goods_id) {
		$where = array();
		$where['goods_id'] = $goods_id;
		/*多商户处理*/
		if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0 ) {
			$where['seller_id'] = $_SESSION['seller_id'];
		}
		$row = $this->where($where)->find();
	
		if (! empty ( $row )) {
			$row['formatted_shop_price']		= price_format($row['shop_price']);
			$row['formatted_market_price']		= price_format($row['market_price']);
			$row['formatted_promote_price']		= price_format($row['promote_price']);
			$row['promote_start_date']			=  $row['promote_start_date'];
			$row['promote_end_date']  			=  $row['promote_end_date'];
			$row['formatted_promote_start_date']  		= RC_Time::local_date('Y-m-d H:i:s', $row['promote_start_date']);
			$row['formatted_promote_end_date']    		= RC_Time::local_date('Y-m-d H:i:s', $row['promote_end_date']);
			$row['img']							= array(
					'goods_thumb'  => !empty($row['goods_thumb']) ? RC_Upload::upload_url($row['goods_thumb']) : RC_Uri::admin_url('statics/images/nopic.png'),
					'original_img' => !empty($row['original_img']) ? RC_Upload::upload_url($row['original_img']) : RC_Uri::admin_url('statics/images/nopic.png'),
					'goods_img'    => !empty($row['goods_img']) ? RC_Upload::upload_url($row['goods_img']) : RC_Uri::admin_url('statics/images/nopic.png')
			);
		}
		unset($row['goods_thumb']);
		unset($row['original_img']);
		unset($row['goods_img']);
		return $row;
	}
	
	/**
	 * 取消商品的促销活动
	 * @param int $act_id
	 * @return boolean
	 */
	public function promotion_remove($goods_id) {
		$this->where(array('goods_id' => $goods_id))->update(array('is_promote' => 0, 'promote_price' => 0, 'promote_start_date' => 0, 'promote_end_date' => 0));
		return true;
	}
	
	
	/**
	 * 促销商品管理
	 * @param array $parameter
	 * @return int goods_id
	 */
	public function promotion_manage($parameter)
	{
		if (isset($parameter['goods_id']) && $parameter['goods_id'] > 0) {
			$act_id = $this->where(array('goods_id' => $parameter['goods_id']))->update($parameter);
		}
		return $act_id;
	}

}

// end