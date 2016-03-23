// JavaScript Document
;(function(app, $) {
	app.goods_type = {
		init : function() {
			app.goods_type.edit_type();
		},
		edit_type : function() {
			var $this = $('form[name="theForm"]');
			var option = {
				rules:{
					cat_name : {required : true},
					},
				messages:{
					cat_name : {required : "请输入商品类型名称"},
					},
				submitHandler:function() {
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
		}
	};

	app.goods_arrt = {
		init : function() {
			app.goods_arrt.change_attr();
		},

		change_attr : function() {
			$('select[name="goods_type"]').on('change', function() {
				var $this = $(this),
					url = $this.attr('data-url') + $this.val();
				ecjia.pjax(url);
			});
		},

	};

	app.edit_arrt = {
		init : function() {
			//单选框切换事件
			$(document).on('click', 'input[name="attr_input_type"]', function(e){
				$("input[name='attr_input_type']:checked").val() == 1 ? $('.attr_values').show() : $('.attr_values').hide();
			});
			$('input[name="attr_input_type"]:checked').trigger('click');

			app.edit_arrt.edit_type_attr();
		},

		edit_type_attr : function() {
			var $this = $('form[name="theForm"]');
			var option = {
					rules:{
						attr_name : {required : true},
						cat_id : {min : 1},
						},
					messages:{
						attr_name : {required : "请输入属性名称"},
						cat_id : {min : "请选择所属商品类型"}
						},
					submitHandler:function() {
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
