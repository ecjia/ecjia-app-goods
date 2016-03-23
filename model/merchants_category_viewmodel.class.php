<?php 
defined('IN_ECJIA') or exit('No permission resources.');

class merchants_category_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'merchants_category';
		$this->table_alias_name = 'mc';

		$this->view = array(
			'category' => array(
					'type'  => Component_Model_View::TYPE_LEFT_JOIN,
					'alias' => 'c',
					'on'    => 'mc.cat_id = c.cat_id ',
			),
			'merchants_shop_information' => array(
					'type'  => Component_Model_View::TYPE_LEFT_JOIN,
					'alias' => 'ms',
					'on'    => 'ms.user_id = mc.user_id ',
			),
		);
		parent::__construct();
	}
}

// end