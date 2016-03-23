<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax" id="sticky_a"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>

<!-- 商品编辑属性 -->
<div class="row-fluid">
	<div class="span12">
		<table class="table table-striped dataTable table-hide-edit">
			<thead>
				<tr>
					<th class="w200">{$lang.goods_type_name}</th>					
					<th>{t}商家名称{/t}</th>
					<th>{$lang.attr_groups}</th>
					<th class="w80">{$lang.attribute_number}</th>
					<th class="w80">{$lang.goods_type_status}</th>
				</tr>
			</thead>
			<tbody>
				<!-- {foreach from=$goods_type_arr.type item=goods_type} -->
				<tr>
					<td class="hide-edit-area">
						<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin_goods_type/edit_type_name')}" data-name="edit_type_name" data-pk="{$goods_type.cat_id}" data-title="{t}请输入类型名称{/t}"><!-- {$goods_type.cat_name} --></span>
						<div class="edit-list">
							<a class="data-pjax" href='{url path="goods/admin_attribute/init" args="cat_id={$goods_type.cat_id}"}' title="{t}查看属性组{/t}">{t}查看类型属性{/t}</a>&nbsp;|&nbsp;
							<a class="data-pjax" href='{url path="goods/admin_goods_type/edit" args="cat_id={$goods_type.cat_id}"}' title="{t}编辑{/t}">{t}编辑{/t}</a>&nbsp;|&nbsp;
							<a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg="{t}您确定要删除类型[{$goods_type.cat_name}]吗？{/t}" href='{url path="goods/admin_goods_type/remove" args="id={$goods_type.cat_id}"}' title="{t}删除{/t}">{t}删除{/t}</a>
						</div>
					</td>
					<td>
						<!-- {if $goods_type.shop_name} -->
						<font style="color:#F00;">{$goods_type.shop_name}</font>
						<!-- {else} -->
						<font style="color:#0e92d0;">{t}自营{/t}</font>
						<!-- {/if} -->
					</td>
					<td>{$goods_type.attr_group}</td>
					<td>{$goods_type.attr_count}</td>
					<td><i class="{if $goods_type.enabled}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" title="{t}点击改变状态{/t}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/admin_goods_type/toggle_enabled')}" data-id="{$goods_type.cat_id}"></i></td>
				</tr>
				<!-- {foreachelse} --><tr><td class="no-records" colspan="5">{$lang.no_records}</td></tr><!-- {/foreach} -->
			</tbody>
		</table>
		<!-- {$goods_type_arr.page} -->
	</div>
</div>
<!-- {/block} -->
