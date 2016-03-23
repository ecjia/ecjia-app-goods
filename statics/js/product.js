// JavaScript Document
;(function(app, $) {
	app.product = {
			init : function() {
				$('input[name="submit"]').on('click', function(e) {
					e.preventDefault();
					var $form = $("form[name='theForm']");
					$form.ajaxSubmit({
						dataType : "json",
						success : function(data) {
							ecjia.admin.showmessage(data);
						}
					});
				})
			}
		};
})(ecjia.admin, jQuery);

// end