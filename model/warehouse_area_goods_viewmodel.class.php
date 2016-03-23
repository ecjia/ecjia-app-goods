<?php
defined('IN_ECJIA') or exit('No permission resources.');

class warehouse_area_goods_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'warehouse_area_goods';
		$this->table_alias_name = 'wa';
		
		$this->view =array(
				'region_warehouse' => array(
						'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
						'alias' => 'rw',
						'on' 	=> 'wa.region_id = rw.regionId'
				)
		);
		
		parent::__construct();
	}
}

// end