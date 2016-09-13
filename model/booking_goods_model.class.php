<?php
defined('IN_ECJIA') or exit('No permission resources.');

class booking_goods_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'booking_goods';
		parent::__construct();
	}
	
	public function booking_goods_delete($id) {
		return $this->where(array('rec_id' => $id))->delete();
	}
	
	public function booking_goods_update($where, $data) {
		return $this->where($where)->update($data);
	}
}

// end