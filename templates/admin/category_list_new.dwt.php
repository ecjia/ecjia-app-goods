<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
	<!-- {if $ur_here}{$ur_here}{/if} -->
	<!-- {if $exchange_link} -->
	<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$exchange_link.href}"><i class="fontello-icon-exchange"></i>{$exchange_link.text}</a>
	<!-- {/if} -->
	
	<!-- {if $back_link} -->
	<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$back_link.href}"><i class="fontello-icon-reply"></i>{$back_link.text}</a>
	<!-- {/if} -->
	</h3>
</div>

<div class="row-fluid">
	<div class="span12">
		<form method="post" action="{$form_action}" name="listForm">
		<div class="list-div list media_captcha wookmark warehouse" id="listDiv">
		  	<ul>
			<!-- {foreach from=$cat_list item=val} -->
				<li class="thumbnail">
					<div class="bd">
						<div class="model-title ware_name"><span style="font-size:16px;"><a target="__blank" href='{url path="goods/admin/init" args="cat_id={$val.cat_id}"}'>{$val.cat_name}</a></span></div>
					</div>

					<div class="input">
						<a class="data-pjax no-underline" title="{t}进入{/t}" href="{url path='goods/admin_category/init' args="cat_id={$val.cat_id}"}"><i class="fontello-icon-login"></i></a>
						<a class="no-underline data-pjax" title="{t}编辑{/t}" href="{url path='goods/admin_category/edit' args="cat_id={$val.cat_id}"}"><i class="fontello-icon-edit"></i></a>
						<i class="{if $val.is_show eq '1'}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" data-trigger="toggleState" data-url="{url path='goods/admin_category/toggle_is_show'}" data-id="{$val.cat_id}"></i>
						<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg="{t}您确定要删除此分类[{$val.cat_name}]吗？{/t}" href='{url path="goods/admin_category/remove" args="id={$val.cat_id}"}' title="{t}删除{/t}"><i class="fontello-icon-trash ecjiafc-red"></i></a>
					</div>
				</li>
				<!-- {/foreach} -->
				<li class="thumbnail add-ware-house">
					<a class="more data-pjax" href="{$action_link.href}">
						<i class="fontello-icon-plus"></i>
					</a>
				</li>
			</ul>
		  <!-- {$cat_list.page} -->
		</div>
		</form>
	</div>
</div>
<!-- {/block} -->