<?php
defined('IN_ECJIA') or exit('No permission resources.');

class seller_shopinfo_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'seller_shopinfo';
		parent::__construct();
	}
	
	public function get_seller_name_by_id($seller_id = 0) {
	    return RC_DB::table('seller_shopinfo')->where('id', '=', $seller_id)->pluck ('shop_name');
	}
}

// end