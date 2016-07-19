<?php
defined('IN_ECJIA') or exit('No permission resources.');

class goods_booking_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config 		= RC_Config::load_config('database');
		$this->db_setting 		= 'default';
		$this->table_name 		= 'booking_goods';
		$this->table_alias_name = 'bg';		
		
		 $this->view = array(
    		'goods' => array(
    				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
    				'alias'	=> 'g',
    				'field' => 'bg.rec_id, bg.user_id, IFNULL(u.user_name, "'.RC_Lang::lang('guest_user').'") AS user_name,bg.link_man, g.goods_name, bg.goods_id, bg.goods_number,bg.booking_time, bg.goods_desc,bg.dispose_user, bg.dispose_time, bg.email,bg.tel, bg.dispose_note ,bg.dispose_user, bg.dispose_time,bg.is_dispose',     
    				'on'    => 'g.goods_id  = bg.goods_id',
    		),
    		'users' => array(
    				'type'  => Component_Model_View::TYPE_LEFT_JOIN,
    				'alias'	=> 'u',
    				'on'    => 'u.user_id = bg.user_id ',
    		)
    );		
		parent::__construct();
	}
}

// end