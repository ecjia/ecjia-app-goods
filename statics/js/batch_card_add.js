// JavaScript Document
;(function(app, $) {
	app.batch_card = {
		init : function() {
			app.batch_card.submit();
		},
		submit : function() {
			var $this = $('form[name="batchForm"]');
			var option = {
				rules:{
					separator : {required : true},
					},
				messages:{
					separator : {required : "分隔符不能为空！"},
					},
			}
			
			var options = $.extend(ecjia.admin.defaultOptions.validate, option);
			$this.validate(options);
		},
		confirm : function() {
			app.batch_card.batch_confirm();
		},
		batch_confirm : function() {
			$("form[name='batch_confirm']").on('submit', function(e) {
				e.preventDefault();
				$(this).ajaxSubmit({
					dataType:"json",
					success:function(data){
						ecjia.admin.showmessage(data);
					}
				});
			});
		}
	};
		
})(ecjia.admin, jQuery);


// end
