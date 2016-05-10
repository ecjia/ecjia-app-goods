<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.replenish.list();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax"><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>
<div class="row-fluid batch" >
	<div class="btn-group f_l m_r5">
		<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
			<i class="fontello-icon-cog"></i>{t}批量操作{/t}
			<span class="caret"></span>
		</a>
		<ul class="dropdown-menu">
			<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}" data-msg="删除操作不可恢复。您确定要这么做吗？" data-noSelectMsg="请先选中要操作的信息" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-trash"></i>{t}批量删除{/t}</a></li>
			<li><a class="batch_edit" href="javascript:;"><i class="fontello-icon-edit"></i>{t}批量编辑{/t}</a></li>
		</ul>
	</div>
	<div class="choose_list f_r" >
		<form class="f_r" action='{RC_Uri::url("goods/admin_virtual_card/card", "goods_id={$goods_id}")}' method="post" name="searchForm">
			<input type="text" name="keyword" placeholder="请输入卡片序号或订单号 " value="{$smarty.get.keyword}" />
			<button class="btn" type="submit">{$lang.button_search}</button>
		</form>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<form method="post" action="{$form_action}" name="listForm" data-batch="{$batch_action}" >
			<div class="row-fluid">
				<table class="table table-striped smpl_tbl">
					<thead>
						<tr>
							<th class="table_checkbox">
								<input type="checkbox" data-toggle="selectall" data-children=".checkbox" />
							</th>
							<th>{$lang.record_id}</th>
							<th>{$lang.lab_card_sn}</th>
							<th>{$lang.lab_card_password}</th>
							<th>{$lang.lab_end_date}</th>
							<th>{$lang.lab_is_saled}</th>
							<th>{$lang.lab_order_sn}</th>
							<th>{$lang.handler}</th>
						</tr>
					</thead>
					<tbody>
						<!-- {foreach from=$card_list.item item=card} -->
						<tr>
							<td>
								<input class="checkbox"  type="checkbox" name="checkboxes[]" value="{$card.card_id}" >
							</td>
							<td>{$card.card_id}</td>
							<td><span>{$card.card_sn}</span></td>
							<td><span>{$card.card_password}</span></td>
							<td align="right">
								<span>{$card.end_date}</span>
							</td>
							<td align="center">
								<i class="{if $card.is_saled}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" title="点击改变状态" data-trigger="toggleState" data-url="{RC_Uri::url('goods/admin_virtual_card/toggle_sold')}" data-id="{$card.card_id}"></i>
							</td>
							<td>{$card.order_sn}</td>
							<td align="center">
								<a class="data-pjax no-underline" href="{RC_Uri::url('goods/admin_virtual_card/edit_replenish',"card_id={$card.card_id}&goods_id={$goods_id}")}" title="{$lang.edit}" ><i class="fontello-icon-edit"></i></a>
								<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg="{t}您确定要删除虚拟卡[{$card.card_sn}]吗？{/t}" href="{RC_Uri::url('goods/admin_virtual_card/remove_card', "id={$card.card_id}")}"><i class="fontello-icon-trash"></i></a>
							</td>
						</tr>
						<!-- {foreachelse} -->
						<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
						<!-- {/foreach} -->
					</tbody>
				</table>
				<!-- {$card_list.page} --> 
			</div>
		</form>
	</div>
</div>
<!-- {/block} -->