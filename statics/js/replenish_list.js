// JavaScript Document
;(function(app, $) {
	app.replenish = {
		list : function () {
			app.replenish.search();
			app.replenish.batch_edit();
		},
		edit : function () {
			/* 加载日期控件 */
			$(".date").datepicker({
				format: "yyyy-mm-dd"
			});
			var $this = $('form[name="theForm"]');
			var option = {
				rules:{
					card_sn			: {required : true},
					card_password	: {required : true}
				},
				messages:{
					card_sn			: {required : "请输入卡片序号"},
					card_password	: {required : "请输入卡片密码"}
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

		search : function () {
			$("form[name='searchForm']").on('submit', function(e) {
				e.preventDefault();
				var url = $("form[name='searchForm']").attr('action') + '&keyword=' +$("input[name='keyword']").val();
				ecjia.pjax(url);
			});
		},
		
		batch_edit : function () {
			$(".batch_edit").on('click', function(e) {
				e.preventDefault();
				var card_id = [];		
				$(".checkbox:checked").each(function () {
					card_id.push($(this).val());
				});
				if (card_id == '') {
					smoke.alert('请先选择需要操作的信息');
					return false;
				} else {
					var url = $("form[name='listForm']").attr('data-batch') + '&card_id=' +card_id + '&batch=1';
					ecjia.pjax(url);
				}
			});
		},
	};
})(ecjia.admin, jQuery);
// end
