<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_mode.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->

<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} --><a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$action_link.href}{if $code}&extension_code={$code}{/if}"><i class="fontello-icon-reply"></i><!-- {$action_link.text} --></a><!-- {/if} -->
	</h3>
</div>
<div class="row-fluid">
	<div class="span12">
		<form class="form-horizontal" name="theForm" action="{$form}" method="post">
			<fieldset>
				<div class="control-group formSep">
					<label class="control-label">{t}商品模式：{/t}</label>
					<div class="controls chk_radio">
						<input class="uni_style" name="goods_model" type="radio" value="0" checked="true" autocomplete="off" /><span>{t}默认{/t}</span>
						<input class="uni_style" name="goods_model" type="radio" value="1" autocomplete="off" /><span>{t}仓库{/t}</span>
						<input class="uni_style" name="goods_model" type="radio" value="2" autocomplete="off" /><span>{t}地区{/t}</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{t}商品价格模式：{/t}</label>
					<div class="controls chk_radio">
						<input class="uni_style" name="model_price" type="radio" value="0" {if $price_model eq 0} checked="true"{/if} autocomplete="off" /><span>{t}默认{/t}</span>
						<input class="uni_style" name="model_price" type="radio" value="1" {if $price_model eq 1} checked="true"{/if} autocomplete="off" /><span>{t}仓库{/t}</span>
						<input class="uni_style" name="model_price" type="radio" value="2" {if $price_model eq 2} checked="true"{/if} autocomplete="off" /><span>{t}地区{/t}</span>
						<span class="help-block">根据不同价格模式，使用不同模式下的价格。</span>
					</div>
				</div>
				<div class="control-group formSep">
					<label class="control-label">{t}商品库存模式：{/t}</label>
					<div class="controls chk_radio">
						<input class="uni_style" name="model_inventory" type="radio" value="0" {if $inventory_model eq 0} checked="true"{/if} autocomplete="off" /><span>{t}默认{/t}</span>
						<input class="uni_style" name="model_inventory" type="radio" value="1" {if $inventory_model eq 1} checked="true"{/if} autocomplete="off" /><span>{t}仓库{/t}</span>
						<input class="uni_style" name="model_inventory" type="radio" value="2" {if $inventory_model eq 2} checked="true"{/if} autocomplete="off" /><span>{t}地区{/t}</span>
						<span class="help-block">根据不同库存模式，使用不同模式下的库存。</span>
					</div>
				</div>
				<div class="control-group">
						<div class="controls chk_radio">
							<input class="btn btn-gebo" type="submit" value="保存" />
						</div>
					</div>
			</fieldset>
			<input type="hidden" name="goods_id" value="{$goods_id}">
		</form>
	</div>
</div>

<div class="row-fluid goods-photo-list goods_warehouse_model{if $price_model eq 1} price{/if}{if $inventory_model eq 1} area{/if}">
	<div class="span12">
		<h3 class="heading m_b10">{t}仓库模式{/t}<small>{t}（编辑、排序、删除）{/t}</small></h3>
	</div>
	
	<div class="list wookmark goods_model">
		<ul>
			<!-- {foreach from=$warehouse_goods item=val}-->
			<li class="thumbnail">
				<div class="bd">
					<div>
						<p class="model-title">{$val.region_name}</p>
						<p class="model_number">
							仓库库存：{$val.region_number}
						</p>
						<p class="model_price">
							价格：{$val.warehouse_price}
						</p>
						<p class="model_price">
							促销价格：{$val.warehouse_promote_price}
						</p>
					</div>
					
				</div>
				<div class="input" data-toggle="modal" href="#add_warehouse" data-id="{$val.w_id}" data-warehouse="{$val.region_id}" data-number="{$val.region_number}" data-price="{$val.warehouse_price}" data-promote="{$val.warehouse_promote_price}">
					<i class="fontello-icon-edit"></i>
				</div>
				<div class="input right" data-toggle="ajaxremove" data-href="{url path='goods/admin_goods_mode/delete_warehouse_goods' args="id={$val.w_id}"}">
					<i class="fontello-icon-trash"></i>
				</div>
			</li>
			<!-- {/foreach} -->
			<li class="thumbnail add-ware-house">
				<a class="more" data-type="add" data-toggle="modal" href="#add_warehouse">
					<i class="fontello-icon-plus"></i>
					<span>添加商品仓库模式</span>
				</a>
			</li>
		</ul>
	</div>

	<br><br><br><br><br>

	<div class="modal hide fade in" id="add_warehouse">
		<div class="modal-header">
			<button class="close" data-dismiss="modal">×</button>
			<h3></h3>
		</div>
		<div class="span12 modal-body">
			<h3 class="m_b10">{t}输入添加信息{/t}</h3>
			<form class="form-horizontal" name="theForm" data-inserturl="{$form_insert}" data-editurl="{$form_edit}" method="post">
				<fieldset>
					<div class="control-group formSep">
						<label class="control-label">{t}仓库名称{/t}</label>
						<div class="controls chk_radio">
							<select name="warehouse_name">
								<option selected="" value="0">请选择...</option>
								<!--{foreach from=$repertory item=val}-->
								<option value="{$val.region_id}">{$val.region_name}</option>
								<!--{/foreach}-->
							</select>
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}仓库库存{/t}</label>
						<div class="controls chk_radio">
							<input type="text" size="10" value="0" name="warehouse_number" />
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}仓库价格{/t}</label>
						<div class="controls chk_radio">
							<input type="text" size="10" value="0" name="warehouse_price" />
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}仓库促销价格{/t}</label>
						<div class="controls chk_radio">
							<input type="text" size="10" value="0" name="warehouse_promote_price" />
						</div>
					</div>
					<input type="hidden" name="w_id" value="">
					<div class="control-group">
						<div class="controls chk_radio">
							<input class="btn btn-gebo" type="submit" value="提交" />
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>

