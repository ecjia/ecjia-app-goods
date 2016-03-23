<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_info.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} --> 
<div>
	<h3 class="heading"> 
		<!-- {if $ur_here}{$ur_here}{/if} --> 
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$action_link.href}{if $smarty.get.extension_code eq 'virtual_card'}&extension_code=virtual_card{/if}"><i class="fontello-icon-reply"></i>{$action_link.text}</a> 
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<div class="tabbable">
			<ul class="nav nav-tabs">
				<!-- {foreach from=$tags item=tag} -->
				<li{if $tag.active} class="active"{/if}><a{if $tag.active} href="javascript:;"{else}{if $tag.pjax} class="data-pjax"{/if} href='{$tag.href}'{/if}><!-- {$tag.name} --></a></li>
				<!-- {/foreach} -->
			</ul>
			<form class="form-horizontal" enctype="multipart/form-data" action="{$form_action}" method="post" name="theForm">
				<div class="row-fluid formSep">
					<div class="span12">
						{ecjia:editor content=$goods.goods_desc textarea_name='goods_desc' is_teeny=0}
					</div>
				</div>
				
				<fieldset class="t_c">
					<button class="btn btn-gebo" type="submit">{t}保存{/t}</button>
					<input type="hidden" name="goods_id" value="{$goods_id}" />
					{if $code neq ''}
					<input type="hidden" name="extension_code" value="{$code}" />
					{/if}
					<input type="hidden" id="type" value="{$link.type}" />
				</fieldset>
			</form>
		</div>
	</div>
</div>
<!-- {/block} -->