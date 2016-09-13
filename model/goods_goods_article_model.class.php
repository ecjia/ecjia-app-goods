<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_goods_article_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods_article';
		parent::__construct();
	}
	
	public function goods_article_delete($where) {
		return $this->where($where)->delete();
	}
	
	public function goods_article_select($where) {
		return $this->where($where)->select();
	}
}

// end