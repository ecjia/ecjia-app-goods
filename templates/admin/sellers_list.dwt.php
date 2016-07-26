<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->
<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.seller_list.init();
</script>
<!-- {/block} -->
<!-- {block name="main_content"} -->
	<div>
		<h3 class="heading">
			<!-- {if $ur_here}{$ur_here}{/if} -->
			{if $action_link}
				<a href="{$action_link.href}" class="btn plus_or_reply data-pjax" id="sticky_a">
					<i class="fontello-icon-plus"></i>{$action_link.text}
				</a>
			{/if}
		</h3>
	</div>
	<div class="row-fluid batch" >
		<div class="choose_list">
			<form class="f_r form-inline" action="{$search_action}" name="searchForm">
				<input type="text" name="keywords" placeholder="{t}请输入登录名称或店铺名称关键字{/t}" value="{$users_list.filter.keywords}"/>
				<input class="btn" type="submit" value="搜索"/>
		  	</form>
	  	</div>
	</div>
	<div class="row-fluid list-page">
		<table class="table table-striped table-hide-edit">
			<thead>
				<tr>
				    <th class="w200">{t}登录名称{/t}</th>
				    <th class="w350">{t}店铺名称{/t}</th>
				    <th class="w100">{t}店铺类型{/t}</th>
				    <th class="w100">{t}审核状态{/t}</th>
				    <th class="w50">{t}操作{/t}</th>
				 </tr>
			</thead>
			<tbody>
			<!-- {foreach from=$users_list.users_list item=users} -->
			<tr>
				<td class="hide-edit-area">
					<span class="ecjiaf-pre">{$users.hopeLoginName}{if $users.user_name}（{$users.user_name}）{/if}</span>
				</td>
	    		<td>{$users.shop_name}</td>
				<td>
					{if $users.shoprz_type eq 1}
					{t}旗舰店{/t}
					{else if $users.shoprz_type eq 2}
					{t}专卖店{/t}
					{else if $users.shoprz_type eq 3}
					{t}专营店{/t}
					{/if}
				</td>
	    		<td>
	    			{if $users.steps_audit eq 1}
	    				{if $users.merchants_audit eq 0}
	    					{t}未审核{/t}
	        			{elseif $users.merchants_audit eq 1}
	      					{t}审核已通过{/t}
	        			{elseif $users.merchants_audit eq 2}
	       					{t}审核未通过{/t}
	        			{/if}
	    			{else}
	    				<font style="color:#F90">{t}尚未提交信息{/t}</font>
	    			{/if}    
	    		</td>
	    		<td>
	    			<a class="data-pjax" href='{url path="goods/admin_category_store/seller_goods_cat_list" args="seller_id={$users.id}"}' title="{t}查看店铺商品分类{/t}"><i class="fontello-icon-doc-text"></i></a>
	    		</td>
			</tr>
			<!-- {foreachelse} -->
			<tr>
				<td class="no-records" colspan="6">{t}没有找到任何记录{/t}</td>
			</tr>
			<!-- {/foreach} -->
			</tbody>
	  	</table>
	</div>
	<!-- {$users_list.page} -->
<!-- {/block} -->