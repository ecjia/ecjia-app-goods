// JavaScript Document
;
(function(app, $) {
	app.product = {
		init: function() {
			$('input[name="submit"]').on('click', function(e) {
				e.preventDefault();
				var $form = $("form[name='theForm']");
				$form.ajaxSubmit({
					dataType: "json",
					success: function(data) {
						ecjia.merchant.showmessage(data);
					}
				});
			});
			app.product_info.previewImage();
		},

		/**
		 * clone_product 克隆添加一个节点的方法
		 */
		clone_product: function(options) {
			var tmpObj = options.parentobj.clone();

			tmpObj.find('[data-toggle="clone_product"]').attr('data-toggle', 'remove_product').on('click', function() {
				tmpObj.remove()
			}).find('i').attr('class', 'fa fa-times ecjiafc-red');

			$('.product_list').children('tbody').append(tmpObj);
			tmpObj.find('.chzn-container').remove();
			tmpObj.find('select').removeClass('chosen_hide').removeClass('chzn-done').attr({
				'id': ''
			}).chosen();
		}
	};
	
	app.product_info = {
		init: function() {
			app.product_info.previewImage();
			app.product_info.fileupload();
		},

		fileupload: function() {
			$(".fileupload-btn").on('click', function(e) {
				e.preventDefault();
				$(this).parent().find("input").trigger('click');
			})
		},

		previewImage: function(file) {
			if (file == undefined) {
				return false;
			}
			if (file.files && file.files[0]) {
				var reader = new FileReader();
				reader.onload = function(evt) {
					$(file).siblings('.fileupload-btn').addClass('preview-img').css("backgroundImage", "url(" + evt.target.result + ")");
					$('.thumb_img').removeClass('hide').find('.fileupload-btn').addClass('preview-img').css("backgroundImage", "url(" + evt.target.result + ")");
				}
				reader.readAsDataURL(file.files[0]);
			} else {
				$(file).prev('.fileupload-exists').remove();
				$(file).siblings('.fileupload-btn').addClass('preview-img').css("filter", "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src='" + file.value + "'");
			}
		},
	};

	
	app.product_spec = {
			init: function() {
				app.product_spec.select_spec();
			},

			select_spec: function() {
	            $("a[data-toggle='modal']").off('click').on('click', function (e) {
	            	e.preventDefault();
	                var $this = $(this);
	                var goods_id = $this.attr('goods-id');
	                var url = $this.attr('attr-url');
	                $.post(url, {'goods_id': goods_id}, function (data) {
	                	$('.modal').html(data.data);
	                	
	                	$(".insertSubmit").on('click', function(e) {
	        				$("form[name='insertForm']").submit();
	        			});	
	                	
	        			$("form[name='insertForm']").on('submit', function(e) {
	        				e.preventDefault();
	        			});
	        			
	        			var $this = $('form[name="insertForm"]');
	        			var option = {
	        				submitHandler: function() {
	        					$this.ajaxSubmit({
	        						dataType: "json",
	        						success: function(data) {
	        							$('#myModal1').modal('hide');
	        							$(".modal-backdrop").remove();
	        							ecjia.pjax(data.url, function() {
	        								ecjia.merchant.showmessage(data);
	        							})
	        						}
	        					});
	        				},
	        			};

	        			var options = $.extend(ecjia.merchant.defaultOptions.validate, option);
	        			$this.validate(options);
	                }, 'json');
				})
	        
			}
		};
	
	
	/**
	 * clone_product触发器
	 * data-parent 		要复制的父级节点
	 */
	$(document).on('click', '.add_item', function(e) {
		e.preventDefault();
		var $this = $(this),
			$parentobj = $('.clone_div').children().find('.attr_row'),
			option = {
				parentobj: $parentobj
			};
		app.product.clone_product(option);
	});

	/**
	 * remove_product 删除节点obj
	 * data-parent 要删除的父级节点
	 */
	$(document).on('click', '[data-toggle="remove_product"]', function(e) {
		e.preventDefault();
		var $this = $(this),
			$parentobj = $this.parents($this.attr('data-parent'));
		$parentobj.remove();
	});

})(ecjia.merchant, jQuery);

// end