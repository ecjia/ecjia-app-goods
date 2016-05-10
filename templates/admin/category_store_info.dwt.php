<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_category_info.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>
<!-- start add new category form -->
<form class="form-horizontal" action="{$form_action}" method="post" name="theForm" enctype="multipart/form-data" data-edit-url="{RC_Uri::url('goods/admin_category_store/edit')}">
	<fieldset class="formSep">
		<div class="row-fluid editpage-rightbar">
			<div class="left-bar move-mod">
				<div class="control-group">
					<label class="control-label">{$lang.cat_name}：</label>
					<div class="controls">
						<input class="w350" type='text' name='cat_name' maxlength="20" value='{$cat_info.cat_name|escape:html}' size='27'/>
						<span class="input-must">{$lang.require_field}</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">{$lang.parent_id}：</label>
					<div class="controls">
						<select class="w350" name="parent_id">
							<option value="0">{$lang.cat_top}</option>
							<!-- {$cat_select} -->
						</select>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">{$lang.measure_unit}：</label>
					<div class="controls">
						<input class="w350" type="text" name='measure_unit' value='{$cat_info.measure_unit}' size="12" />
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">{$lang.grade}：</label>
					<div class="controls">
						<input class="w350" type="text" name="grade" value="{$cat_info.grade|default:0}" size="40" />
						<span class="help-block" {if $help_open}style="display:block" {else} style="display:none" {/if} id="noticeGrade">{$lang.notice_grade}</span>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">
						{$lang.filter_attr}：
					</label>
					<div class="controls">
						<!-- {if $attr_cat_id eq 0} -->
						<div class="goods_type">
							<select class="w150 choose_goods_type" data-url="{url path='goods/admin_category/choose_goods_type'}" autocomplete="off">
								<option value="0">{$lang.sel_goods_type}</option>
								<!-- {$goods_type_list} -->
							</select>&nbsp;&nbsp;
							<select class="w150 show_goods_type" name="filter_attr[]" autocomplete="off">
								<option value="0">{$lang.sel_filter_attr}</option>
							</select>
							<a class="no-underline" data-toggle="clone-obj" data-parent=".goods_type" href="javascript:;"><i class="fontello-icon-plus"></i></a>    
						</div>
						<!-- {/if} -->

						<!-- {foreach from=$filter_attr_list item=filter_attr name="filter_attr_tab"}-->  
						<div class="goods_type">
							<select class="w150 choose_goods_type" data-url="{url path='goods/admin_category/choose_goods_type'}" autocomplete="off">
								<option value="0">{$lang.sel_goods_type}</option>
								<!-- {$filter_attr.goods_type_list} -->
							</select>&nbsp;&nbsp;
							<select class="w150 show_goods_type" name="filter_attr[]" autocomplete="off">
								<option value="0">{$lang.sel_filter_attr}</option>
								<!-- {html_options options=$filter_attr.option selected=$filter_attr.filter_attr} -->
							</select>
							<!-- {if $smarty.foreach.filter_attr_tab.index eq 0} -->
							<a class="no-underline" data-toggle="clone-obj" data-parent=".goods_type" href="javascript:;"><i class="fontello-icon-plus"></i></a>
							<!-- {else} -->
							<a class="no-underline" data-toggle="remove-obj" data-parent=".goods_type" href="javascript:;"><i class="fontello-icon-minus"></i></a>
							<!-- {/if} -->

							<!-- <select onChange="changeCat(this)"><option value="0">{$lang.sel_goods_type}</option>{$filter_attr.goods_type_list}</select>&nbsp;&nbsp;
							<select name="filter_attr[]"><option value="0">{$lang.sel_filter_attr}</option>{html_options options=$filter_attr.option selected=$filter_attr.filter_attr}</select> -->
						</div>
						<!-- {/foreach}-->
						<span class="help-block" style="display:block" id="noticeFilterAttr">{$lang.filter_attr_notic}</span>
					</div>
				</div>

				<div class="foldable-list move-mod-group" id="goods_info_sort_seo">
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_seo">
								<strong>{t}SEO优化{/t}</strong>
							</a>
						</div>
						<div class="accordion-body in collapse" id="goods_info_area_seo">
							<div class="accordion-inner">
								<div class="control-group control-group-small" >
									<label class="control-label">{$lang.keywords}：</label>
									<div class="controls">
										<input class="span12" type="text" name="keywords" value="{$cat_info.keywords|escape}" size="40" />
										<br />
										<p class="help-block w280 m_t5">{t}用英文逗号分隔{/t}</p>
									</div>
								</div>
								<div class="control-group control-group-small" >
									<label class="control-label">{$lang.cat_desc}：</label>
									<div class="controls">
										<textarea class="span12" name='cat_desc' rows="6" cols="48">{$cat_info.cat_desc}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="right-bar move-mod">
				<div class="foldable-list move-mod-group edit-page" id="goods_info_sort_brand">
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_brand">
								<strong>{t}促销信息{/t}</strong>
							</a>
						</div>
						<div class="accordion-body in in_visable collapse" id="goods_info_area_brand">
							<div class="accordion-inner">
								<div class="control-group control-group-small">
									<label class="control-label"><b>{$lang.sort_order}：</b></label>
									<div class="controls">
										<input class="w200" type="text" name='sort_order' {if $cat_info.sort_order}value='{$cat_info.sort_order}'{else} value="50"{/if} size="15" />
									</div>
								</div>
								<div class="control-group control-group-small">
									<label class="control-label">{$lang.is_show}：</label>
									<div class="controls chk_radio">
										<input type="radio" name="is_show" id="" value="1" {if $cat_info.is_show neq 0}checked="checked"{/if}  /><span>{$lang.yes}</span>
										<input type="radio" name="is_show" id="" value="0" {if $cat_info.is_show eq 0}checked="checked"{/if}  /><span>{$lang.no}</span>
									</div>
								</div>
								<div class="control-group control-group-small">
									<label class="control-label">{t}首页推荐{/t}：</label>
									<div class="controls chk_radio">
										<input type="checkbox" name="cat_recommend[]" value="1"  {if $cat_recommend[1] eq 1}checked="checked"{/if}  /><span>{$lang.index_best}</span>
										<input type="checkbox" name="cat_recommend[]" value="2"  {if $cat_recommend[2] eq 1}checked="checked"{/if}  /><span>{$lang.index_new}</span>
										<input type="checkbox" name="cat_recommend[]" value="3"  {if $cat_recommend[3] eq 1}checked="checked"{/if}  /><span>{$lang.index_hot}</span>
										<span class="help-block">{t}设置为首页推荐类型{/t}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="foldable-list move-mod-group" id="goods_info_sort_img">
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle collapsed move-mod-head" data-toggle="collapse" data-target="#goods_info_area_img">
								<strong>{t}分类图片{/t}</strong>
							</a>
						</div>
						<div class="accordion-body in collapse" id="goods_info_area_img">
							<div class="accordion-inner">
								<label>{$lang.lab_picture}</label>
								<div class="ecjiaf-db">
									<div class="fileupload {if $cat_info.style}fileupload-exists{else}fileupload-new{/if} m_t10" data-provides="fileupload">
										<div class="fileupload-preview fileupload-exists thumbnail"><input type="hidden" name="old_img" value="1" />{if $cat_info.style}<img src="{$cat_info.style}" >{/if}</div>
										<div>
											<span class="btn btn-file">
												<span class="fileupload-new">{t}选择分类图片{/t}</span>
												<span class="fileupload-exists">{t}修改分类图片{/t}</span>
												<input type="file" name="cat_img" />
											</span>
											<a class="btn fileupload-exists" data-dismiss="fileupload" href="#">{t}删除{/t}</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="control-group">
		<div class="controls">
			<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
			<input type="hidden" name="old_cat_name" value="{$cat_info.cat_name}" />
			<input type="hidden" name="cat_id" value="{$cat_info.cat_id}" />
		</div>
	</div>
</form>
<!-- {/block} -->