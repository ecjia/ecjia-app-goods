<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_trash.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div class="admin_goods goods_admin_trash">
	<div>
		<h3 class="heading">
			<!-- {if $ur_here}{$ur_here}{/if} -->
			{if $action_link}
			<a href="{$action_link.href}" class="btn plus_or_reply data-pjax" ><i class="fontello-icon-reply"></i>{$action_link.text}</a>
			{/if}
		</h3>
	</div>
	<div class="row-fluid batch" >
		<form method="post" action="{$search_action}" name="actionForm">
			<div class="btn-group f_l m_r5">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="fontello-icon-cog"></i>{t}批量操作{/t}
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=restore&page={$smarty.get.page}" data-msg="您确定要把商品从回收站还原吗？"  data-noSelectMsg="请选择需要操作的信息" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-export"></i>{$lang.restore}</a></li>
					<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=drop&page={$smarty.get.page}" data-msg="删除操作无法恢复！您确定要这么做吗？"  data-noSelectMsg="请选择需要操作的信息" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-trash"></i>{$lang.remove}</a></li>
				</ul>
			</div>
		</form>
		<div class="choose_list f_r" >
			<form action="{RC_Uri::url('goods/admin/trash')}"  method="post" class="f_r" name="searchForm">
				<!-- <span>{$lang.keyword} ：</span> TODO-->
				<input type="text" name="keyword" value="{$smarty.get.keyword}" placeholder="请输入关键字"/>
				<button class="btn" type="submit">{$lang.button_search}</button>
			</form>
		</div>
	</div>
	<div class="row-fluid list-page">
		<div class="span12">
			<form method="post" action="{$form_action}" name="listForm" >
				<div class="row-fluid">
					<table class="table table-striped smpl_tbl">
						<thead>
							<tr>
								<th class="table_checkbox">
									<input type="checkbox" data-toggle="selectall" data-children=".checkbox"/>	
								</th>
								<th class="w50">{$lang.record_id}</th>
								<th>{$lang.goods_name}</th>
								<th class="w100">{$lang.goods_sn}</th>
								<th class="w100">{$lang.shop_price}</th>
								<th class="w100">{$lang.handler}</th>
							</tr>
						</thead>
						<tbody>
							<!-- {foreach from=$goods_list.goods item=goods} -->
							<tr>
								<td>
									<input class="checkbox" value="{$goods.goods_id}" name="checkboxes[]" type="checkbox"/>
								</td>

								<td>{$goods.goods_id}</td>
								<td>{$goods.goods_name|escape:html}</td>
								<td>{$goods.goods_sn}</td>
								<td align="right">{$goods.shop_price}</td>
								<td align="center">
									<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg="{t}您确定要还原商品[{$goods.goods_name}]吗？{/t}" href="{RC_Uri::url('goods/admin/restore_goods',"id={$goods.goods_id}")}" title="{t}还原{/t}"><i class="fontello-icon-export"></i></a>
									<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg="{t}您确定要删除商品[{$goods.goods_name}]吗？{/t}" href="{RC_Uri::url('goods/admin/drop_goods',"id={$goods.goods_id}")}" title="{t}移除{/t}"><i class="fontello-icon-trash"></i></a>
								</td>
							</tr>
							<!-- {foreachelse} -->
							<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
							<!-- {/foreach} -->
						</tbody>
					</table>
					<!-- {$goods_list.page} -->
				</div>
			</form>
		</div>
	</div>
</div>
<!-- {/block} -->