<?php
defined('IN_ECJIA') or exit('No permission resources.');

class group_goods_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'group_goods';
		parent::__construct();
	}
	
	public function group_goods_delete($where) {
		return $this->where($where)->delete();
	}
	
	public function group_goods_select($where) {
		return $this->where($where)->select();
	}
}

// end