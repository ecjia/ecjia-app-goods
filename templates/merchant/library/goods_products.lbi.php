<div class="goods-products">
	商品属性
	<hr>
</div>
<table class="table table-striped table-hide-edit">
	<thead>
        <tr>
        	<th class="w200">{t domain="goods"}货品SKU{/t}</th>
        	<th class="w100">{t domain="goods"}商品货号{/t}</th>
        	<th class="w100">{t domain="goods"}条形码{/t}</th>
        	<th class="w80">{t domain="goods"}价格{/t}</th>
        	<th class="w50">{t domain="goods"}库存{/t}</th>
        	<th class="w110">{t domain="goods"}操作{/t}</th>
        </tr>
	</thead>
	<tbody>
	<!-- {foreach from=$product_list item=list} -->
    	<tr>
    		<td class="hide-edit-area">
    			{if $list.product_thumb}
    				<img class="ecjiaf-fl" src="{$list.product_thumb}" width="60" height="60">
				{/if}
				 <div class="product-info" style="margin-left:65px;">
				    <div class="goods-name-style">{$list.product_name}</div>
				           【{$list.product_attr_value}】
				    {if $goods.is_promote eq 1}<span class="goods-promote">{t domain='goods'}促{/t}</span>{/if}
				 </div>
    		</td>
    		<td>
    			{$list.product_sn}
    		</td>
    		<td>
    			{$list.product_bar_code}				
    		</td>
    		<td>{$list.product_shop_price}</td>
    		<td>{$list.product_number}</td>
    		<td>
    			<a class="data-pjax" href='{url path="goods/merchant/edit" args="product_id={$list.product_id}&goods_id={$list.goods_id}"}'>{t domain='goods'}编辑{/t}</a>&nbsp;|&nbsp;
    			<a class="data-pjax" href='{url path="goods/merchant/product_preview" args="product_id={$list.product_id}&goods_id={$list.goods_id}"}'>{t domain='goods'}预览{/t}</a>	&nbsp;|&nbsp;		
				<a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg="{t domain='goods'}您确定要把该货品放入回收站吗？{/t}" href='{url path="goods/merchant/remove" args="product_id={$list.product_id}&id={$list.goods_id}"}'>{t domain='goods'}删除{/t}</a>
			</td>
    	</tr>
    <!-- {/foreach} -->
 </tbody>
</table>