<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_type_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods_type';
		$this->table_alias_name = 'gt';
		
		$this->view = array(
				'attribute' => array(
						'type'  => Component_Model_View::TYPE_LEFT_JOIN,
						'alias' => 'a',
						'field' => 'gt.*,count(a.cat_id)|attr_count',
						'on'    => 'a.cat_id = gt.cat_id'
				)
		);
		parent::__construct();
	}
}

// end