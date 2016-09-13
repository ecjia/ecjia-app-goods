<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_type_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods_type';
		parent::__construct();
	}
	
	public function goods_type_manage($parameter) {
		if (!isset($parameter['cat_id'])) {
			$id = $this->insert($parameter);
		} else {
			$where = array('cat_id' => $parameter['cat_id']);
	
			$this->where($where)->update($parameter);
			$id = $parameter['cat_id'];
		}
		return $id;
	}
	
	public function goods_type_find($where, $field = '*') {
		return $this->field($field)->where($where)->find();
	}
	
	public function goods_type_count($where = array()) {
		return $this->where($where)->count();
	}
}

// end