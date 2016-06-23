<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_auto_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods';
		$this->table_alias_name	= 'g';
		
		$this->view = array(
				'auto_manage' => array(
						'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
						'alias'	=> 'a',
						'field'	=> 'g.*,a.starttime,a.endtime',
						'on'	=> "g.goods_id = a.item_id AND a.type='goods'"
				),
// 				'merchants_shop_information' => array(
// 						'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
// 						'alias'	=> 'ms',
// 						'on'	=> "ms.user_id = g.user_id"
// 				),
				'seller_shopinfo' => array(
						'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
						'alias'	=> 'ssi',
						'on'	=> "ssi.id = g.seller_id"
				),
				
		);		
		parent::__construct();
	}
}

// end