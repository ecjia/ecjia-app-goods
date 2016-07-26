// JavaScript Document

;(function(app, $) {
	app.seller_list = {
		init : function() {
			app.seller_list.search();
		},
		search : function() {
			$("form[name='searchForm']").on('submit', function(e){
				e.preventDefault();
				var keyword = $("input[name='keywords']").val();
				var url = $(this).attr('action');
				ecjia.pjax(url + '&keywords=' + keyword);
			});
		},
		
	};
})(ecjia.admin, jQuery);

