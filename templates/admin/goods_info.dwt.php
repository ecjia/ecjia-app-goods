<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_info.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" id="sticky_a" href="{$action_link.href}"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<div class="tabbable">
			{if $action eq 'edit'}
			<ul class="nav nav-tabs">
				<!-- {foreach from=$tags item=tag} -->
				<li {if $tag.active}class="active"{/if}><a{if $tag.active} href="javascript:;"{else}{if $tag.pjax} class="data-pjax"{/if} href='{$tag.href}'{/if}><!-- {$tag.name} --></a></li>
				<!-- {/foreach} -->
			</ul>
			{/if}
			<form class="form-horizontal" enctype="multipart/form-data" action="{$form_action}" method="post" name="theForm">
				<div class="tab-content">
					<!--通用信息  -->
					<div class="tab-pane active" id="tab1">
						<fieldset>
							<div class="row-fluid edit-page editpage-rightbar">
								<div class="left-bar move-mod">
									<div class="control-group control-group-small" >
										<label class="control-label">{$lang.lab_goods_name}</label>
										<div class="controls">
											<input class="f_l w350" type="text" name="goods_name" value="{$goods.goods_name|escape}" style="color:{$goods_name_color};" size="30" />
											<div class="input-append color" data-color="#000"  id="color">
												<input class="span6" type="hidden" value="{$goods_name_color}"/>
												<span class="add-on" style="border-radius: 4px;">
													<i class="dft_color"></i>
												</span>
												<input name="goods_name_color" type="hidden" value="{$goods_name_color}">
											</div>
											<span class="input-must">{$lang.require_field}</span>
										</div>
									</div>
									<div class="control-group control-group-small" >
										<label class="control-label">{$lang.lab_goods_sn}</label>
										<div class="controls">
											<input class="w350" type="text" name="goods_sn" value="{$goods.goods_sn|escape}" size="20" data-toggle="checkGoodsSn" data-id="{$goods.goods_id}" data-url="{url path='goods/admin/check_goods_sn'}" />
											<label id="goods_sn_notice" class="error"></label>
											<span class="help-block" id="noticeGoodsSN">{$lang.notice_goods_sn}</span>
										</div>
									</div>
									<!--本店售价-->
									<div class="control-group control-group-small" >
										<label class="control-label">{$lang.lab_shop_price}</label>
										<div class="controls">
											<input class="w350" type="text" name="shop_price" value="{$goods.shop_price}" size="20" data-toggle="priceSetted" />
											<a class="btn" data-toggle="marketPriceSetted">{$lang.compute_by_mp}</a>
											<span class="input-must">{$lang.require_field}</span>
										</div>
									</div>
									<!--市场售价-->
									<div class="control-group control-group-small" >
										<label class="control-label">{$lang.lab_market_price}</label>
										<div class="controls">
											<input class="w350" type="text" name="market_price" value="{$goods.market_price}" size="20" />
											<button class="btn" type="button" data-toggle="integral_market_price">{$lang.integral_market_price}</button>
										</div>
									</div>

									<fieldset>
										<!-- {if $cfg.use_storage} -->
										<div class="control-group control-group-small" >
											<label class="control-label">{$lang.lab_goods_number}</label>
											<div class="controls">
												<input class="input-small w350" type="text" name="goods_number" value="{$goods.goods_number}" size="20" />
												<span class="input-must">{$lang.require_field}</span>
												<span class="help-block" {if $help_open}style="display:block" {else} style="display:none" {/if} id="noticeStorage">{$lang.notice_storage}</span>
											</div>
										</div>
										<div class="control-group control-group-small" >
											<label class="control-label">{$lang.lab_warn_number}</label>
											<div class="controls">
												<input class="input-small w350" type="text" name="warn_number" value="{$goods.warn_number}" size="20" />
											</div>
										</div>
										<!-- {/if} -->
										<!-- {if $code eq ''} -->
										<div class="control-group control-group-small" >
											<label class="control-label">{$lang.lab_goods_weight}</label>
											<div class="controls">
												<input class="f_l m_r5 input-small w350" type="text" name="goods_weight" value="{$goods.goods_weight_by_unit}" size="20" />
												<select name="weight_unit" class="w100">
													<!-- {html_options options=$unit_list selected=$weight_unit} -->
												</select>
											</div>
										</div>
										<!-- {/if} -->
										<div class="control-group control-group-small" >
											<label class="control-label">{t}作为商品：{/t}</label>
											<div class="controls chk_radio">
												<input type="checkbox" name="is_alone_sale" value="1" style="opacity: 0;" {if $goods.is_alone_sale}checked="checked"{/if}>
												<span>{$lang.alone_sale}</span>
											</div>
										</div>
										<div class="control-group control-group-small" >
											<label class="control-label">{$lang.lab_is_free_shipping}</label>
											<div class="controls chk_radio">
												<input type="checkbox" name="is_shipping" value="1" style="opacity: 0;" {if $goods.is_shipping}checked="checked"{/if}>
												<span>{$lang.free_shipping}</span>
											</div>
										</div>
									</fieldset>

									<div class="foldable-list move-mod-group" id="goods_info_sort_seo">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed acc-in move-mod-head" data-toggle="collapse" data-target="#goods_info_area_seo">
													<strong>{t}SEO优化{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in collapse" id="goods_info_area_seo">
												<div class="accordion-inner">

													<div class="control-group control-group-small" >
														<label class="control-label">{t}关键字：{/t}</label>
														<div class="controls">
															<input class="span12" type="text" name="keywords" value="{$goods.keywords|escape}" size="40" />
															<br />
															<p class="help-block w280 m_t5">{$lang.notice_keywords}</p>
														</div>
													</div>
													<div class="control-group control-group-small" >
														<label class="control-label">{$lang.lab_goods_brief}</label>
														<div class="controls">
															<textarea class="span12 h100" name="goods_brief" cols="40" rows="3">{$goods.goods_brief|escape}</textarea>
														</div>
													</div>

												</div>
											</div>
										</div>
									</div>

									<div class="foldable-list move-mod-group" id="goods_info_sort_note">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed acc-in move-mod-head" data-toggle="collapse" data-target="#goods_info_area_note">
													<strong>{t}备注信息{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in collapse" id="goods_info_area_note">
												<div class="accordion-inner">

													<div class="control-group control-group-small" >
														<label class="control-label">{$lang.lab_seller_note}</label>
														<div class="controls">
															<textarea name="seller_note" cols="40" rows="3" class="span12 h100">{$goods.seller_note}</textarea>
															<span class="help-block" {if $help_open}style="display:block" {else} style="display:none" {/if} id="noticeSellerNote">{$lang.notice_seller_note}</span>
														</div>
													</div>

												</div>
											</div>
										</div>
									</div>

									<!-- {if $action eq 'edit'} -->
									<div class="foldable-list move-mod-group" id="goods_info_sort_note">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed acc-in move-mod-head" data-toggle="collapse" data-target="#goods_info_term_meta">
													<strong>{t}自定义栏目{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in" id="goods_info_term_meta">
												<div class="accordion-inner">
	 												<!-- 自定义栏目模板区域 START -->
	 												<!-- {if $data_term_meta} -->
 													<label><b>编辑自定义栏目：</b></label>
													<table class="table smpl_tbl ">
														<thead>
															<tr>
																<td class="span4">名称</td>
																<td>值</td>
															</tr>
														</thead>
														<tbody class="term_meta_edit" data-id="{$goods.goods_id}" data-extension-code="{$code}" data-active="{url path='goods/admin/update_term_meta'}">
															<!-- {foreach from=$data_term_meta item=term_meta} -->
															<tr>
																<td>
																	<input class="span12" type="text" name="term_meta_key" value="{$term_meta.meta_key}" />

																	<input type="hidden" name="term_meta_id" value="{$term_meta.meta_id}">
																	<a class="data-pjax btn m_t5" data-toggle="edit_term_meta" href="javascript:;">{t}更新{/t}</a>
																	<a class="ajaxremove btn btn-danger m_t5" data-toggle="ajaxremove" data-msg="{t}您确定要删除此条自定义栏目吗？{/t}" href="{url path='goods/admin/remove_term_meta' args="meta_id={$term_meta.meta_id}"}">{t}移除{/t}</a>

																</td>
																<td><textarea class="span12 h70" name="term_meta_value">{$term_meta.meta_value}</textarea></td>
															</tr>
															<!-- {/foreach} -->
														</tbody>
													</table>
													<!-- {/if} -->

 													<!-- 编辑区域 -->
 													<label><b>添加自定义栏目：</b></label>
 													<div class="term_meta_add" data-id="{$goods.goods_id}" data-extension-code="{$code}" data-active="{url path='goods/admin/insert_term_meta'}">
														<table class="table smpl_tbl ">
															<thead>
																<tr>
																	<td class="span4">名称</td>
																	<td>值</td>
																</tr>
															</thead>
															<tbody class="term_meta_edit" data-id="{$goods.goods_id}" data-extension-code="{$code}" data-active="{url path='goods/admin/update_term_meta'}">
																<tr>
																	<td>
 																		<!-- {if $term_meta_key_list} -->
																		<select class="span12" data-toggle="change_term_meta_key" >
																			<!-- {foreach from=$term_meta_key_list item=meta_key} -->
																			<option value="{$meta_key.meta_key}">{$meta_key.meta_key}</option>
																			<!-- {/foreach} -->
																		</select>
																		<input class="span12 hide" type="text" name="term_meta_key" value="{$term_meta_key_list.0.meta_key}" />
																		<div><a data-toggle="add_new_term_meta" href="javascript:;">添加新栏目</a></div>
 																		<!-- {else} -->
																		<input class="span12" type="text" name="term_meta_key" value="" />
																		<!-- {/if} -->
																		<a class="btn m_t5" data-toggle="add_term_meta" href="javascript:;">添加自定义栏目</a>
																	</td>
																	<td><textarea class="span12" name="term_meta_value"></textarea></td>
																</tr>
															</tbody>
														</table>
													</div>
	 												<!-- 自定义栏目模板区域 END -->
												</div>
											</div>
										</div>
									</div>
									<!-- {/if} -->
								</div>
								<!-- 选填信息 -->
								<div class="right-bar move-mod">
									<div class="foldable-list move-mod-group" id="goods_info_sort_submit">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_submit">
													<strong>{t}发布{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in collapse" id="goods_info_area_submit">
												<div class="accordion-inner">
													<div class="control-group control-group-small" >
														{$lang.lab_is_on_sale}
															<input type="checkbox" name="is_on_sale" value="1" style="opacity: 0;" {if $goods.is_on_sale}checked="checked"{/if}>
															<span>{$lang.on_sale_desc}</span>
													</div>
													<div class="control-group control-group-small" >
														{$lang.lab_intro}
															<input type="checkbox" name="is_best" value="1" style="opacity: 0;" {if $goods.is_best}checked="checked"{/if}>
															<span>{$lang.is_best}</span>
															<input type="checkbox" name="is_new" value="1" style="opacity: 0;" {if $goods.is_new}checked="checked"{/if}>
															<span>{$lang.is_new}</span>
															<input type="checkbox" name="is_hot" value="1" style="opacity: 0;" {if $goods.is_hot}checked="checked"{/if}>
															<span>{$lang.is_hot}</span>
													</div>
													<input type="hidden" name="goods_id" value="{$goods.goods_id}" />
													<input type="hidden" name="goods_copyid" value="{$goods.goods_copyid}" />
													{if $code neq ''}
													<input type="hidden" name="extension_code" value="{$code}" />
													{/if}
													<button class="btn btn-gebo" type="submit">{if $goods.goods_id}{t}更新{/t}{else}{t}发布{/t}{/if}</button>
													<input type="hidden" id="type" value="{$link.type}" />
												</div>
											</div>
										</div>
									</div>

									<div class="foldable-list move-mod-group" id="goods_info_sort_cat">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_admin_cat">
													<strong>{t}平台商品分类{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in in_visable collapse" id="goods_info_admin_cat">
												<div class="accordion-inner">
													<div class="control-group">

														<label><b>{t}选择平台商品分类{/t}</b></label>
														<div>
															<select class="w300" name="cat_id" >
																<option value="0">{$lang.select_please}</option>
																<!-- {foreach from=$cat_list item=cat} -->
																<!-- {if $cat.is_show} -->
																<option {if $goods.cat_id eq $cat.cat_id}selected="selected"{/if} value="{$cat.cat_id}" {if $cat.level}style="padding-left:{$cat.level * 20}px"{/if}><!-- {$cat.cat_name} --></option>
																<!-- {/if} -->
																<!-- {/foreach} -->
															</select>
														</div>

														<label><b>{t}选择平台扩展分类{/t}</b></label>
														<div class="goods-cat">
															<div class="goods-span">
																<!-- {foreach from=$cat_list item=cat} -->
																	<!-- {if $cat.is_show} -->
																		<label style="padding-left:{$cat.level * 20}px">
																			<input type="checkbox" name="other_cat[]" value="{$cat.cat_id}" {if $cat.is_other_cat}checked="checked"{/if} />
																			<!-- {$cat.cat_name} -->
																		</label>
																	<!-- {/if} -->
																<!-- {/foreach} -->
															</div>
														</div>
														<a class="m_l5 l_h30" class="data-pjax" href="{url path='goods/admin_category/add'}">{t}去添加平台商品分类{/t}</a>
													</div>
												</div>
											</div>
										</div>
									</div>

									{if $goods.seller_id}
									<div class="foldable-list move-mod-group" id="goods_info_sort_cat">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_cat">
													<strong>{t}商户商品分类{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in in_visable collapse" id="goods_info_area_cat">
												<div class="accordion-inner">
													<div class="control-group">

														<label><b>{t}选择商户商品分类{/t}</b></label>
														<div>
															<select class="w300" name="store_category" >
																<option value="0">{$lang.select_please}</option>
																<!-- {foreach from=$merchant_cat_list item=cat} -->
																<option {if $goods.cat_id eq $cat.cat_id}selected="selected"{/if} value="{$cat.cat_id}" {if $cat.level}style="padding-left:{$cat.level * 20}px"{/if}><!-- {$cat.cat_name} --></option>
																<!-- {/foreach} -->
															</select>
														</div>

														<label><b>{t}选择扩展商户分类{/t}</b></label>
														<div class="goods-cat">
															<div class="goods-span">
																<!-- {foreach from=$merchant_cat_list item=cat} -->
																	<label style="padding-left:{$cat.level * 20}px">
																		<input type="checkbox" name="other_cat[]" value="{$cat.cat_id}" {if $cat.is_other_cat}checked="checked"{/if} />
																		<!-- {$cat.cat_name} -->
																	</label>
																<!-- {/foreach} -->
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									{/if}

									<div class="foldable-list move-mod-group" id="goods_info_sort_brand">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_brand">
													<strong>{t}商品品牌{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in in_visable collapse" id="goods_info_area_brand">
												<div class="accordion-inner">
													<div class="control-group">
														<label><b>{t}选择商品品牌：{/t}</b></label>
														<select class="w300" name="brand_id">
															<option value="0">{$lang.select_please}{html_options options=$brand_list selected=$goods.brand_id}</option>
														</select>
														<a class="m_l5 l_h30" class="data-pjax" href="{url path='goods/admin_brand/add'}">{t}去添加商品品牌{/t}</a>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="foldable-list move-mod-group" id="goods_info_sort_img">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_img">
													<strong>{t}商品图片{/t}</strong>
												</a>
											</div>
											<div class="accordion-body in collapse" id="goods_info_area_img">
												<div class="accordion-inner">
													<div class="control-group">
														<label>{$lang.lab_picture}</label>
														<div class="ecjiaf-db">
															<div class="goods_img">
																<span {if $goods.goods_img}class="btn fileupload-btn preview-img" style="background-image: url({$goods.goods_img});"{else}class="btn fileupload-btn"{/if}>
																	<span class="fileupload-exists"><i class="fontello-icon-plus"></i></span>
																</span>
																<input class="hide" type="file" name="goods_img" onchange="ecjia.admin.goods_info.previewImage(this)" />
															</div>
															<div class="thumb_img{if !$goods.goods_thumb} hide{/if}">
																<label>{t}商品缩略图：{/t}</label>
																<span {if $goods.goods_img}class="btn fileupload-btn preview-img" style="background-image: url({$goods.goods_thumb});"{else}class="btn fileupload-btn"{/if}>
																	<span class="fileupload-exists"><i class="fontello-icon-plus"></i></span>
																</span>
																<input class="hide" type="file" name="thumb_img" onchange="ecjia.admin.goods_info.previewImage(this)" />
															</div>
															<div><span class="help-inline">点击更换商品图片或商品缩略图。</span></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="foldable-list move-mod-group" id="goods_info_sort_rankprice">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle move-mod-head" data-toggle="collapse" data-target="#goods_info_area_rankprice">
													<strong>折扣、促销价格</strong>
												</a>
											</div>
											<div class="accordion-body collapse" id="goods_info_area_rankprice">
												<div class="accordion-inner">
													<!-- {if $user_rank_list}  -->
													<!-- 会员价格  -->
													<div class="control-group control-group-small">
														<label class="w80 fl t_r">
															<b>{$lang.lab_user_price}</b>
														</label>
														<div class="f_l m_l30">
															<div class="goods-span">
																<!-- {foreach from=$user_rank_list item=user_rank} -->
																<div class="m_b5">
																	<span class="f_l w80 t_l l_h30">{$user_rank.rank_name|truncate:"10":".."}</span>
																	<input type="text" id="rank_{$user_rank.rank_id}" class="span4" name="user_price[]" value="{$member_price_list[$user_rank.rank_id]|default:-1}" size="8" />
																	<input type="hidden" name="user_rank[]" value="{$user_rank.rank_id}" />
																	<span class="m_l5" id="nrank_{$user_rank.rank_id}"></span>&nbsp;
																</div>
																<!-- {/foreach} -->
																<p class="help-block w280 m_t5" id="noticeUserPrice">{$lang.notice_user_price}</p>
															</div>
														</div>
													</div>
													<!-- {/if} -->
													<!-- 优惠价格 -->
													<div class="control-group control-group-small">
														<label class="w80 fl t_r"><b>{t}优惠价格：{/t}</b></label>
														<div class="f_l m_l10">
															<!-- {foreach from=$volume_price_list item=volume_price name="volume_price_tab"} -->
															<div class="goods-span">
																<span class="m_l5 l_h30">{$lang.volume_number}</span>
																<input class="w50" type="text" name="volume_number[]" size="8" value="{$volume_price.number}"/>
																<span class="m_l5 l_h30">{$lang.volume_price}</span>
																<input class="w80" type="text" name="volume_price[]" size="8" value="{$volume_price.price}"/>
																<span>
																	<a class="l_h30 t_l no-underline" href="javascript:;" data-toggle="clone-obj" data-parent=".goods-span">
																		<i class="fontello-icon-plus hide"></i>
																	</a>
																</span>
															</div>
															<!-- {/foreach} -->
															<a class="m_l5 l_h30 add_volume_price" href="javascript:;">{t}添加优惠价格{/t}</a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="foldable-list move-mod-group" id="goods_info_sort_promote">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle move-mod-head" data-toggle="collapse" data-target="#goods_info_area_promote">
													<strong>促销信息</strong>
												</a>
											</div>
											<div class="accordion-body collapse" id="goods_info_area_promote">
												<div class="accordion-inner">
													<div class="control-group control-group-small">
														<input class="toggle_promote" type="checkbox" name="is_promote" value="1" {if $goods.is_promote}checked="checked"{/if} />
														<span>{$lang.lab_promote_price}</span>
														<input class="span4" type="text" id="promote_1" name="promote_price" value="{$goods.promote_price}" size="20"{if !$goods.is_promote} disabled{/if} />
													</div>
													<div class="control-group control-group-small">
														<div class="f_l m_l10">
															<div class="w300">
																<span class="m_l5 l_h30">{$lang.lab_promote_date}</span>
																<input class="date span4" type="text" name="promote_start_date"{if !$goods.promote_start_date} disabled="disabled"{/if} size="12" value="{$goods.promote_start_date}" />
																<span class="m_l5 l_h30">&nbsp;&nbsp;-&nbsp;&nbsp;</span>
																<input class="date span4" type="text" name="promote_end_date"{if !$goods.promote_end_date} disabled="disabled"{/if} size="12" value="{$goods.promote_end_date}" />
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- 积分相关 -->
									<div class="foldable-list move-mod-group" id="goods_info_sort_integral">
										<div class="accordion-group">
											<div class="accordion-heading">
												<a class="accordion-toggle move-mod-head" data-toggle="collapse" data-target="#goods_info_area_integral">
													<strong>积分相关</strong>
												</a>
											</div>
											<div class="accordion-body collapse" id="goods_info_area_integral">
												<div class="accordion-inner">
													<!-- 赠送消费积分数-->
													<div class="control-group control-group-small">
														<label class="f_l w120 m_t5">{$lang.lab_give_integral}</label>
														<div class="m_l5 l_h30">
															<input class="span3" type="text" name="give_integral" value="{$goods.give_integral}" size="20" data-toggle="parseint_input" />
															<span class="help-block" id="giveIntegral">
																{$lang.notice_give_integral}
															</span>
														</div>
													</div>
													<!-- 赠送等级积分数 -->
													<div class="control-group control-group-small">
														<label class="f_l w120 m_t5">{$lang.lab_rank_integral}</label>
														<div class="m_l5 l_h30">
															<input class="span3" type="text" name="rank_integral" value="{$goods.rank_integral}" size="20" data-toggle="parseint_input" />
															<span class="help-block" id="rankIntegral">{$lang.notice_rank_integral}</span>
														</div>
													</div>
													<!-- 积分购买金额 -->
													<div class="control-group control-group-small">
														<label class="f_l w120 m_t5">{$lang.lab_integral}</label>
														<div class="m_l5 l_h30">
															<input class="span3" type="text" name="integral" value="{$goods.integral}" size="20" data-toggle="parseint_input" />
															<span class="help-block" id="noticPoints">{$lang.notice_integral}</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>

				<div class="row-fluid edit-page">
					<div class="span12 move-mod">
					</div>
				</div>

			</form>
		</div>
	</div>
</div>
<!-- {/block} -->
