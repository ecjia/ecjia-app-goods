<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods';
		$this->table_alias_name = 'g';
		$this->view = array(
			'goods_attr' => array (
				'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'a',
				'on' 	=> 'g.goods_id = a.goods_id'
			),
			'category' => array(
				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'c',
				'on'    => 'g.cat_id = c.cat_id'
			),
			'brand' => array(
				'type'	=> Component_Model_View::TYPE_LEFT_JOIN,
				'alias'	=> 'b',
				'on'	=> 'g.brand_id = b.brand_id'
			),
			'attribute' => array (
				'type' => Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'a',
				'on' 	=> 'g.goods_type = a.cat_id'
			),
		);
		parent::__construct();
	}
}

// end