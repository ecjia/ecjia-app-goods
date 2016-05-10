<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.batch_card.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div class="alert alert-info">
	<div class="controls">
		{$lang.use_help}
		<span class="m_l9">4.<a href="{$down_url}/virtual_goods_list.csv">{$lang.download_file}</a></span>
	</div>
</div>
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax"> <i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<div class="tabbable">
			<ul class="nav nav-tabs">
				<li class="tab"><a class="data-pjax" href='{RC_Uri::url("goods/admin_virtual_card/replenish", "goods_id={$goods_id}")}'>{t}批量补货{/t}</a></li>
				<li class="tab active"><a href="#tab2">{t}批量上传{/t}</a></li>					
			</ul>
		</div>
		<div class="tab-content">
			<div class="tab-pane active" id="tab2">
				<div class="row-fluid">
					<div class="span12">
						<form class="form-horizontal" method="post" action="{$form_action}" name="batchForm" enctype="multipart/form-data">
							<fieldset>
								<div class="control-group">
									<label class="control-label">{$lang.separator}</label>
									<div class="controls">
										<input type="text" name="separator" class="w100" value=","/>
										<span class="input-must">{$lang.require_field}</span>
									</div>
								</div>
								<div class="control-group ">
									<label class="control-label">{$lang.uploadfile}</label>
									<div data-provides="fileupload" class="controls fileupload fileupload-new">
										<span class="btn btn-file">
											<span class="fileupload-new">选择文件</span>
											<span class="fileupload-exists">修改</span>
											<input type="file" name="uploadfile"></span>
											<span class="fileupload-preview"></span>
											<a style="float: none" data-dismiss="fileupload" class="close fileupload-exists" href="">&times;</a>
											<span class="input-must">{$lang.require_field}</span>
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<input type="submit" class="btn btn-gebo" value="{$lang.button_submit}" />&nbsp;&nbsp;&nbsp;
										<input type="hidden" name="goods_id" value="{$goods_id}" />
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- {/block} -->