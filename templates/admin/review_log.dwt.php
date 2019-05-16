<?php defined('IN_ECJIA') or exit('No permission resources.');?>

<div class="modal-header">
	<button class="close" data-dismiss="modal">×</button>
	<h3 class="modal-title">{t domain="express"}查看审核{/t}</h3>
</div> 

<div class="modal-body" >
	<div class="box_top">
	审核状态：{if $goods_info.review_status eq 2}审核未通过{elseif $goods_info.review_status eq 3}审核已通过{/if}
	</div>
	
			
	<!-- {foreach from=$list_log item=val} --> 
	<div class="control-group control-group-small">
	{$val.status}
	</div>
	<!-- {/foreach} -->	
		
</div>
