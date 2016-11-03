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

<ul class="nav nav-pills">
	<li class="{if $filter.type eq ''}active{/if}">
		<a class="data-pjax" href='{url path="goods/admin/trash" args="{if $filter.merchant_keywords}&merchant_keywords={$filter.merchant_keywords}{/if}{if $filter.keywords}&keywords={$filter.keywords}{/if}"}'>{lang key='goods::goods.intro_type'}
			<span class="badge badge-info">{$filter.count_goods_num}</span>
		</a>
	</li>
	<li class="{if $filter.type eq 'merchant'}active{/if}">
		<a class="data-pjax" href='{url path="goods/admin/trash" args="type=merchant{if $filter.merchant_keywords}&merchant_keywords={$filter.merchant_keywords}{/if}{if $filter.keywords}&keywords={$filter.keywords}{/if}"}'>{lang key='goods::goods.merchant'}
			<span class="badge badge-info">{$filter.merchant}</span>
		</a>
	</li>
</ul>

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
		<form action="{RC_Uri::url('goods/admin/trash')}{if $filter.type}&type={$filter.type}{/if}"  method="post" class="f_r" name="searchForm">
			<select class="w220" name="cat_id">
			<option value="0">{lang key='goods::goods.goods_cat'}</option>
			<!-- {$cat_list} -->
		    </select>
			<!-- <span>{lang key='goods::goods.keyword'} ：</span> TODO-->
			<input type="text" class="w180" name="merchant_keywords" value="{$smarty.get.merchant_keywords}" placeholder="{lang key='goods::goods.enter_merchant_keywords'}"/>
			<input type="text" name="keywords" value="{$smarty.get.keywords}" placeholder="{lang key='goods::goods.enter_goods_keywords'}"/>
			<button class="btn" type="submit">{lang key='system::system.button_search'}</button>
		</form>
	</div>
</div>

<div class="row-fluid list-page">
	<div class="span12">
		<div class="row-fluid">
			<table class="table table-striped smpl_tbl">
				<thead>
					<tr>
						<th class="table_checkbox">
							<input type="checkbox" data-toggle="selectall" data-children=".checkbox"/>	
						</th>
						<th class="w50">{lang key='system::system.record_id'}</th>
						<th class="w110">{lang key='goods::goods.business_name'}</th>
						<th>{lang key='goods::goods.goods_name'}</th>
						<th class="w100">{lang key='goods::goods.goods_sn'}</th>
						<th class="w100">{lang key='goods::goods.shop_price'}</th>
						<th class="w70">{lang key='system::system.handler'}</th>
					</tr>
				</thead>
				<tbody>
			<!-- {foreach from=$goods_list.goods item=goods} -->
					<tr>
						<td>
							<input class="checkbox" value="{$goods.goods_id}" name="checkboxes[]" type="checkbox"/>
						</td>
						<td>{$goods.goods_id}</td>
						<td class="ecjiafc-red">{$goods.merchants_name}</td>
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
		</div>
	</div>
</div>
<!-- {/block} -->