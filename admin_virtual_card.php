<?php
/**
 *  ECJIA 虚拟卡商品管理程序
 * 
 */
defined('IN_ECJIA') or exit('No permission resources.');
RC_Loader::load_sys_class('ecjia_admin', false);

class admin_virtual_card extends ecjia_admin 
{
	private $db_goods;
	private $db_virtual_card;
	private $db_view;
	private $auth_key;
	public function __construct() 
	{
		parent::__construct();

		RC_Lang::load('admin_virtual_card');
	
		RC_Loader::load_app_func('goods');
		RC_Loader::load_app_func('function');
		RC_Loader::load_app_func('global');

		$this->db_goods = RC_Loader::load_app_model('goods_model');
		$this->db_virtual_card = RC_Loader::load_app_model('virtual_card_model');
		$this->db_view = RC_Loader::load_app_model('virtual_card_viewmodel');
		
		/* 加密串功能加入配置文件 */
		if (!ecjia::config('auth_key' , ecjia::CONFIG_CHECK)) {
			ecjia_config::instance()->insert_config('hidden' , 'auth_key' , '' , array('type' => 'hidden'));
		}

		assign_adminlog_content();
		
		/* 读取商店配置的加密串密钥 */
		$this->auth_key = ecjia_config::instance()->read_config('auth_key');
		
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');
		
		RC_Script::enqueue_script('jquery-uniform');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Style::enqueue_style('chosen');
		
		RC_Style::enqueue_style('datepicker' , RC_Uri::admin_url('statics/lib/datepicker/datepicker.css') , array() , false , false);
		RC_Script::enqueue_script('bootstrap-datepicker.min' , RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js') , array() , false , false);
		RC_Script::enqueue_script('replenish_list' , RC_App::apps_url('statics/js/replenish_list.js' , __FILE__) , array() , false , false);
		RC_Script::enqueue_script('batch_card_add' , RC_App::apps_url('statics/js/batch_card_add.js' , __FILE__) , array());
	}
	
	/**
	 * 加密串列表
	 * 
	 */
	public function init() 
	{
		$this->admin_priv('virualcard');

		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__(RC_Lang::lang('virtual_card_change'))));
		RC_Script::enqueue_script('admin_virtual_card' , RC_App::apps_url('statics/js/admin_virtual_card.js' , __FILE__) , array() , false , false);

		$this->assign('ur_here', 		RC_Lang::lang('virtual_card_change'));
		$this->assign('form_action', 	RC_Uri::url('goods/admin_virtual_card/edit_virtual_card'));
		
