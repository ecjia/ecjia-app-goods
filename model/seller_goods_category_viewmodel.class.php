<?php
defined('IN_ECJIA') or exit('No permission resources.');

class seller_goods_category_viewmodel extends Component_Model_View {
	public $table_name = '';
	public  $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'seller_goods_category';
		$this->table_alias_name = 'c';
		
		$this->view = array(
				'seller_goods_category' => array(
						'type'  =>	Component_Model_View::TYPE_LEFT_JOIN,
						'alias' =>	's',
						'field' =>	'c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children',
						'on'   	=>	's.parent_id = c.cat_id'
				)				
		);
		parent::__construct();
	}
}

// end