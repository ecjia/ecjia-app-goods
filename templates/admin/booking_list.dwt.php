<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
ecjia.admin.goods_booking.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
	</h3>
</div>
<div class="row-fluid">
	<div class="choose_list span12">
		<form action="{$form_action}" class="f_r" name="searchForm" method="post">
			<input type="text" name="keywords" size="15" value="{$filter.keywords}" placeholder="请输入缺货商品名"/>
			<button class="btn data-pjax" type="submit">{$lang.button_search}</button>   
		</form>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<!--  缺货商品名  -->
		<table class="table table-striped" id="smpl_tbl">
			<thead>
				<tr>
					<th class="w50">{$lang.record_id}</th>
					<th class="w150">{t}商家名称{/t}</th>
					<th class="w150">{$lang.link_man}</th>
					<th class="w300">{$lang.goods_name}</th>
					<th class="w50">{$lang.number}</th>
					<th class="w150">{$lang.booking_time}</th>
					<th class="w100">{$lang.is_dispose}</th>
					<th class="w150">{$lang.handler}</th>
				</tr>
			</thead>
			<tbody>
				<!-- {foreach from=$booking_list.item item=booking} -->
				<tr>
					<td>{$booking.rec_id}</td>
					<td>
						<!-- {if $booking.shop_name} -->
						<font style="color:#F00;">{$booking.shop_name}</font>
						<!-- {else} -->
						<font style="color:#0e92d0;">{t}自营{/t}</font>
						<!-- {/if} -->
					</td>					
					<td>{$booking.link_man|escape}</td>
					<td><a href='{url path="goods/admin/preview" args="id={$booking.goods_id}"}' target="_blank" title="查看商品详情">{$booking.goods_name}</a></td>
					<td align="right">{$booking.goods_number}</td>
					<td align="right">{$booking.booking_time}</td>
					<td align="center">
						<i class="{if $booking.is_dispose}fontello-icon-ok{else}fontello-icon-cancel{/if}"></i>
					</td>
					<td>
						<a class="data-pjax no-underline" href='{url path="goods/admin_goods_booking/detail" args="id={$booking.rec_id}"}'><i class="fontello-icon-doc-text" title="{$lang.detail}"></i></a>
						<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg='{t name="{$booking.goods_name}"}您确定要删除商品名为[ %1 ]的缺货登记信息吗？{/t}' href='{url path="goods/admin_goods_booking/remove" args="id={$booking.rec_id}"}' title="{t}移除{/t}"><i class="fontello-icon-trash"></i></a>
					</td>
				</tr>
				<!-- {foreachelse} -->
				   <tr><td class="no-records" colspan="10">{t}没有找到任何记录{/t}</td></tr>
				<!-- {/foreach} -->
			</tbody>
		</table>
		<!--   {$booking_list.page} -->
	</div>
</div>
<!-- {/block} -->