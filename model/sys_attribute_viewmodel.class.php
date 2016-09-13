<?php
defined('IN_ECJIA') or exit('No permission resources.');

class sys_attribute_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view =array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'attribute';
		$this->table_alias_name = 'a';
		
		$this->view = array(
			'goods_attr' => array(
				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'v',
// 				'field' => 'a.attr_type, v.attr_value, v.goods_attr_id',
				'on'   	=> 'v.attr_id = a.attr_id AND a.attr_type = 1'
			)
		);
		parent::__construct();
	}
}

// end