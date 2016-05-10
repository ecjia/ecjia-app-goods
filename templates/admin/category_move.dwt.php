<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_category_move.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div class="alert alert-info">
	<a class="close" data-dismiss="alert">×</a>
	<strong>{t}提示：{/t}</strong>{t}{$lang.cat_move_desc}{/t}<br />{t}{$lang.cat_move_notic}{/t}
</div>
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<form class="form-horizontal" action="{$form_action}" method="post" name="theForm">
			<fieldset>
				<div class="cat_move">
					<div class="control-group">
						<label class="control-label">
							{$lang.source_cat}：
						</label>
						<div class="controls">
							<select name="cat_id">
								<option value="0">{$lang.select_please}</option>
								<!-- {$cat_select} -->
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							{$lang.target_cat}：
						</label>
						<div class="controls">
							<select name="target_cat_id">
								<option value="0">{$lang.select_please}</option>
								<!-- {$cat_select} -->
							</select>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button class="btn btn-gebo" type="submit">{$lang.start_move_cat}</button>
						</div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<!-- {/block} -->