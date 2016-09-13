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
					<i class="fontello-icon-cog"></i>{lang key='goods::goods.batch_handle'}
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=restore&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_restore_confirm'}"  data-noSelectMsg="{lang key='goods::goods.select_goods_msg'}" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-export"></i>{lang key='system::system.restore'}</a></li>
					<li><a data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=drop&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_drop_confirm'}"  data-noSelectMsg="{lang key='goods::goods.select_goods_msg'}" data-name="checkboxes" href="javascript:;"><i class="fontello-icon-trash"></i>{lang key='system::system.remove'}</a></li>
				</ul>
			</div>
		</form>
		<div class="choose_list f_r" >
			<form action="{RC_Uri::url('goods/admin/trash')}"  method="post" class="f_r" name="searchForm">
				<!-- <span>{lang key='goods::goods.keyword'} ：</span> TODO-->
				<input type="text" name="keyword" value="{$smarty.get.keyword}" placeholder="{lang key='goods::goods.enter_goods_keywords'}"/>
				<button class="btn" type="submit">{lang key='system::system.button_search'}</button>
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
								<th class="w50">{lang key='system::system.record_id'}</th>
								<th>{lang key='goods::goods.goods_name'}</th>
								<th class="w100">{lang key='goods::goods.goods_sn'}</th>
								<th class="w100">{lang key='goods::goods.shop_price'}</th>
								<th class="w100">{lang key='system::system.handler'}</th>
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
									<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg='{lang key="goods::goods.restore_goods_confirm"}' href='{RC_Uri::url("goods/admin/restore_goods", "id={$goods.goods_id}")}' title="{lang key='goods::goods.restore'}"><i class="fontello-icon-export"></i></a>
									<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg='{lang key="goods::goods.drop_goods_confirm"}' href='{RC_Uri::url("goods/admin/drop_goods", "id={$goods.goods_id}")}' title="{lang key='system::system.drop'}"><i class="fontello-icon-trash"></i></a>
								</td>
							</tr>
							<!-- {foreachelse} -->
							<tr><td class="no-records" colspan="10">{lang key='system::system.no_records'}</td></tr>
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