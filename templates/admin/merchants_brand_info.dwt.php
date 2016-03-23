<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_brand_info.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<form class="form-horizontal" method="post" action="{$form_action}" name="theForm" enctype="multipart/form-data" data-edit-url="{RC_Uri::url('goods/merchants_brand/edit')}">
			<fieldset>
				<div class="control-group formSep">
					<label class="control-label">{$lang.brand_name}：</label>
					<div class="controls">
						<input class="w350" type="text" name="brandName" maxlength="60" value="{$brand.brandName}" />
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{$lang.site_url}：</label>
					<div class="controls">
						<input class="w350" type="text" name="site_url" maxlength="60" size="40" value="{$brand.site_url}" />
					</div>
				</div>   
				<div class="control-group formSep">
					<label class="control-label">{$lang.brand_logo}：</label>
					<div class="controls chk_radio">
						<input type="radio" name="brand_logo_type" value='0'{if !$brand.type} checked="checked"{/if} autocomplete="off" /><span>{t}远程链接{/t}</span>
						<input type="radio" name="brand_logo_type" value='1'{if $brand.type} checked="checked"{/if} autocomplete="off" /><span>{t}本地上传{/t}</span>
					</div>
					<div class="controls cl_both brand_logo_type" id="show_src">
						<input class="w350" type='text' name='url_logo' size="42" value="{if !$brand.type}{$brand.brandLogo}{/if}"/>
						<span class="help-block">{t}在指定远程LOGO图片时, LOGO图片的URL网址必须为http:// 或 https://开头的正确URL格式!{/t}</span>
					</div>
					<div class="controls cl_both brand_logo_type" id="show_local" style="display:none;">
						<div class="fileupload {if $brand.url && $brand.type}fileupload-exists{else}fileupload-new{/if}" data-provides="fileupload">	
							<div class="fileupload-preview fileupload-exists thumbnail" style="width: 50px; height: 50px; line-height: 50px;">
								{if $brand.url && $brand.type}
								<img src="{$brand.url}" alt="图片预览" />
								{/if}
							</div>
							<span class="btn btn-file">
								<span  class="fileupload-new">浏览</span>
								<span  class="fileupload-exists">修改</span>
								<input type='file' name='brand_img' size="35"/>
							</span>
							<a class="btn fileupload-exists" data-dismiss="fileupload" href="javascrpt:;">删除</a>
						</div>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{$lang.brand_desc}：</label>
					<div class="controls">
						<textarea class="w350" name="brand_desc" cols="60" rows="5" >{$brand.brand_desc}</textarea>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{$lang.sort_order}：</label>
					<div class="controls">
						<input class="w350" type="text" name="sort_order" maxlength="40" size="15" value="{$brand.sort_order}" />
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{$lang.is_show}：</label>
					<div class="controls chk_radio">
						<input class="uni_style" type="radio" name="is_show" value="1" style="opacity: 0;" {if $brand.is_show eq 1}checked="checked"{/if}  /><span>{$lang.yes}</span>
						<input class="uni_style" type="radio" name="is_show" value="0" style="opacity: 0;" {if $brand.is_show eq 0}checked="checked"{/if}  /><span>{$lang.no}</span>
						<span class="help-block">{$lang.visibility_notes}</span>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
						<input type="hidden" id="type" value="{$brand.type}" />	 
						<input type="hidden" name="old_brandname" value="{$brand.brand_name}" />
						<input type="hidden" name="id" value="{$brand.brand_id}" />
						<input type="hidden" name="old_brandlogo" value="{$brand.brand_logo}">
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
<!-- {/block} -->