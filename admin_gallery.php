<?php
/**
 *  ECJIA 商品相册管理
 */
defined('IN_ECJIA') or exit('No permission resources.');

class admin_gallery extends ecjia_admin {
	private $db_goods;
	private $db_goods_gallery;
	
    public function __construct() {
        parent::__construct();
        
        $this->db_goods 		= RC_Loader::load_app_model('goods_model');
        $this->db_goods_gallery = RC_Loader::load_app_model('goods_gallery_model');
    }
    
    /**
     * 商品相册
     */
    public function init() {
        $this->admin_priv('goods_update');
        
        RC_Script::enqueue_script('goods_list', RC_App::apps_url('statics/js/goods_list.js', __FILE__), array('ecjia-common', 'ecjia-utils', 'smoke', 'jquery-validate', 'jquery-form', 'bootstrap-placeholder', 'jquery-wookmark', 'jquery-imagesloaded', 'jquery-colorbox'));
        RC_Script::enqueue_script('jquery-dropper', RC_Uri::admin_url() . '/statics/lib/dropper-upload/jquery.fs.dropper.js', array(), false, true);

        RC_Style::enqueue_style('jquery-colorbox');
        RC_Script::enqueue_script('jquery-chosen');
        RC_Style::enqueue_style('chosen');
        RC_Script::enqueue_script('replenish_list', RC_App::apps_url('statics/js/replenish_list.js', __FILE__), array());
        RC_Script::enqueue_script('batch_card_add', RC_App::apps_url('statics/js/batch_card_add.js', __FILE__), array());
        RC_Style::enqueue_style('goods-colorpicker-style', RC_Uri::admin_url() . '/statics/lib/colorpicker/css/colorpicker.css');
        RC_Script::enqueue_script('goods-colorpicker-script', RC_Uri::admin_url('/statics/lib/colorpicker/bootstrap-colorpicker.js'), array());
//         RC_Script::enqueue_script('article-tinymce-script', RC_Uri::vendor_url() . '/tinymce/tinymce.min.js', array(), false, true);
        RC_Script::enqueue_script('bootstrap-editable-script', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js', array(), false, true);
        RC_Style::enqueue_style('bootstrap-editable-css', RC_Uri::admin_url() . '/statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css');
        RC_Script::enqueue_script('jquery-uniform');
        RC_Style::enqueue_style('uniform-aristo');
        RC_Script::enqueue_script('jq_quicksearch', RC_Uri::admin_url() . '/statics/lib/multi-select/js/jquery.quicksearch.js', array('jquery'), false, true);

		RC_Style::enqueue_style('goodsapi', RC_Uri::home_url('content/apps/goods/statics/styles/goodsapi.css'));
		RC_Script::enqueue_script('ecjia-region',RC_Uri::admin_url('statics/ecjia.js/ecjia.region.js'), array('jquery'), false, true);

        RC_Loader::load_app_class('goods', 'goods');
        RC_Loader::load_app_class('goods_image', 'goods', false);
        
        RC_Loader::load_app_func('functions');
        RC_Loader::load_app_func('common');
        RC_Loader::load_app_func('system_goods');
        RC_Loader::load_app_func('category');
        
        $goods_list_jslang = array(
        	'user_rank_list'	=> get_user_rank_list(),
        	'marketPriceRate'	=> ecjia::config('market_price_rate'),
        	'integralPercent'	=> ecjia::config('integral_percent'),
        );
        RC_Script::localize_script('goods_list', 'admin_goodsList_lang', $goods_list_jslang );
        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods.goods_list'), RC_Uri::url('goods/admin/init')));
		$this->assign('ur_here', RC_Lang::get('goods::goods.edit_goods_photo'));
        ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('goods::goods.edit_goods_photo')));
        ecjia_screen::get_current_screen()->add_help_tab(array(
	        'id'		=> 'overview',
	        'title'		=> RC_Lang::get('goods::goods.overview'),
	        'content'	=>
	        '<p>' . RC_Lang::get('goods::goods.goods_gallery_help') . '</p>'
        ));
        
