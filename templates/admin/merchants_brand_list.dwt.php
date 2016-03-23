category_info.dwt.php<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_brand.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
	</h3>
</div>
<!-- 品牌搜索 -->
<div class="row-fluid">
	<div class="choose_list f_r">
		<form class="f_r" action="{url path='goods/merchants_brand/init' args='brand_name='}" method="post" name="searchForm">
			<!--<span>{$lang.keyword}</span> TODO --> 
			<input type="text" name="brand_name" value="{$smarty.get.brand_name}" size="15" placeholder="请输入品牌关键字" />
			<button class="btn" type="submit">{$lang.button_search}</button>
		</form>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<form method="post" name="listForm"  action="">
			<!-- start brand list -->
			<table class="table table-striped dataTable" cellpadding="3" cellspacing="1" id="smpl_tbl">
				<thead>
					<tr>
						<th>{$lang.brand_name}</th>
						<th>{t}商家名称{/t}</th>
						<th class="w100">{$lang.brand_logo}</th>
						<th>{$lang.site_url}</th>
						<th>{$lang.brand_desc}</th>
						<th class="w50">{$lang.sort_order}</th>
						<th class="w100">{$lang.is_show}</th>
						<th class="w100">{$lang.handler}</th>
					</tr>
				</thead>
				<tbody>
					<!-- {foreach from=$brand_list item=brand} -->
					<tr>
						<td class="first-cell">
							<span class="cursor_pointer" data-trigger="editable" data-url="{url path='goods/merchants_brand/edit_brand_name'}" data-name="edit_brand_name" data-pk="{$brand.bid}" data-title="请输入品牌名称">
								{$brand.brandName|escape:html}
							</span>
						</td>
						<td><font style="color:#F00;">{$brand.shop_name}</font></td>
						<td>{$brand.brandLogo}</td>
						<td>{$brand.site_url}</td>
						<td align="left">{$brand.brand_desc|truncate:36}</td>
						<td align="right">
							<span  class="cursor_pointer" data-trigger="editable" data-url="{url path='goods/merchants_brand/edit_sort_order'}" data-name="edit_sort_order" data-pk="{$brand.bid}" data-title="请输入排序序号">
								{$brand.sort_order}
							</span>
						</td>
						<td align="center">
							<i class="{if $brand.is_show}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" data-trigger="toggleState" data-url="{url path='goods/merchants_brand/toggle_show'}" data-id="{$brand.bid}"></i>
						</td>
						<td align="center">
							<a class="data-pjax no-underline" href='{url path="goods/merchants_brand/edit" args="id={$brand.bid}"}' title="{$lang.edit}"><i class="fontello-icon-edit"></i></a>
							<a class="ajaxremove no-underline" data-toggle="ajaxremove" data-msg="{t}您确定要删除品牌[{$brand.brand_name}]吗？{/t}" href='{url path="goods/merchants_brand/remove" args="id={$brand.bid}"}' title="{t}移除{/t}"><i class="fontello-icon-trash"></i></a>
						</td>
					</tr>
					<!-- {foreachelse} -->
					<tr><td class="no-records" colspan="10">{$lang.no_records}</td></tr>
					<!-- {/foreach} -->
				</tbody>
			</table>
			<!-- end brand list -->
		</form>
	</div>
</div>
<!-- {$brand_list.page} -->
<!-- {/block} -->