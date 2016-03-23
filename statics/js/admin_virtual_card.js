// JavaScript Document
;(function(app, $) {
	app.virtual_card = {
		init : function() {
			/* 加载日期控件 */
			$(".date").datepicker({
				format: "yyyy-mm-dd"
			});
			app.virtual_card.submit();
		},
		submit : function() {
			var $this = $('form[name="card_Form"]');
			var option = {
				rules:{
					old_key : {required : true},
					new_key : {required : true}
					},
				messages:{
					old_key : {required : "请输入原加密串！"},
					new_key : {required : "请输入新加密串！"}
					},
				submitHandler:function(){
					$this.ajaxSubmit({
						dataType:"json",
						success:function(data) {
							ecjia.admin.showmessage(data);
						}
					});
				}
			}
			
			var options = $.extend(ecjia.admin.defaultOptions.validate, option);
			$this.validate(options);
		},
		
	};

})(ecjia.admin, jQuery);


// end