		ecjia_screen::get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __('概述'),
		'content'	=>
		'<p>' . __('欢迎访问ECJia智能后台更改加密串页面，可以在此对加密串进行编辑。') . '</p>'
		) );
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('更多信息:') . '</strong></p>' .
		'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:更改加密串" target="_blank">关于更改加密串帮助文档</a>') . '</p>'
		);
		
		$this->assign_lang();
		$this->display('virtual_card_change.dwt');
	}

	/**
	 * 补货处理
	 */
	public function replenish() 
	{

		$this->admin_priv('virualcard');
		
		RC_Script::enqueue_script('batch_card_add' , RC_App::apps_url('statics/js/batch_card_add.js' , __FILE__) , array() , false , false);
		
		$goods_id = $_REQUEST['goods_id'];
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id , 'is_real' => 0 , 'extension_code' => 'virtual_card'))->get_field('goods_name');
		
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('虚拟商品列表') , RC_Uri::url('goods/admin/init' , 'extension_code=virtual_card')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($goods_name , RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id)));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('补货')));
		
		$card = array('goods_id' => $_REQUEST['goods_id'] , 'goods_name'=> $goods_name , 'end_date' => date('Y-m-d' , strtotime('+1 year')));
		
		$this->assign('card',			$card);
		$this->assign('goods_id',		$goods_id);
		$this->assign('ur_here',		__('批量补货'));
		$this->assign('action_link',	array('text'=>RC_Lang::lang('go_list') , 'href' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id)));		
		$this->assign('form_action',	RC_Uri::url('goods/admin_virtual_card/insert_replenish'));
		
		$this->assign_lang();
		$this->display('replenish_info.dwt');
	}
	
	/**
	 * 编辑补货信息
	 */
	public function edit_replenish() 
	{
		$this->admin_priv('virualcard');
		
		$goods_id = $_REQUEST['goods_id'];
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id , 'is_real' => 0 , 'extension_code' => 'virtual_card'))->get_field('goods_name');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('虚拟商品列表') , RC_Uri::url('goods/admin_virtual_card/init' , 'extension_code=virtual_card')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($goods_name) , RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id)));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑补货')));
		
		$card_id 	= $_GET['card_id'];
		$batch		= $_GET['batch'];
		
		/* 判断是批量编辑还是单个编辑  */
		if (!$batch) {
			/* 获取卡片信息 */
			$card = $this->db_view->join('goods')->find(array('vc.card_id' => $card_id));
			$card['end_date'] = RC_Time::local_date('Y-m-d' , $card['end_date']);//格式化日期

			if ($card['crc32'] == crc32($this->auth_key)) {
				$card['card_sn']		= RC_Crypt::decrypt($card['card_sn'] , $this->auth_key);
				$card['card_password']	= RC_Crypt::decrypt($card['card_password'] , $this->auth_key);
			} else {
				$card['card_sn']		= '***';
				$card['card_password']	= '***';
			}
		} else {
			/* 批量编辑 */
			$card = $this->db_view->join('goods')->in(array('vc.card_id' => $card_id))->select();
			foreach ($card as $k => $v) {
				$v['end_date'] = RC_Time::local_date('Y-m-d' , $v['end_date']);//格式化日期			
				if ($v['crc32'] == crc32($this->auth_key)) {
					$v['card_sn']		= RC_Crypt::decrypt($v['card_sn'] , $this->auth_key);
					$v['card_password'] = RC_Crypt::decrypt($v['card_password'] , $this->auth_key);
				} else {
					$v['card_sn']		= '***';
					$v['card_password'] = '***';
				}
				$card[$k] = $v;
			}
		}

		$this->assign('form_action',	RC_Uri::url('goods/admin_virtual_card/update_replenish'));
		$this->assign('ur_here',		__('编辑补货'));
		$this->assign('action_link',	array('text' => RC_Lang::lang('go_list'), 'href' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id)));
		$this->assign('card',			$card);
		$this->assign('goods_id',		$goods_id);
		$this->assign('goods_name',		$goods_name);
		
		$this->assign_lang();
		$this->display('replenish_info.dwt');
	}
	
	/**
	 * 添加补货
	 */
	public function insert_replenish() 
	{
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
	    /* 对添加补货进行权限检查  BY：MaLiuWei  START */
	    if (!empty($_SESSION['ru_id'])) {
	    	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    /* 对添加补货进行权限检查  BY：MaLiuWei  END */
		$goods_id 				= $_POST['goods_id'];
		$card_sn_Array 			= $_POST['card_sn'];
		$card_password_Array 	= $_POST['card_password'];
		$end_date_Array			= $_POST['end_date'];

		/* 遍历验证是否为空 */
		$newArray = array();
		$i = 0 ;
		foreach ($card_sn_Array as $k => $v) {
			/* 遍历插入 */
			/* 加密后的 */
			$coded_card_sn			= RC_Crypt::encrypt($v , $this->auth_key);
			$coded_card_password	= RC_Crypt::encrypt($card_password_Array[$k] , $this->auth_key);

			if (empty($card_sn_Array[$k]) || empty($card_password_Array[$k])) {
				$this->showmessage(__('卡片序号或者卡片密码不能为空！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			/* 检查同一个goods_id 下有没有重复的卡片序号 */
			if ($this->db_virtual_card->where(array('goods_id' => $goods_id , 'card_sn' => RC_Crypt::encrypt($v , $this->auth_key)))->count() == 0) {
				$i++;
				/* 插入一条或多条新记录 */
				$end_date = strtotime($end_date_Array[$k]);//转换成时间戳
				$add_date = RC_Time::gmtime(); //获取当前日期
				$data = array(
						'goods_id'		=> $goods_id,
						'card_sn'		=> $coded_card_sn,
						'card_password'	=> $coded_card_password,
						'end_date'		=> $end_date,
						'add_date'		=> $add_date,
						'crc32'			=> crc32($this->auth_key),
				);
				
				$this->db_virtual_card->insert($data);
				/* 记录日志 */
				ecjia_admin::admin_log($v, 'batch_add', 'virtual_card');
				/* 如果添加成功且原卡号为空时商品库存加1 */
				$data = array(
						'goods_number' => goods_number+1,
				);
				$this->db_goods->where(array('goods_id' => $goods_id))->update($data);
			} else {
				array_push($newArray, $v);
			}
		}
		/* 记录日志 */
//		ecjia_admin::admin_log($goods_id, 'add', 'virtual_card');

		$links[] = array('text' => RC_Lang::lang('go_list') , 'href' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$_POST['goods_id']));
		if ($newArray) {
			$this->showmessage(__('虚拟卡').join(',' , $newArray).__('已经存在!').__('本次插入'.$i.'条记录') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		} else {
			$this->showmessage(__('批量操作成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('links' => $links));
		}
	}
	
	/**
	 * 更新补货信息
	 */
	public function update_replenish() 
	{
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
	    /* 对更新补货信息进行权限检查  BY：MaLiuWei  START */
	    if (!empty($_SESSION['ru_id'])) {
	    	$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	    }
	    /* 对更新补货信息进行权限检查  BY：MaLiuWei  END */
		$goods_id		= $_POST['goods_id'];
		$card_sn		= $_POST['card_sn'];
		$card_id		= empty($_POST['card_id']) ? '' : $_POST['card_id'];
		$card_password	= $_POST['card_password'];
		$end_date		= $_POST['end_date'];
		$batch			= $_POST['batch'];
		
		/* 判断是批量编辑还是单条编辑  */
		$links[] = array('text' => RC_Lang::lang('go_list') , 'href' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id));
		if (!$batch) {
			/* 加密后的 */
			$coded_card_sn			= RC_Crypt::encrypt($card_sn[0] , $this->auth_key);
			$coded_old_card_sn		= RC_Crypt::encrypt($_POST['old_card_sn'] , $this->auth_key);
			$coded_card_password	= RC_Crypt::encrypt($card_password[0] , $this->auth_key);
			
			/* 在前后两次card_sn不一致时，检查是否有重复记录,一致时直接更新数据 */
			if ($card_sn != $_POST['old_card_sn']) {
				if ($this->db_virtual_card->where(array('goods_id' => $goods_id , 'card_sn' => $coded_card_sn))->count() > 0) {
					$this->showmessage(sprintf(RC_Lang::lang('card_sn_exist') ,"[ ".$card_sn." ]") , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
			
			/* 更新数据 */
			$end_date = strtotime($end_date[0]);
			$data = array(
					'card_sn'		=> $coded_card_sn,
					'card_password'	=> $coded_card_password,
					'end_date'		=> $end_date
			);
			$this->db_virtual_card->where(array('card_id' => $card_id ))->update($data);
			
			$this->showmessage(__('虚拟卡')."[ ".$card_sn[0]." ]".__('编辑成功'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('links' => $links));
		} else {
			/* 遍历更新 */
			$newArray = array();
			$card_id = explode(',' , $card_id); // 转换成数组
			$card_id = array_reverse($card_id);
			$i = 0;
			foreach ($card_id as $k => $v) {
				/* 加密后的 */
				$coded_card_sn		= RC_Crypt::encrypt($card_sn[$k] , $this->auth_key);
				$coded_card_password = RC_Crypt::encrypt($card_password[$k] , $this->auth_key);

				if (empty($card_sn[$k]) || empty($card_password[$k])) {
					$this->showmessage(__('卡片序号或者卡片密码不能为空！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
				/* 查询之前的记录，判断用户是否改变原来的值 */
				if (RC_Crypt::encrypt($card_sn[$k] , $this->auth_key) != $this->db_virtual_card->where(array('goods_id' => $goods_id , 'card_id' => $v))->get_field('card_sn')) {
					if ($this->db_virtual_card->where(array('goods_id' => $goods_id , 'card_sn' => RC_Crypt::encrypt($card_sn[$k] , $this->auth_key)))->count() == 0) {
						$data = array(
								'card_sn' 		=> $coded_card_sn,
								'card_password' => $coded_card_password,
								'end_date'		=> strtotime($end_date[$k])
						);
						$this->db_virtual_card->where(array('card_id' => $v ))->update($data);

					} else {
						$i++;
						array_push($newArray, $card_sn[$k]);
					}
				}
			}
			$count = count($card_id)-$i;
			$links[] = array('text' => RC_Lang::lang('go_list') , 'href' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$_POST['goods_id']));
			if ($newArray) {
				$this->showmessage(__('虚拟卡').join(',' , $newArray).__('已经存在!').__('本次更新'.$count.'条记录') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			} else {
				$this->showmessage(__('批量操作成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('links' => $links));
			}
		}
		/* 对操作的商品进行权限检查  BY：MaLiuWei  END */
	}
	
	/**
	 * 补货列表
	 */
	public function card() 
	{
		$this->admin_priv('virualcard');
		
		$goods_id = $_REQUEST['goods_id'];
		
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id , 'is_real' => 0,'extension_code' => 'virtual_card'))->get_field('goods_name');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('虚拟商品列表') , RC_Uri::url('goods/admin/init' , 'extension_code=virtual_card')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__($goods_name)));

		$card_list = get_replenish_list($_REQUEST);
		$this->assign('goods_id',		$goods_id);
		$this->assign('ur_here',		$goods_name);
		$this->assign('action_link',	array('text'	=> __('批量补货') , 'href'  => RC_Uri::url('goods/admin_virtual_card/replenish' , 'goods_id='.$goods_id)));	
		$this->assign('card_list',		$card_list);
		$this->assign('form_action',	RC_Uri::url('goods/admin_virtual_card/batch_drop_card' , 'goods_id='.$goods_id));
		$this->assign('batch_action',	RC_Uri::url('goods/admin_virtual_card/edit_replenish' , 'goods_id='.$goods_id));
		$this->assign_lang();
		$this->display('replenish_list.dwt');
	}

	/**
	 * 批量删除card
	 */
	public function batch_drop_card() 
	{
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
		/* 对更新补货信息进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对更新补货信息进行权限检查  BY：MaLiuWei  END */
		$num = count($_POST['checkboxes']);
		if ($this->db_virtual_card->in(array('card_id' => $_POST['checkboxes']))->delete()) {
			/* 商品数量减$num */
			update_goods_number(intval($_REQUEST['goods_id']));
			$this->showmessage(RC_Lang::lang('action_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('pjaxurl' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$_REQUEST['goods_id'])));
		}
	}
	
	/**
	 * 批量上传
	 */
	public function batch_card_add() 
	{
		$this->admin_priv('virualcard');
		
		RC_Script::enqueue_script('bootstrap-placeholder');
		RC_Script::enqueue_script('batch_card_add' , RC_App::apps_url('statics/js/batch_card_add.js' , __FILE__) , array() , false , false);
		
		$goods_id = $_GET['goods_id'];
		
		$goods_name = $this->db_goods->where(array('goods_id' => $goods_id, 'is_real' => 0, 'extension_code' => 'virtual_card'))->get_field('goods_name');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('虚拟商品列表') , RC_Uri::url('goods/admin/init' , 'extension_code=virtual_card')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here($goods_name , RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id)));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('补货')));		
		
		$this->assign('ur_here',			__('批量上传'));
		$this->assign('action_link',		array('text' => RC_Lang::lang('go_list') , 'href' => RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$goods_id)));	
		$this->assign('goods_id',			$goods_id);
		$this->assign('form_action',		RC_Uri::url('goods/admin_virtual_card/batch_confirm'));
		$this->assign('down_url',			RC_App::apps_url('statics/data' , __FILE__));

		$this->assign_lang();
		$this->display('batch_card_info.dwt');
	}
	
	/**
	 * 批量添加补货确认页面
	 */
	public function batch_confirm() 
	{		
		$this->admin_priv('virualcard');
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('虚拟商品列表') , RC_Uri::url('goods/admin/init' , 'extension_code=virtual_card')));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__(RC_Lang::lang('batch_card_add')) , RC_Uri::url('goods/admin_virtual_card/batch_card_add' , 'goods_id='.$_REQUEST['goods_id'])));
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('批量补货确认')));
		RC_Script::enqueue_script('batch_card_add' , RC_App::apps_url('statics/js/batch_card_add.js' , __FILE__) , array() , false , false);
		/* 检查上传是否成功 */	
		if ((isset($_FILES['uploadfile']['error']) && $_FILES['uploadfile']['error'] == 0) ||
		(!isset($_FILES['uploadfile']['error']) && isset($_FILES['uploadfile']['tmp_name']) && $_FILES['uploadfile']['tmp_name'] != 'none')) {
			$data = file($_FILES['uploadfile']['tmp_name']);
		} else {
			$this->showmessage(__('请选择上传文件') , ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR ,array('links' => array(array('text'=>'返回上一页','href'=>'javascript:history.go(-1)'))));				
		}
		
		$rec = array(); //数据数组
		$i = 0;
		$separator = trim($_POST['separator']);
		foreach ($data as $line) {
			$row = explode($separator , $line);
			switch(count($row)) {
				case '3':
				$rec[$i]['end_date'] = $row[2];
				case '2':
				$rec[$i]['card_password'] = $row[1];
				case '1':
				$rec[$i]['card_sn']  = $row[0];
				break;
				default:
				$rec[$i]['card_sn']  = $row[0];
				$rec[$i]['card_password'] = $row[1];
				$rec[$i]['end_date'] = $row[2];
				break;
			}
			$i++;
		}
		
		$this->assign('ur_here',			__('批量补货确认'));
		$this->assign('action_link',		array('text' => RC_Lang::lang('batch_card_add') , 'href' => RC_Uri::url('goods/admin_virtual_card/batch_card_add' , 'goods_id='.$_REQUEST['goods_id'])));
		$this->assign('list',				$rec);
		$this->assign('form_action',		RC_Uri::url('goods/admin_virtual_card/batch_insert'));
		
		$this->assign_lang();
		$this->display('batch_card_confirm.dwt');
	}
	
	/**
	 * 批量上传处理
	 */
	public function batch_insert() 
	{
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
		$add_time = RC_Time::gmtime();
		$i = 0;
		foreach ($_POST['checked'] as $key) {
			$rec['card_sn']			= RC_Crypt::encrypt($_POST['card_sn'][$key] , $this->auth_key);
			$rec['card_password']	= RC_Crypt::encrypt($_POST['card_password'][$key] , $this->auth_key);
			$rec['crc32']			= crc32($this->auth_key);
			$rec['end_date']		= empty($_POST['end_date'][$key]) ? 0 : strtotime($_POST['end_date'][$key]);
			$rec['goods_id']		= $_POST['goods_id'];
			$rec['add_date']		= $add_time;

			$this->db_virtual_card->insert($rec);
			$i++;
		}
		
		/* 更新商品库存 */
		update_goods_number(intval($_REQUEST['goods_id']));
		$link = RC_Uri::url('goods/admin_virtual_card/card' , 'goods_id='.$_REQUEST['goods_id']);
		$this->showmessage(sprintf(RC_Lang::lang('batch_card_add_ok') , $i) , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('pjaxurl' => $link));
	}
	
	
	/**
	 * 更新加密串
	 */
	
	public function edit_virtual_card() 
	{
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
		/* 对更新补货信息进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对更新补货信息进行权限检查  BY：MaLiuWei  END */
		$old_key = $_POST['old_key'];
		$new_key = $_POST['new_key'];
		
		/* 验证老的加密串是否正确 */
		if ($this->auth_key) {
			if ($old_key != $this->auth_key) {
				$this->showmessage(__('原加密串不正确！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		} else {
			/* 初始化加密串 */
			ecjia_config::instance()->write_config('auth_key', '888888');
			$this->showmessage(__('检测到您之前可能未设置加密串，系统将初始化加密串为 888888,请及时修改新的加密串！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_INFO);
		}
		
		
		/* 检查原加密串和新加密串是否相同 */
		if ($old_key == $new_key) {
			$this->showmessage(__('新加密串跟原加密串相同!') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 生成新的crc32效验码*/
		$old_crc32 = crc32($old_key);
		$new_crc32 = crc32($new_key);

		/* 替换原来使用的加密串 */
		$data = $this->db_virtual_card->field('card_id, card_sn, card_password')->where(array('crc32' => array('neq' => $new_crc32)))->select();
		foreach ($data as $key => $row) {
			$row['card_sn']			= RC_Crypt::encrypt(RC_Crypt::decrypt($row['card_sn'] , $old_key) , $new_key);
			$row['card_password']	= RC_Crypt::encrypt(RC_Crypt::decrypt($row['card_password'] , $old_key) , $new_key);
			$row['crc32']			= $new_crc32;
			$this->db_virtual_card->where(array('card_id' => $row['card_id']))->update($row);
		}	
		/* 记录日志 */
		ecjia_admin::admin_log($new_key, 'edit' , 'encryption');

		/* 更新数据 */
		ecjia_config::instance()->write_config('auth_key' , $new_key);
		$this->showmessage(__('新加密串设置成功！同时本次还更新了')."[ ". count($data)." ]".__('条虚拟卡信息！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 切换是否已出售状态
	 */
	public function toggle_sold() 
	{		
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
		/* 对 切换是否已出售状态进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对 切换是否已出售状态进行权限检查  BY：MaLiuWei  END */
		$id = intval($_POST['id']);
		$val = intval($_POST['val']);
		$data = array(
			'is_saled' => $val
			);
		if ($this->db_virtual_card->join(null)->where(array('card_id' => $id))->update($data)) {
			/* 修改商品库存 */
			$goods_id = $this->db_virtual_card->where(array('card_id' => $id))->get_field('goods_id');
			update_goods_number($goods_id);
			$this->showmessage(__('状态切换成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('content' => $val));
		} else {
			$this->showmessage(RC_Lang::lang('action_fail') . "\n" .$this->db_virtual_card->error() , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	
	/**
	 * 删除卡片
	 */
	public function remove_card() 
	{
		$this->admin_priv('virualcard', ecjia::MSGTYPE_JSON);
		
		/* 对 删除卡片进行权限检查  BY：MaLiuWei  START */
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 对 删除卡片进行权限检查  BY：MaLiuWei  END */
		$id = intval($_GET['id']);

		$row = $this->db_virtual_card->field('card_sn, goods_id')->find(array('card_id' => $id));
		
		if ($this->db_virtual_card->where(array('card_id' => $id))->delete()) {
			/* 修改商品数量 */
			update_goods_number($row['goods_id']);
			$this->showmessage(__('虚拟卡')."[ ".RC_Crypt::decrypt($row['card_sn'] , $this->auth_key)." ]".__('删除成功！') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage($this->db_virtual_card->error() , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
	}
}

// end