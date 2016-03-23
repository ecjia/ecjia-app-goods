<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} --> 
<script type="text/javascript">
	ecjia.admin.link_parts.init();
</script> 
<!-- {/block} --> 

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading"> 
		<!-- {if $ur_here}{$ur_here}{/if} --> 
		{if $action_link} <a href="{$action_link.href}" class="btn plus_or_reply data-pjax" id="sticky_a">
		<i class="fontello-icon-reply"></i>{$action_link.text}</a> {/if}
	</h3>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="tabbable">
			<ul class="nav nav-tabs">
				<!-- {foreach from=$tags item=tag} -->
				<li{if $tag.active} class="active"{/if}><a{if $tag.active} href="javascript:;"{else}{if $tag.pjax} class="data-pjax"{/if} href='{$tag.href}'{/if}><!-- {$tag.name} --></a></li>
				<!-- {/foreach} -->
			</ul>
			
			<form class="form-horizontal" action='{url path="goods/admin/add_link_parts" args="goods_id={$smarty.get.goods_id}"}' method="post" name="theForm" >
				<div class="tab-content">
					<fieldset>
						<div class="control-group choose_list span12" data-url="{url path='goods/admin/get_goods_list'}">
							<div class="ecjiaf-cb">
								<!-- <div class="f_l"> -->
									<select name="cat_id">
										<option value="0">{$lang.all_category}{$cat_list}</option>
									</select>
									<select name="brand_id">
										<option value="0">{$lang.all_brand}{html_options options=$brand_list}</option>
									</select>
								<!-- </div> -->
								<input type="text" name="keyword" placeholder="{t}商品名称{/t}" />
								<a class="btn" data-toggle="searchGoods"><!-- {$lang.button_search} --></a>
							</div>
							<span class="help-inline m_t5">搜索要关联的配件，搜到配件会展示在左侧列表框中。点击左侧列表中选项，配件即可进入右侧已关联列表。保存后生效。您还可以在右侧编辑关联配件的价格。</span>
						</div>
						<div class="control-group draggable">
							<div class="ms-container " id="ms-custom-navigation">
								<div class="ms-selectable">
									<div class="search-header">
										<input class="span12" id="ms-search" type="text" placeholder="{t}筛选搜索到的商品信息{/t}" autocomplete="off">
									</div>
									<ul class="ms-list nav-list-ready">
										<li class="ms-elem-selectable disabled"><span>暂无内容</span></li>
									</ul>
								</div>
								<div class="ms-selection">
									<div class="custom-header custom-header-align">关联配件</div>
									<ul class="ms-list nav-list-content">
										<!-- {foreach from=$group_goods_list item=link_good key=key} -->
										<li class="ms-elem-selection">
											<input type="hidden" name="goods_id[]" data-double="0" data-price="{$link_good.goods_price}" value="{$link_good.goods_id}" />
											<!-- {$link_good.goods_name} --><span class="link_price m_l5">[价格:{$link_good.goods_price}]</span>
											<span class="edit-list"><a class="change_link_price" href="javascript:;">修改价格</a><i class="fontello-icon-minus-circled ecjiafc-red del"></i></span>
										</li>
										<!-- {/foreach} -->
									</ul>
								</div>
							</div>
						</div>
					</fieldset>
				</div>
				<fieldset class="t_c">
					<button class="btn btn-gebo" type="submit">{t}保存{/t}</button>
					<input type="hidden" name="goods_id" value="{$goods.goods_id}" />
					{if $code neq ''}
					<input type="hidden" name="extension_code" value="{$code}" />
					{/if}
					<input type="hidden" id="type" value="{$link.type}" />
					<input type="hidden" name="act" value="{$form_act}" />
				</fieldset>
			</form>
		</div>
	</div>
</div>
<!-- {/block} -->