<div class="row-fluid goods-photo-list goods_area_model{if $price_model eq 2} price{/if} {if $inventory_model eq 2} area{/if}">
	<div class="span12">
		<h3 class="heading m_b10">{t}地区模式{/t}<small>{t}（编辑、排序、删除）{/t}</small></h3>
	</div>

	<div class="list media_captcha wookmark goods_model">
		<ul>
		<!-- {foreach from=$area_goods item=rs} -->
			<li class="thumbnail">
				<div class="bd">
					<div class="model-title ware_name">{$rs.ware_name}</div>
					<div name="div_warehouseNumber">
						地区名称：{$rs.region_name} 
					</div>
					<div class="model_number">
						地区库存：{$rs.region_number}
					</div>
					<div class="model_price">
						地区价格：{$rs.region_price}
					</div>
					<div class="model_price">
						地区促销价格：{$rs.region_promote_price}
					</div>
				</div>
				<div class="input" data-toggle="modal" href="#area_houser_model" data-id="{$rs.a_id}" data-warehouse="{$rs.warehouse}" data-number="{$rs.region_number}" data-price="{$rs.region_price}" data-promote="{$rs.region_promote_price}" data-region="{$rs.region_id}">
					<i class="fontello-icon-edit"></i>
				</div>
				<div class="input right" data-toggle="ajaxremove" data-href="{url path='goods/admin_goods_mode/delete_area_goods' args="id={$rs.a_id}"}">
					<i class="fontello-icon-trash"></i>
				</div>
			</li>
			<!-- {/foreach} -->
			<li class="thumbnail add-ware-house">
				<a class="more" data-type="add"  data-toggle="modal" href="#area_houser_model">
					<i class="fontello-icon-plus"></i>
					<span>添加商品地区模式</span>
				</a>
			</li>
		</ul>
	</div>
	<br><br><br><br><br>

	<div class="modal hide fade in" id="area_houser_model">
		<div class="modal-header">
			<button class="close" data-dismiss="modal">×</button>
			<h3></h3>
		</div>
		<div class="span12 modal-body">
		<h3 class="m_b10">{t}输入添加信息{/t}</h3>
			<form class="form-horizontal" name="theForm" data-inserturl="{$form_action2}" data-editurl="{$form_area_url}" method="post">
				<fieldset>
					<div class="control-group formSep">
						<label class="control-label">{t}仓库名称{/t}</label>
						<div class="controls chk_radio">
							<select name="warehouse_area_name" data-toggle="regionSummary" data-url="{url path='goods/admin_goods_mode/region'}" data-type="1" data-target="region-summary-cities" data-url="{url path='goods/admin_goods_mode/region'}">
								<option selected="" value="0">请选择...</option>
								<!--{foreach from=$repertory item=val}-->
								<option value="{$val.region_id}">{$val.region_name}</option>
								<!--{/foreach}-->
							</select>
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}地区名称{/t}</label>
						<div class="controls chk_radio">
							<select class="region-summary-cities" name="region_name">
								<option value="0">请选择....</option>
							<!-- {foreach from=$area item=city} -->
								<option value="{$city.region_id}">{$city.region_name}</option>
							<!-- {/foreach} -->
							</select>
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}地区库存{/t}</label>
						<div class="controls chk_radio">
							<input type="text" size="10" value="0" name="region_number" />
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}地区价格{/t}</label>
						<div class="controls chk_radio">
							<input type="text" size="10" value="0" name="region_price" />
                            <span class="input-must">
                                <span class="require-field" style="color:#FF0000;">*</span>
                            </span>
						</div>
					</div>
					<div class="control-group formSep">
						<label class="control-label">{t}地区促销价格{/t}</label>
						<div class="controls chk_radio">
							<input type="text" size="10" value="0" name="region_promote_price" />
						</div>
					</div>
					<input type="hidden" name="a_id" value="">
					<div class="control-group">
						<div class="controls chk_radio">
							<input class="btn btn-gebo" type="submit" value="提交" />
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>
<!-- {/block} -->