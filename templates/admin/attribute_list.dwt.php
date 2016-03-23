<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} --> 
<script type="text/javascript">
	ecjia.admin.goods_arrt.init();
</script> 
<!-- {/block} --> 

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$action_link.href}"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
		<!-- {if $action_link2} -->
		<a href="{$action_link2.href}" class="btn plus_or_reply data-pjax" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link2.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid batch">
	<form class="f_l" action="" name="searchForm">
		<div class="btn-group f_l m_r5">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fontello-icon-cog"></i>{t}批量操作{/t}<span class="caret"></span>
			</a>
			<ul class="dropdown-menu batch-move">
				<li><a class="batch-trash-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{RC_Uri::url('goods/admin_attribute/batch')}" data-msg="是否删除选中的商品属性?" data-noSelectMsg="请选中要删除的商品属性" href="javascript:;"> <i class="fontello-icon-trash"></i>{$lang.batchdrop}</a></li>
			</ul>
		</div>
	</form>
	<div class="choose_list f_r" >
		<span>{$lang.by_goods_type}</span>
		<select name="goods_type" data-url="{url path='goods/admin_attribute/init' args='cat_id='}">
			<option value="0">{$lang.all_goods_type}</option>
			<!-- {foreach from=$goods_type_list item=goods_type} -->
			<option value="{$goods_type.cat_id}" {if $goods_type.cat_id eq $smarty.get.cat_id}selected{/if}>{$goods_type.cat_name}</option>
			<!-- {/foreach} -->
		</select>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<form method="post" action="{$form_action}" name="listForm">
			<div class="row-fluid" id="listDiv">
				<table class="table table-striped smpl_tbl">
					<thead>
						<tr>
							<th class="table_checkbox">
								<input type="checkbox" data-toggle="selectall" data-children=".checkbox" autocomplete="off" />
							</th>
							<th>{$lang.attr_name}</th>
							<th width="10%">{$lang.cat_id}</th>
							<th width="15%">{$lang.attr_input_type}</th>
							<th width="35%">{$lang.attr_values}</th>
							<th width="5%">{$lang.sort_order}</th>
							<th>{$lang.handler}</th>
						</tr>
					</thead>
					<tbody>
						<!-- {foreach from=$attr_list.item item=attr} -->
						<tr>
							<td nowrap="true" valign="top">
								<input class="checkbox" value="{$attr.attr_id}" name="checkboxes[]" type="checkbox" autocomplete="off" />
							</td>
							<td class="first-cell" nowrap="true" valign="top">
								<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin_attribute/edit_attr_name')}" data-name="edit_attr_name" data-pk="{$attr.attr_id}" data-title="请输入属性名称"> 
									{$attr.attr_name}
								</span>
							</td>
							<td nowrap="true" valign="top"><span>{$attr.cat_name}</span></td>
							<td nowrap="true" valign="top"><span>{$attr.attr_input_type_desc}</span></td>
							<td valign="top"><span>{$attr.attr_values}</span></td>
							<td align="right" nowrap="true" valign="top"><span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin_attribute/edit_sort_order')}" data-name="edit_sort_order" data-pjax-url='{url path="goods/admin_attribute/init" args="cat_id={$smarty.get.cat_id}"}' data-pk="{$attr.attr_id}" data-title="请输入排序号">{$attr.sort_order}</span></td>
							<td align="center" nowrap="true" valign="top">
								{assign var=edit_url value=RC_Uri::url('goods/admin_attribute/edit',"attr_id={$attr.attr_id}")}
								<a class="data-pjax" href="{$edit_url}" title="{$lang.edit}"><i class="fontello-icon-edit"></i></a>
								<a class="ajaxremove" data-toggle="ajaxremove" data-msg="{t}您确定要删除属性[{$attr.attr_name}]至回收站吗？{/t}" href="{RC_Uri::url('goods/admin_attribute/remove',"id={$attr.attr_id}")}" title="{t}移除{/t}"><i class="fontello-icon-trash"></i></a>
							</td>
						</tr>
						<!-- {foreachelse} -->
						<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
						<!-- {/foreach} -->
					</tbody>
				</table>
			</div>
		</form>
	</div>
</div>
<!-- {$attr_list.page} -->
<!-- {/block} -->
