<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia-merchant.dwt.php"} -->

<!-- {block name="footer"} --> 
<script type="text/javascript">
	ecjia.merchant.product_spec.init();
</script> 
<!-- {/block} --> 

<!-- {block name="home-content"} -->

{if $step}
<!-- #BeginLibraryItem "/library/goods_step.lbi" --><!-- #EndLibraryItem -->
{/if}

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

<div class="modal fade" id="myModal1"></div>

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
                <div class="tab-content">
                	<div class="form">
						<form class="form-horizontal" action="{$form_action}" method="post" name="theForm">
							<fieldset>
								<div class="template_box">
									<div class="box_content">
										<div class="form-group">
											<label class="control-label col-lg-2 ">{t domain="goods"}商品货号：{/t}</label>
											<div class="col-lg-6">
												<input class="form-control" type="text" name="goods_sn" value="{$goods_info.goods_sn}" disabled="disabled" />
											</div>
										</div>
										
										<div class="form-group">
											<label class="control-label col-lg-2 ">{t domain="goods"}库存数量：{/t}</label>
											<div class="col-lg-6">
												<input class="form-control" type="text" name="goods_number" value="{$goods_info.goods_number}" >
											
											</div>
										</div>
										
										<div class="form-group">
											<label class="control-label col-lg-2 ">{t domain="goods"}商品条形码：{/t}</label>
											<div class="col-lg-6">
												<input class="form-control" type="text" name="goods_barcode" value="{$goods_info.goods_barcode}" >
												<span class="help-block">非必填项，条形码必须搭配条码秤才可使用</span>
											</div>
										</div>
									 </div>
								</div> 
								
								<span class="help-block m_t10">注：商品规格设置后，商品的价格、货号、库存、条形码都以商品规格属性为准</span>
								
								<div class="template_box">
									{if $has_template}
										<div class="box_content">
											<div class="form-group">
												<label class="control-label col-lg-2 ">{t domain="goods"}规格模板：{/t}</label>
												<div class="col-lg-6 l_h35">
													{$template_info.cat_name}
													<span class="m_l10">
														<a href='{url path="goods/mh_category/edit" args="cat_id={$goods_info.merchant_cat_id}"}'><button type="button" class="btn btn-info" >{t domain="goods"}更换模板{/t}</button></a>
													</span>
														<a data-toggle="modal" data-backdrop="static" href="#myModal1" goods-id="{$goods_info.goods_id}" attr-url="{RC_Uri::url('goods/merchant/select_spec_values')}" ><button class="btn btn-info"><i class="fa fa-plus"></i> {t domain="goods"}选择属性值{/t}</button></a>
												</div>
											</div>
											
											<hr>
										</div>
									{else}
										<div class="box_content">
											<div class="form-group">
												<label class="control-label col-lg-2 ">{t domain="goods"}规格模板：{/t}</label>
												<div class="col-lg-6 l_h35">
													<span class="badge bg-important">!</span> <span class="ecjiafc-red">您当前还未绑定任何规格模板，请先绑定后，再来设置</span>
												</div>
											</div>
											
											<div class="form-group">
												<div class="col-lg-offset-2 col-lg-6">
													<a href='{url path="goods/mh_category/edit" args="cat_id={$goods_info.merchant_cat_id}"}'><button type="button" class="btn btn-info" >{t domain="goods"}绑定模板{/t}</button></a>
												</div>
											</div>
										</div>
									{/if}
								</div>
		
								{if $has_template}
									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-6 m_t10">
											<button class="btn btn-info" type="submit">{t domain="goods"}保存{/t}</button>
											<input type="hidden" name="mer_cat_id" value="{$goods_info.merchant_cat_id}" />
											<input type="hidden" name="goods_id" value="{$goods_info.goods_id}" />
											<input type="hidden" name="template_id" value="{$template_id}" />
										</div>
									</div>
								{/if}
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- {/block} -->