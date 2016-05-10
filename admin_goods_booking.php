<?php
/**
 * ECJIA 缺货处理管理程序
 */

defined('IN_ECJIA') or exit('No permission resources.');

class admin_goods_booking extends ecjia_admin {
	private $dbview;
	private $db_book;
	public function __construct() {
		parent::__construct();

		$this->dbview	= RC_Loader::load_app_model('order_booking_goods_viewmodel');
		$this->db_book	= RC_Loader::load_app_model('booking_goods_model','goods');
		
		RC_Lang::load('admin_goods_booking');
		
		/* 加载全局js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		
		RC_Script::enqueue_script('smoke');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('jquery-uniform');
		RC_Script::enqueue_script('admin_goods_booking', RC_Uri::home_url('content/apps/goods/statics/js/admin_goods_booking.js'));
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('缺货登记列表'), RC_Uri::url('goods/admin_goods_booking/init')));
	}
	
	/**
	 * 列出所有订购信息
	 */
	public function init() {
		$this->admin_priv('booking');
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('缺货登记列表')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台缺货登记列表页面，系统中有关缺货信息都在此列表中显示。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:缺货登记#.E7.BC.BA.E8.B4.A7.E7.99.BB.E8.AE.B0.E5.88.97.E8.A1.A8" target="_blank">关于商品缺货登记帮助文档</a>') . '</p>'
		);
		
		RC_Loader::load_app_func('functions');
		$list = get_bookinglist();
		
		$this->assign('ur_here', __('缺货登记列表'));
		$this->assign('booking_list', $list);
		$this->assign('form_action', RC_Uri::url('goods/admin_goods_booking/init'));
		$this->assign('filter', $list['filter']);
		$this->assign_lang();
		
		$this->display('booking_list.dwt');
	}
	
	/**
	 * 删除缺货登记
	 */
	public function remove() {
		$this->admin_priv('booking', ecjia::MSGTYPE_JSON);

		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$id = intval($_GET['id']);
        $goods_name = $this->dbview->where(array('rec_id' => $id))->get_field('goods_name');

		if (empty($_SESSION['ru_id'])) {
			$this->db_book->where(array('rec_id' => $id))->delete();
		}
		/* 记录日志 */
		RC_Loader::load_app_func('functions');
		assign_adminlog_content();
		ecjia_admin::admin_log('商品名称是'.$goods_name, 'remove', 'goods_booking');
		$this->showmessage(RC_Lang::lang('js_languages/drop_success') , ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS );
	}
	
	
	/**
	 * 显示详情
	 */
	public function detail() {
		$this->admin_priv('booking');
		$id = intval($_GET['id']);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('查看详情')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台缺货登记详情页面，系统中有关缺货的详情信息显示在此页面。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:缺货登记#.E6.9F.A5.E7.9C.8B.E8.AF.A6.E6.83.85" target="_blank">关于商品缺货登记详情帮助文档</a>') . '</p>'
		);
		
		RC_Loader::load_app_func('functions');
		$booking_email = get_booking_info($id);
		
		$this->assign('booking', $booking_email);
		$this->assign('ur_here', RC_Lang::lang('detail'));
		$this->assign('action_link', array('text' => RC_Lang::lang('06_undispose_booking'), 'href' => RC_Uri::url('goods/admin_goods_booking/init')));
		$this->assign('form_action', RC_Uri::url('goods/admin_goods_booking/update'));
		$this->assign_lang();
		
		$this->display('booking_info.dwt');
	}
	
	/**
	 * 处理提交数据
	 */
	public function update() {
		/* 权限判断 */
		$this->admin_priv('booking', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		RC_Loader::load_app_func('common', 'goods');
		$dispose_note = !empty($_POST['dispose_note']) ? trim($_POST['dispose_note']) : '';
	
		/* 邮件通知处理流程 */
		if (!empty($_POST['send_email_notice']) or isset($_POST['remail'])) {
			//获取邮件中的必要内容
			$booking_info = $this->dbview->join('goods')->find(array('bg.rec_id' => $_POST['rec_id']));

			/* 设置缺货回复模板所需要的内容信息 */
			$tpl_name = 'goods_booking';
			$template = RC_Api::api('mail', 'mail_template', $tpl_name);
			$goods_link = RC_Uri::url('goods/index/lists' , 'id='.$booking_info['goods_id']);

			$this->assign('user_name', $booking_info['link_man']);
			$this->assign('goods_link', $goods_link);
			$this->assign('goods_name', $booking_info['goods_name']);
			$this->assign('dispose_note', $dispose_note);
			$this->assign('shop_name', "<a href='".SITE_URL."'>" . ecjia::config('shop_name') . '</a>');
			$this->assign('send_date', date('Y-m-d'));

			$content = $this->fetch_string($template['template_content']);
			/* 发送邮件 */
			if (RC_Mail::send_mail($booking_info['link_man'], $booking_info['email'], $template['template_subject'], $content, $template['is_html'])) {
				$this->showmessage('邮件发送成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS , array('pjaxurl' => RC_Uri::url('goods/admin_goods_booking/detail','id='.$_POST['rec_id'])));
			} else {
				$this->showmessage(RC_Lang::lang('mail_send_fail'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR );
			}
		} else {
			if (empty($dispose_note)) {
				$this->showmessage(RC_Lang::lang('js_languages/no_note'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
			
			$data = array(
				'is_dispose'	=> 1,
				'dispose_note'	=> $dispose_note,
				'dispose_time'	=> RC_Time::gmtime(),
				'dispose_user'	=> $_SESSION['admin_name'],
			);
			
			if ($this->db_book->where(array('rec_id' => $_POST['rec_id']))->update($data)) {
				$goods_name = $this->dbview->where(array('rec_id' => $_POST['rec_id']))->get_field('goods_name');
				/* 记录日志 */
				RC_Loader::load_app_func('functions');
				assign_adminlog_content();
				ecjia_admin::admin_log('商品名称是，'.$goods_name, 'setup', 'goods_booking');
				$this->showmessage(RC_Lang::lang('dispose_succeed'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('goods/admin_goods_booking/detail', 'id='.$_POST['rec_id'])));
			}
		}
		$this->showmessage(__('请返回入驻商后台进行操作'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
	}
}

//  end