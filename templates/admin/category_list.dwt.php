<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->
<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$action_link.href}" ><i class="fontello-icon-plus"></i>{$action_link.text}</a>
		<!-- {/if} -->
		<!-- {if $action_link1} -->
		<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$action_link1.href}"><i class="fontello-icon-exchange"></i>{$action_link1.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<!-- start ad position list -->
<div class="row-fluid">
	<table class="table table-striped"  id="list-table">
		<thead>
			<tr>
				<th>{$lang.cat_name}</th>
				<th>{t}商家名称{/t}</th>
				<th class="w100">{$lang.goods_number}</th>
				<th class="w100">{$lang.measure_unit}</th>
				<th class="w100">{$lang.short_grade}</th>
				<th class="w50">{$lang.sort_order}</th>
				<th class="w100">{$lang.is_show}</th>
				<th class="w80">{$lang.handler}</th>
			</tr>
		</thead>
		{$cat_info}
	</table>
</div>
<!-- {/block} -->