        ecjia_screen::get_current_screen()->set_help_sidebar(
       	 	'<p><strong>' . RC_Lang::get('goods::goods.more_info') . '</strong></p>' .
        	'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:商品列表#.E5.95.86.E5.93.81.E7.9B.B8.E5.86.8C" target="_blank">'. RC_Lang::get('goods::goods.about_goods_gallery') .'</a>') . '</p>'
        );
        
        $goods_id = intval($_GET['goods_id']);

        $this->tags = get_goods_info_nav($goods_id, $extension_code);
        $this->tags['edit_goods_photo']['active'] = 1;
        /* 图片列表 */
        $img_list = $this->db_goods_gallery->where(array('goods_id' => $goods_id))->select();
        
        $img_list_sort = $img_list_id = array();
        $no_picture = ecjia::config('no_picture');
        if (substr($no_picture, 0, 1) == '.') {
        	$no_picture = str_replace('../', '', $no_picture);
        }
         /* 格式化相册图片路径 */
        if (!empty($img_list)) {
        	foreach ($img_list as $key => $gallery_img) {
        		$desc_index = intval(strrpos($gallery_img['img_original'], '?')) + 1;
        		!empty($desc_index) && $img_list[$key]['desc'] = substr($gallery_img['img_original'], $desc_index);
        		$img_list[$key]['img_url'] = empty($gallery_img['img_url']) ?  RC_Upload::upload_url().'/'.$no_picture : RC_Upload::upload_url() . '/' . $gallery_img['img_url'];
        		$img_list[$key]['thumb_url'] = empty($gallery_img['thumb_url']) ?  RC_Upload::upload_url().'/'.$no_picture : RC_Upload::upload_url() . '/' . $gallery_img['thumb_url'];
        		$img_list[$key]['img_original'] = empty($gallery_img['img_original']) ?  RC_Upload::upload_url().'/'.$no_picture : RC_Upload::upload_url() . '/' . $gallery_img['img_original'];
        	
        		$img_list_sort[$key] = $img_list[$key]['desc'];
        		$img_list_id[$key] = $gallery_img['img_id'];
        	}
        	
        	//先使用sort排序，再使用id排序。
        	array_multisort($img_list_sort, $img_list_id, $img_list);
        }
		
        //设置选中状态,并分配标签导航
        $this->assign('tags', $this->tags);
        $this->assign('action_link', array('href' => RC_Uri::url('goods/admin/init'), 'text' => RC_Lang::get('goods::goods.goods_list')));
        
        $this->assign('goods_id', $goods_id);
        $this->assign('img_list', $img_list);
        $this->assign('form_action', RC_Uri::url('goods/admin_gallery/insert', "goods_id=$goods_id"));
        
        $this->display('goods_photo.dwt');
    }
    
    /**
     * 上传商品相册图片的方法
     */
    public function insert() {
        $this->admin_priv('goods_update');
        
        $step = isset($_GET['step']) ? trim($_GET['step']) : '';
        
        RC_Loader::load_app_class('goods_image_data', 'goods', false);
        $goods_id = intval($_GET['goods_id']);

        if (empty($goods_id)) {
            $this->showmessage(RC_Lang::get('goods::goods.parameter_missing'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }

        $upload = RC_Upload::uploader('image', array('save_path' => './images', 'auto_sub_dirs' => true));
        $upload->add_saving_callback(function ($file, $filename) {
            return true;
        });
        
        if (!$upload->check_upload_file($_FILES['img_url'])) {
            $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }

        $image_info = $upload->upload($_FILES['img_url']);
        if (empty($image_info)) {
            $this->showmessage($upload->error(), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
        }
        $goods_image = new goods_image_data($image_info['name'], $image_info['tmpname'], $image_info['ext'], $goods_id);
        $goods_image->update_gallery();
        
        $arr['goods_id'] = $goods_id;
        if (!empty($code)) {
        	$arr['extension_code'] = $code;
        }
        	
        if ($step) {
        	$arr['step'] = 'add_goods_gallery';
        }
        $url = RC_Uri::url('goods/admin_gallery/init', $arr);
        $this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => $url));
    }

	/**
	* 删除图片
	*/
	public function drop_image() {
		$this->admin_priv('goods_update', ecjia::MSGTYPE_JSON);
		$img_id = empty($_GET['img_id']) ? 0 : intval($_GET['img_id']);
		
		/* 删除图片文件 */
		$row = $this->db_goods_gallery->goods_gallery_find('img_url, thumb_url, img_original', array('img_id' => $img_id));
		strrpos($row['img_original'], '?') && $row['img_original'] = substr($row['img_original'], 0, strrpos($row['img_original'], '?'));

		RC_Loader::load_app_class('goods_imageutils', 'goods', false);
		if ($row['img_url']) {
			RC_Filesystem::disk()->delete(goods_imageutils::getAbsolutePath($row['img_url']));
		}
		if ($row['thumb_url']) {
			RC_Filesystem::disk()->delete(goods_imageutils::getAbsolutePath($row['thumb_url']));
		}
		if ($row['img_original']) {
			RC_Filesystem::disk()->delete(goods_imageutils::getAbsolutePath($row['img_original']));
		}

		/* 删除数据 */
		$this->db_goods_gallery->goods_gallery_delete($img_id);
		$this->showmessage(RC_Lang::get('goods::goods.drop_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}

	/**
	* 修改相册图片描述
	*/
	public function update_image_desc() {
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		$img_id = $_GET['img_id'];
		$val = $_GET['val'];
		
		if ($this->db_goods_gallery->where(array('img_id' => $img_id))->update(array('img_desc' => $val))) {
			$this->showmessage(RC_Lang::get('goods::goods.edit_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
		} else {
			$this->showmessage(RC_Lang::get('goods::goods.edit_fail'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}

	/**
	* 相册图片排序
	*/
	public function sort_image() {
		$this->admin_priv('goods_manage', ecjia::MSGTYPE_JSON);
		
		$sort = $_GET['info'];
		$i = 1;
		foreach ($sort as $k => $v) {
			//$v['img_original'] = substr($v['img_original'], 0, strrpos($v['img_original'], '?')) . '?' . $i;
			//$v['img_original'] = str_replace(SITE_UPLOAD_URL . '/', '', $v['img_original']);
			$v['img_original'] = strrpos($v['img_original'], '?') > 0 ? substr($v['img_original'], 0, strrpos($v['img_original'], '?')) . '?' . $i : $v['img_original']. '?' . $i;
			$v['img_original'] = str_replace(RC_Upload::upload_url() . '/', '', $v['img_original']);
			$i++;
			$where = array('img_id' => $v['img_id']);
			$data = array('img_original' => $v['img_original']);
			
			$this->db_goods_gallery->where($where)->update($data);
		}
		$this->showmessage(RC_Lang::get('goods::goods.save_sort_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
}

// end