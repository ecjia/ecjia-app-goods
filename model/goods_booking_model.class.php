<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_booking_model extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config 		= RC_Config::load_config('database');
		$this->db_setting 		= 'default';
		$this->table_name 		= 'booking_goods';
		$this->table_alias_name = 'bg';		
		
		
		parent::__construct();
	}
}

// end