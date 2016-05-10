<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.virtual_card.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div class="row-fluid">
	<div class="alert alert-info">
			{$lang.user_guide}
	</div>
</div>
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>

<div class="row-fluid">
	<div class="span12">
		<form id="form-privilege" class="form-horizontal" name="card_Form" action="{$form_action}" method="post">
			<fieldset>
				<div class="control-group">
					<label class="control-label">{$lang.label_old_string}</label>
					<div class="controls">
						<input name="old_key" type="text" class="w355" />
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">{$lang.label_new_string}</label>
					<div class="controls">
						<input name="new_key" type="text" class="w355" />
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<!-- {/block} -->