<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!--{extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.goods_list.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div class="modal hide fade" id="movetype">
	<div class="modal-header">
		<button class="close" data-dismiss="modal">×</button>
		<h3>{lang key='goods::goods.move_to_cat'}</h3>
	</div>
	<div class="modal-body h300">
		<div class="row-fluid ecjiaf-tac">
			<div>
				<select class="noselect w200" size="15" name="target_cat">
					<option value="0">{lang key='goods::goods.goods_cat'}</option>
					<!-- {$cat_list} -->
				</select>
			</div>
			<div>
				<a class="btn btn-gebo m_l5" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=move_to&" data-msg="{lang key='goods::goods.move_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_move_goods'}" href="javascript:;" name="move_cat_ture">{lang key='goods::goods.start_move'}</a>
			</div>
		</div>
	</div>
</div>
<div>
	<h3 class="heading"> 
		<!-- {if $ur_here}{$ur_here}{/if} --> 
		{if $action_link}
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax" id="sticky_a">
			<i class="fontello-icon-plus"></i>{$action_link.text}
		</a>{/if}
	</h3>
</div>

<!-- <div class="row-fluid"> -->
<!-- <div class="choose_list span12">  -->
<ul class="nav nav-pills">
	<li class="{if $smarty.get.is_on_sale neq 1 && $smarty.get.is_on_sale neq 2}active{/if}"><a class="data-pjax" href="{RC_Uri::url('goods/admin/init',"is_on_sale=0")}">{lang key='goods::goods.intro_type'} <span class="badge badge-info">{$goods_list.filter.count_goods_num}</span> </a></li>
	<li class="{if $smarty.get.is_on_sale eq 1}active{/if}"><a class="data-pjax" href="{RC_Uri::url('goods/admin/init',"is_on_sale=1")}">{lang key='goods::goods.is_on_saled'}<span class="badge badge-info use-plugins-num">{$goods_list.filter.count_on_sale}</span></a></li>
	<li class="{if $smarty.get.is_on_sale eq 2}active{/if}"><a class="data-pjax" href="{RC_Uri::url('goods/admin/init',"is_on_sale=2")}">{lang key='goods::goods.not_on_saled'}<span class="badge badge-info unuse-plugins-num">{$goods_list.filter.count_not_sale}</span></a></li>
	<!-- 上架 -->
	<!-- <select class="w100" name="is_on_sale"><option value=''>{$lang.intro_type}</option><option value="1">{$lang.on_sale}</option><option value="0">{$lang.not_on_sale}</option></select> -->

	<form class="f_r form-inline" action='{RC_Uri::url("goods/admin/init")}' method="post" name="searchForm">
		<!-- 关键字 -->
		<input type="text" name="keyword" value="{$smarty.get.keyword}" placeholder="{lang key='goods::goods.enter_goods_keywords'}" size="15" />
		<button class="btn" type="submit">{lang key='system::system.button_search'}</button>
	</form>
</ul>
<!-- </div> -->
<!-- </div> -->
<div class="row-fluid batch">
	<div class="choose_list">
		<div class="btn-group f_l m_r5">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fontello-icon-cog"></i>{lang key='goods::goods.batch_handle'}<span class="caret"></span>
			</a>
			<ul class="dropdown-menu batch-move" data-url="{RC_Uri::url('goods/admin/batch')}">
				<li><a class="batch-trash-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=trash&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_trash_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_trash_goods'}" href="javascript:;"> <i class="fontello-icon-box"></i>{lang key='goods::goods.move_to_trash'}</a></li>
				<li><a class="batch-sale-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=on_sale&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_on_sale_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_sale_goods'}" href="javascript:;"> <i class="fontello-icon-up-circled2"></i>{lang key='goods::goods.on_sale'}</a></li>
				<li><a class="batch-notsale-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_on_sale&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_not_on_sale_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_not_sale_goods'}" href="javascript:;"> <i class="fontello-icon-down-circled2"></i>{lang key='goods::goods.not_on_sale'}</a></li>
				<li><a class="batch-best-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=best&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_best_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_best_goods'}" href="javascript:;"> <i class="fontello-icon-star"></i>{lang key='goods::goods.best'}</a></li>
				<li><a class="batch-notbest-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_best&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_not_best_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_not_best_goods'}" href="javascript:;"><i class="fontello-icon-star-empty"></i>{lang key='goods::goods.not_best'}</a></li>
				<li><a class="batch-new-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=new&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_new_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_new_goods'}" href="javascript:;"> <i class="fontello-icon-flag"></i>{lang key='goods::goods.new'}</a></li>
				<li><a class="batch-notnew-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_new&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_not_new_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_not_news_goods'}" href="javascript:;"> <i class="fontello-icon-flag-empty"></i>{lang key='goods::goods.not_new'}</a></li>
				<li><a class="batch-hot-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=hot&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_hot_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_hot_goods'}" href="javascript:;"> <i class="fontello-icon-thumbs-up-alt"></i>{lang key='goods::goods.hot'}</a></li>
				<li><a class="batch-nothot-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_hot&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{lang key='goods::goods.batch_not_hot_confirm'}" data-noSelectMsg="{lang key='goods::goods.select_not_hot_goods'}" href="javascript:;"> <i class="fontello-icon-thumbs-up"></i>{lang key='goods::goods.not_hot'}</a></li>
				<li><a class="batch-move-btn"data-name="move_cat" data-move="data-operatetype" href="javascript:;"> <i class="fontello-icon-forward"></i>{$lang.move_to}</a></li>
			</ul>
		</div>

		<form class="f_r form-inline" action="{RC_Uri::url('goods/admin/init')}"  method="post" name="siftForm">
			<div class="screen f_r">
				<!-- 分类 -->
				<select class="w220" name="cat_id">
					<option value="0">{lang key='goods::goods.goods_cat'}</option>
					<!-- {$cat_list} -->
				</select>
				<!-- 品牌 -->
				<select class="no_search w120" name="brand_id">
					<option value="0">{lang key='goods::goods.goods_brand'}</option>
					<!-- {foreach from=$brand_list item=list key=key} -->
					<option value="{$key}" {if $key == $smarty.get.brand_id}selected{/if}>{$list}</option>
					<!-- {/foreach} -->
				</select>
				<!-- 推荐 -->
				<select class="w100" name="intro_type">
					<option value="0">{lang key='goods::goods.intro_type'}</option>
					<!-- {foreach from=$intro_list item=list key=key} -->
					<option value="{$key}" {if $key == $smarty.get.intro_type}selected{/if}>{$list}</option>
					<!-- {/foreach} -->
				</select>
				<button class="btn screen-btn" type="button">{lang key='goods::goods.filter'}</button>
			</div>
		</form>
	</div>
</div>
<div class="row-fluid list-page">
	<div class="span12">
		<form method="post" action="{$form_action}" name="listForm">
			<div class="row-fluid">
				<table class="table table-striped smpl_tbl table_vam table-hide-edit" id="smpl_tbl" data-uniform="uniform">
					<thead>
						<tr>
							<th class="table_checkbox">
								<input type="checkbox" name="select_rows" data-toggle="selectall" data-children=".checkbox"/>
							</th>
							<th class="w80">{lang key='goods::goods.thumb'}</th>
							<th>{lang key='goods::goods.goods_name'}</th>
							<th class="w150"> {t}商家名称{/t} </th>
							<th class="w70">{t}审核{/t} </th>
							<th class="w100" data-toggle="sortby" data-sortby="goods_sn">{lang key='goods::goods.goods_sn'}</th>
							<th class="w70" data-toggle="sortby" data-sortby="shop_price">{lang key='goods::goods.shop_price'}</th>
							<th class="w70" data-toggle="sortby" data-sortby="sort_order">{lang key='goods::goods.sort_order'}</th>
							<th class="w50"> {lang key='goods::goods.is_on_sale'} </th>
							<th class="w35"> {lang key='goods::goods.is_best'} </th>
							<th class="w35"> {lang key='goods::goods.is_new'} </th>
							<th class="w35"> {lang key='goods::goods.is_hot'} </th>
							
							<!-- {if $use_storage} -->
							<th class="w70" data-toggle="sortby" data-sortby="goods_number"> {lang key='goods::goods.goods_number'} </th>
							<!-- {/if} --> 
						</tr>
					</thead>
					<tbody>
						<!-- {foreach from=$goods_list.goods item=goods}-->
						<tr class="big">
							<td class="center-td">
								<input class="checkbox" type="checkbox" name="checkboxes[]" value="{$goods.goods_id}"/>
							</td>						
							<td>
								<a href="{url path='goods/admin/edit' args="goods_id={$goods.goods_id}"}" title="Image 10" >
									<img class="thumbnail" alt="{$goods.goods_name}" src="{$goods.goods_thumb}">
								</a>
							</td>
							<td class="hide-edit-area {if $goods.is_promote}ecjiafc-red{/if}">
								<span  class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin/edit_goods_name')}" data-name="goods_edit_name" data-pk="{$goods.goods_id}" data-title="请输入商品名称"> 
									{$goods.goods_name|escape:html} 
								</span>
								<br/>
								<div class="edit-list">
									<a class="data-pjax" href='{url path="goods/admin/edit" args="goods_id={$goods.goods_id}"}'>{lang key='system::system.edit'}</a>&nbsp;|&nbsp;
									<a class="data-pjax" href='{url path="goods/admin/edit_goods_attr" args="goods_id={$goods.goods_id}"}'>{lang key='goods::goods.tab_properties'}</a>&nbsp;|&nbsp;
									<a class="data-pjax" href='{url path="goods/admin_gallery/init" args="goods_id={$goods.goods_id}"}'>{lang key='goods::goods.tab_gallery'}</a>&nbsp;|&nbsp;
									<a class="data-pjax" href='{url path="goods/admin/edit_link_goods" args="goods_id={$goods.goods_id}"}'>{lang key='goods::goods.tab_linkgoods'}</a>&nbsp;|&nbsp;
									<a class="data-pjax" href='{url path="goods/admin/edit_link_article" args="goods_id={$goods.goods_id}"}'>{lang key='goods::goods.tab_article'}</a>&nbsp;|&nbsp;
									<a class="data-pjax" href='{url path="goods/admin/edit_link_parts" args="goods_id={$goods.goods_id}"}'>{lang key='goods::goods.tab_groupgoods'}</a>&nbsp;|&nbsp;
									<a target="_blank" href='{url path="goods/admin/preview" args="id={$goods.goods_id}"}'>{lang key='goods::goods.preview'}</a>&nbsp;|&nbsp;
									{if $specifications[$goods.goods_type] neq ''}<a target="_blank" href='{url path="goods/admin/product_list" args="goods_id={$goods.goods_id}"}'>{lang key='goods::goods.product_list'}</a>&nbsp;|&nbsp;{/if}
									<a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg="{lang key='goods::goods.trash_goods_confirm'}" href='{url path="goods/admin/remove" args="id={$goods.goods_id}"}'>{lang key='system::system.drop'}</a>
								</div>
							</td>	
							
							<td>
								<!-- {if $goods.shop_name} -->
							    <font style="color:#F00;">{$goods.shop_name}</font>
							    <!-- {else} -->
							    <font style="color:#0e92d0;">{t}自营{/t}</font>
							    <!-- {/if} -->
							</td>	
							
							<td>
								<span class="cursor_pointer review_static" data-trigger="editable" data-value="{$goods.review_status}" data-type="select"  data-url="{RC_Uri::url('goods/admin/review')}" data-name="sort_order" data-pk="{$goods.goods_id}" data-title="请选择审核状态">
									<!--{if $goods.review_status eq 1}-->未审核<!-- {/if} -->
									<!--{if $goods.review_status eq 2}-->审核未通过<!-- {/if} -->
									<!--{if $goods.review_status eq 3 || $goods.review_status eq 4}-->审核已通过<!-- {/if} -->
									<!--{if $goods.review_status eq 5}-->无需审核<!-- {/if} -->
								</span>
							</td>
							
							<td>
								<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin/edit_goods_sn')}" data-name="goods_edit_goods_sn" data-pk="{$goods.goods_id}" data-title="请输入商品货号">
									{$goods.goods_sn} 
								</span>
							</td>
							<td align="right">
								<span  class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin/edit_goods_price')}" data-name="goods_price" data-pk="{$goods.goods_id}" data-title="请输入商品价格"> 
									{$goods.shop_price}
								</span> 
							</td>
							<td align="center">
								<span  class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin/edit_sort_order')}" data-name="sort_order" data-pk="{$goods.goods_id}" data-title="请输入排序序号"> 
									{$goods.sort_order}
								</span>
							</td>
							<td align="center">
								<i class="{if $goods.is_on_sale}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/admin/toggle_on_sale')}" data-id="{$goods.goods_id}"></i>
							</td>
							<td align="center">
								<i class="{if $goods.is_best}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/admin/toggle_best')}" data-id="{$goods.goods_id}"></i>
							</td>
							<td align="center">
								<i class="{if $goods.is_new}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/admin/toggle_new')}" data-id="{$goods.goods_id}"></i>
							</td>
							<td align="center">
								<i class="{if $goods.is_hot}fontello-icon-ok cursor_pointer{else}fontello-icon-cancel cursor_pointer{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/admin/toggle_hot')}" data-id="{$goods.goods_id}"></i>
							</td>
						
							<!-- {if $use_storage} -->
							<td align="right">
								<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/admin/edit_goods_number')}" data-name="goods_number" data-pk="{$goods.goods_id}" data-title="请输入库存数量">
									{$goods.goods_number}
								</span>
							</td>
							<!-- {/if} -->
						</tr>
						<!-- {foreachelse}-->
						<tr>
							<td class="no-records" colspan="13">{lang key='system::system.no_records'}</td>
						</tr>
						<!-- {/foreach} -->
					</tbody>
				</table>
				<!-- {$goods_list.page} --> 
			</div>
		</form>
	</div>
</div>
<!-- {/block} -->