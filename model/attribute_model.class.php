<?php
defined('IN_ECJIA') or exit('No permission resources.');

class attribute_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'attribute';
		parent::__construct();
	}
	
	public function attribute_count($where) {
		return $this->where($where)->count();
	}
	
	/**
	 * 获取属性信息
	 * @param 属性id $id
	 */
	public function attribute_info($id) {
		return $this->where(array('attr_id' => $id))->find();
	}
	
	public function attribute_manage($parameter) {
		if (!isset($parameter['attr_id'])) {
			$id = $this->insert($parameter);
		} else {
			$where = array('attr_id' => $parameter['attr_id']);
		
			$this->where($where)->update($parameter);
			$id = $parameter['attr_id'];
		}
		return $id;
	}
	
	public function attribute_find($field = '*', $where) {
		return $this->field($field)->where($where)->find();
	}
	
	public function attribute_field($where, $field) {
		return $this->where($where)->get_field($field);
	}
	
	public function attribute_select($where, $field = '*') {
		return $this->field($field)->where($where)->select();
	}
}

// end