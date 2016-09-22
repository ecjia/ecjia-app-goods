<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_gallery_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->table_name = 'goods_gallery';
		parent::__construct();
	}
	
	public function goods_gallery_select($where) {
		return $this->where($where)->select();
	}
	
	public function goods_gallery_find($field = '*', $where = array()) {
		return $this->field($field)->where($where)->find();
	}
	
	public function goods_gallery_delete($id) {
		return $this->where(array('img_id' => $id))->delete();
	}
	
	public function goods_gallery_update($where, $data) {
		return $this->where($where)->update($data);
	}
}

// end