<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 关键词搜索商品
 * @author royalwang
 *
 */
class searchKeywords_module extends api_front implements api_interface {
	
	public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {	
    	$this->authSession();	
    	
		$db = RC_Loader::load_app_model('tag_model','goods');
		$tags = $db->field('tag_words, COUNT(tag_id) AS tag_count')
					->group('tag_words')
					->order(array('tag_count' => 'desc'))
					->limit(20)
					->select();
		$data = array();
		foreach ($tags as $val) {
			$data[] = $val['tag_words'];
		}
		return array('data' => $data);
	}
}

// end