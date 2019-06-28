<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia-merchant.dwt.php"} -->

<!-- {block name="footer"} --> 
<script type="text/javascript">
	ecjia.merchant.product.init();
</script> 
<!-- {/block} --> 

<!-- {block name="home-content"} -->

<div class="page-header">
	<div class="pull-left">
		<h2> 
			<!-- {if $ur_here}{$ur_here}{/if} --> 
		</h2>	
	</div>
	<div class="pull-right">
		{if $action_link} 
		<a href="{$action_link.href}" class="btn btn-primary data-pjax" id="sticky_a">
		<i class="fa fa-reply"></i> {$action_link.text}</a> 
		{/if}
	</div>
	<div class="clearfix"></div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			{if !$step}
           	<div class="panel-body panel-body-small">
				<ul class="nav nav-tabs">
					<!-- {foreach from=$tags item=tag} -->
					<li {if $tag.active} class="active"{/if}><a {if $tag.active} href="javascript:;"{else}{if $tag.pjax} class="data-pjax"{/if} href='{$tag.href}'{/if}><!-- {$tag.name} --></a></li>
					<!-- {/foreach} -->
				</ul>
			</div>
			{/if}
			
			<div class="panel-body">
				<section>
					<table class="table table-striped table-hover table-hide-edit ecjiaf-tlf">
                      <thead>
                          <tr>
                              <th>货品（SKU）</th>
                              <th class="product_sn">{t domain='goods'}商品货号{/t}</th>
                              <th>{t domain='goods'}条形码{/t}</th>
                              <th>{t domain='goods'}价格{/t}</th>
                              <th>{t domain='goods'}库存{/t}</th>
                              <th class="w150">{t domain='goods'}操作{/t}</th>
                          </tr>
                      </thead>
                      
                      <tbody>
                          <!-- {foreach from=$product_list item=product} -->
                          <input type="hidden" name="product_id[]" value="{$product.product_id}" />
                          <tr>
                            <td style="vertical-align: inherit;">
                              <!-- {foreach from=$product.goods_attr item=goods_attr} -->
                              {$goods_attr} {if $goods_attr@last}{else}/{/if}
                              <!-- {/foreach} -->
                              </td>
                              <td>{$product.product_sn}</td>
                              <td>{$product.product_bar_code}</td>
                              <td>{$product.product_shop_price}</td>
                              <td>{$product.product_number}</td>
                              <td style="vertical-align: inherit;">
                                <a class="data-pjax" href='{url path="goods/merchant/product_edit" args="id={$product.product_id}&goods_id={$goods_id}"}' >{t domain='goods'}编辑{/t}</a>&nbsp;|&nbsp;
								<a class="add_cart cursor_pointer" data-href='{$supplier_product_addcart}' action-type="{$action_type}" product-id="{$product.product_id}" goods-id="{$goods_id}">{t domain="goods"}加入采购车{/t}</a>
                              </td>
                          </tr>
                         <!-- {foreachelse}-->
						<tr>
							<td class="no-records" colspan="6">{t domain='goods'}没有找到任何记录{/t}</td>
						</tr>
						<!-- {/foreach} -->
                      </tbody>
                    </table>
				</section>
				<!-- {$goods_list.page} -->
			</div>
		</div>
	</div>
</div>
<!-- {/block} -->