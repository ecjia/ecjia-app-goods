<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.preview.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}{if $code}&extension_code={$code}{/if}" id="sticky_a" ><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
		{if $action_linkedit}
		<a class="btn plus_or_reply data-pjax" href="{$action_linkedit.href}{if $code}&extension_code={$code}{/if}" id="sticky_a" ><i class="fontello-icon-edit"></i>{$action_linkedit.text}</a>
		{/if}
	</h3>	
</div>

<div class="row-fluid">
	<div class="choose_list" >
		<strong class="f_l">{t}商品货号：{/t}{$goods.goods_sn}</strong>
		<form class="f_r" method="post" action="{url path='goods/admin/preview'}" name="searchForm" data-id="{$goods.goods_id}">
			<input type="text" name="keywords" value="{$goods.goods_id}" placeholder="请输商品ID或货号"/>
			<button class="btn" type="submit">{t}搜索{/t}</button>
		</form>
	</div>
</div>

<div class="row-fluid goods_preview">
	<div class="span12 ">
		<div class="row-fluid showview">
			<div class="span4 left">
				<img alt="{$goods.goods_name}" class="span10 thumbnail" src="{$goods.goods_img}">
			</div>
			<div class="span8">
				<h2 class="m_b10"{if $goods.goods_name_style} style="color:{$goods.goods_name_style};"{/if}>{$goods.goods_name} </h2>
				<h2 class="m_b10 price">￥{$goods.shop_price}</h2>
				<!-- <p>{t}本店售价：{/t}￥{$goods.shop_price}</p> -->
				<!-- <p>{t}市场售价：{/t}￥{$goods.market_price}</p> -->
				<!-- <p>{t}商品货号：{/t}{$goods.goods_sn}</p> -->

				<p>{t}精品：{/t}{if $goods.is_best}<i class="fontello-icon-ok"></i>{else}<i class="fontello-icon-cancel"></i>{/if}</p>
				<p>{t}新品：{/t}{if $goods.is_new}<i class="fontello-icon-ok"></i>{else}<i class="fontello-icon-cancel"></i>{/if}</p>
				<p>{t}热卖：{/t}{if $goods.is_hot}<i class="fontello-icon-ok"></i>{else}<i class="fontello-icon-cancel"></i>{/if}</p>

				<!-- <p>{t}商品分类：{/t}{$cat_name}</p>
				<p>{t}商品品牌：{/t}{$brand_name}</p> -->
				<!-- <p>{t}简单描述：{/t}{$goods.goods_brief}</p> -->
			</div>
		</div>

		<div class="foldable-list move-mod-group" id="goods_info_sort_submit">
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_submit">
						<strong>{t}产品信息{/t}</strong>
					</a>
				</div>
				<div class="accordion-body in collapse" id="goods_info_area_submit">
					<table class="table table-oddtd m_b0">
						<tbody class="first-td-no-leftbd">
							<tr>
								<td><div align="right"><strong>{t}添加时间：{/t}</strong></div></td>
								<td>{RC_Time::local_date('Y-m-d h:i:s', {$goods.add_time})}</td>
								<td><div align="right"><strong>{t}最后更新：{/t}</strong></div></td>
								<td>{RC_Time::local_date('Y-m-d h:i:s', {$goods.last_update})}</td>
							</tr>
							<tr>
								<td><div align="right"><strong>{t}库存数量：{/t}</strong></div></td>
								<td>{$goods.goods_number}</td>
								<td><div align="right"><strong>{t}警告数量：{/t}</strong></div></td>
								<td>{$goods.warn_number}</td>
							</tr>
							<tr>
								<td><div align="right"><strong>{t}本店售价：{/t}</strong></div></td>
								<td>{$goods.shop_price}</td>
								<td><div align="right"><strong>{t}市场售价：{/t}</strong></div></td>
								<td>{$goods.market_price}</td>
							</tr>
							<tr>
								<td><div align="right"><strong>{t}商品重量：{/t}</strong></div></td>
								<td>{$goods.goods_weight}</td>
								<td><div align="right"><strong>{t}商品关键词：{/t}</strong></div></td>
								<td>{$goods.keywords}</td>
							</tr>
							<tr>
								<td><div align="right"><strong>{t}简单描述：{/t}</strong></div></td>
								<td colspan="3">{$goods.goods_brief}</td>
							</tr>
							<tr>
								<td><div align="right"><strong>{t}商品分类：{/t}</strong></div></td>
								<td>{$cat_name}</td>
								<td><div align="right"><strong>{t}商品品牌：{/t}</strong></div></td>
								<td>{$brand_name}</td>
							</tr>
							<tr>
								<td><div align="right"><strong>{t}商家备注：{/t}</strong></div></td>
								<td colspan="3">{$goods.seller_note}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div>
			{$goods.goods_desc}
		</div>
	</div>
</div>
<!-- {/block} -->













