<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.batch_card.confirm();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<!-- 面包导航 -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax"> <i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="list-div">
	<form action="{$form_action}" method="post" name="batch_confirm">
		<table class="table table-striped dataTable" >
			<thead>
				<tr>
					<th><input type="checkbox"/>{$lang.lab_card_id}</th>
					<th>{$lang.lab_card_sn}</th>
					<th>{$lang.lab_card_password}</th>
					<th>{$lang.lab_end_date}</th>
				</tr>
			</thead>
			<tbody>
				<!-- {foreach from=$list key=key item=card} -->
				<tr>
					<td><input type="checkbox" name="checked[]" value="{$key}" checked="checked"/> {$key}</td>
					<td><input type="text" name="card_sn[{$key}]" value="{$card.card_sn}" size="15" /></td>
					<td><input type="text" name="card_password[{$key}]" value="{$card.card_password}" size="15" /></td>
					<td><input type="text" name="end_date[{$key}]" value="{$card.end_date}" size="15" /></td>
				</tr>
				<!-- {/foreach} -->
			</tbody>
		</table>
		<div class="button-div ecjiaf-tac m_b10">
			<input type="hidden" name="goods_id" value="{$smarty.request.goods_id}" />
			<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
		</div>
	</form>
</div>
<!-- {/block} -->