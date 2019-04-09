<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!--{extends file="ecjia-merchant.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.merchant.goods_list.init();
</script>
<!-- {/block} -->

<!-- {block name="home-content"} -->

<div class="modal fade" id="movetype">
	<div class="modal-dialog">
    	<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
				<h4 class="modal-title">{t domain="goods"}转移商品至分类{/t}</h4>
			</div>
			<div class="modal-body h400">
				<form class="form-horizontal" method="post" name="batchForm">
					<div class="form-group ecjiaf-tac">
						<div>
							<select class="noselect w200 ecjiaf-ib form-control" size="15" name="target_cat">
							<!-- {if $cat_list} -->
								<!-- {foreach from=$cat_list item=cat} -->
								<option value="{$cat.cat_id}" {if $cat.level}style="padding-left:{$cat.level * 20}px"{/if}>{$cat.cat_name}</option>
								<!-- {/foreach} -->
							<!-- {else} -->
								<option value="0">{t domain="goods"}暂无任何分类{/t}</option>
							<!-- {/if} -->
							</select>
						</div>
					</div>
					<div class="form-group t_c">
						<a class="btn btn-primary m_l5 disabled" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=move_to&" data-msg="{t domain="goods"}是否将选中商品转移至分类？{/t}" data-noSelectMsg="{t domain="goods"}请选择要转移的商品{/t}" href="javascript:;" name="move_cat_ture">{t domain="goods"}开始转移{/t}</a>
					</div>
				</form>
           </div>
		</div>
	</div>
</div>

<div class="page-header">
	<div class="pull-left">
		<h2><!-- {if $ur_here}{$ur_here}{/if} --></h2>
  	</div>
  	<div class="pull-right">
  		{if $action_link}
		<a href="{$action_link.href}" class="btn btn-primary data-pjax">
			<i class="fa fa-plus"></i> {$action_link.text}
		</a>
		<a href="{url path='goodslib/merchant/init'}" class="btn btn-info">
			<i class="fa fa-plus"></i> {t domain="goods"}一键导入{/t}
		</a>
		{/if}
  	</div>
  	<div class="clearfix"></div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-body panel-body-small">
				<ul class="nav nav-pills pull-left">
					<li class="{if !$smarty.get.type}active{/if}">
						<a class="data-pjax" href="{RC_Uri::url('goods/merchant/init')}
							{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}
							{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}
							{if $filter.keywords}&keywords={$filter.keywords}{/if}
							{if $filter.review_status}&review_status={$filter.review_status}{/if}
							">
							{t domain="goods"}全部{/t}
							<span class="badge badge-info">{$goods_list.filter.count_goods_num}</span>
						</a>
					</li>
					
					<li class="{if $smarty.get.type eq 1}active{/if}">
						<a class="data-pjax" href='{RC_Uri::url("goods/merchant/init", "type=1
							{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}
							{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}
							{if $filter.keywords}&keywords={$filter.keywords}{/if}
							{if $filter.review_status}&review_status={$filter.review_status}{/if}
							")}'>{t domain="goods"}已上架{/t}
							<span class="badge badge-info use-plugins-num">{$goods_list.filter.count_on_sale}</span>
						</a>
					</li>
					
					<li class="{if $smarty.get.type eq 2}active{/if}">	
						<a class="data-pjax" href='{RC_Uri::url("goods/merchant/init", "type=2
							{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}
							{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}
							{if $filter.keywords}&keywords={$filter.keywords}{/if}
							{if $filter.review_status}&review_status={$filter.review_status}{/if}
							")}'>{t domain="goods"}未上架{/t}
							<span class="badge badge-info unuse-plugins-num">{$goods_list.filter.count_not_sale}</span>
						</a>
					</li>
				</ul>	
				<div class="clearfix"></div>
			</div>
			
			<div class="panel-body panel-body-small">
				<div class="btn-group f_l">
					<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cogs"></i> {t domain="goods"}批量操作{/t} <span class="caret"></span></button>
					<ul class="dropdown-menu">
		                <li><a class="batch-trash-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=trash&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品放入回收站吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要移至回收站的商品{/t}" href="javascript:;"><i class="fa fa-archive"></i> {t domain="goods"}移至回收站{/t}</a></li>
						<li><a class="batch-sale-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=on_sale&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品上架吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要上架的商品{/t}" href="javascript:;"><i class="fa fa-arrow-circle-o-up"></i> {t domain="goods"}上架{/t}</a></li>
						<li><a class="batch-notsale-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_on_sale&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品下架吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要下架的商品{/t}" href="javascript:;"><i class="fa fa-arrow-circle-o-down"></i> {t domain="goods"}下架{/t}</a></li>
						<li><a class="batch-best-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=best&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品设为精品吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择设为精品的商品{/t}" href="javascript:;"><i class="fa fa-star"></i> {t domain="goods"}精品{/t} </a></li>
						<li><a class="batch-notbest-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_best&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品取消精品吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择取消精品的商品{/t}" href="javascript:;"><i class="fa fa-star-o"></i> {t domain="goods"}取消精品{/t}</a></li>
						<li><a class="batch-new-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=new&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品设为新品吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要设为新品的商品{/t}" href="javascript:;"><i class="fa fa-flag"></i> {t domain="goods"}新品{/t}</a></li>
						<li><a class="batch-notnew-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_new&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品取消新品吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要取消新品的商品{/t}" href="javascript:;"><i class="fa fa-flag-o"></i> {t domain="goods"}取消新品{/t}</a></li>
						<li><a class="batch-hot-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=hot&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品设为热销吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要设为热销的商品{/t}" href="javascript:;"><i class="fa fa-thumbs-up"></i> {t domain="goods"}热销{/t}</a></li>
						<li><a class="batch-nothot-btn" data-toggle="ecjiabatch" data-idClass=".checkbox:checked" data-url="{$form_action}&type=not_hot&is_on_sale={$goods_list.filter.is_on_sale}&page={$smarty.get.page}" data-msg="{t domain="goods"}您确定要把选中的商品取消热销吗？{/t}" data-noSelectMsg="{t domain="goods"}请选择要取消热销的商品{/t}" href="javascript:;"><i class="fa fa-thumbs-o-up"></i> {t domain="goods"}取消热销{/t}</a></li>
						<li><a class="batch-move-btn" data-name="move_cat" data-move="data-operatetype" href="javascript:;"><i class="fa fa-mail-forward"></i> {t domain="goods"}转移到分类{/t}</a></li>
		           	</ul>
				</div>
				
				<form class="form-inline f_l m_l5" action="{RC_Uri::url('goods/merchant/init')}{if $smarty.get.type}&type={$smarty.get.type}{/if}" method="post" name="filter_form">
					<div class="screen f_l">
						<div class="form-group">
							<select class="w130" name="review_status">
								<option value="-1">{t domain="goods"}请选择...{/t}</option>
								<option value="1" {if $filter.review_status eq 1}selected{/if}>{t domain="goods"}未审核{/t}</option>
								<option value="2" {if $filter.review_status eq 2}selected{/if}>{t domain="goods"}审核未通过{/t}</option>
								<option value="3" {if $filter.review_status eq 3}selected{/if}>{t domain="goods"}已审核{/t}</option>
								<option value="5" {if $filter.review_status eq 5}selected{/if}>{t domain="goods"}无需审核{/t}</option>
							</select>
						</div>
						<button class="btn btn-primary filter-btn" type="button"><i class="fa fa-search"></i> {t domain="goods"}筛选{/t} </button>
					</div>
				</form>
				
				<form class="form-inline f_r" action="{RC_Uri::url('goods/merchant/init')}" method="post" name="search_form">
					<div class="screen f_r">
						<div class="form-group">
							<select class="w130" name="cat_id">
								<option value="0">{t domain="goods"}所有分类{/t}</option>
								<!-- {foreach from=$cat_list item=cat} -->
								<option value="{$cat.cat_id}" {if $cat.cat_id == $smarty.get.cat_id}selected{/if} {if $cat.level}style="padding-left:{$cat.level * 20}px"{/if}>{$cat.cat_name}</option>
								<!-- {/foreach} -->
							</select>
						</div>
						<div class="form-group">
							<select class="w130" name="intro_type">
								<option value="0">{t domain="goods"}全部{/t}</option>
								<!-- {foreach from=$intro_list item=list key=key} -->
								<option value="{$key}" {if $key == $smarty.get.intro_type}selected{/if}>{$list}</option>
								<!-- {/foreach} -->
							</select>
						</div>
						<div class="form-group">
							<input type="text" class="form-control" name="keywords" value="{$smarty.get.keywords}" placeholder="{t domain="goods"}请输入商品关键字{/t}">
						</div>
						<button class="btn btn-primary screen-btn" type="button"><i class="fa fa-search"></i> {t domain="goods"}搜索{/t} </button>
					</div>
				</form>
			</div>
			
			<div class="panel-body panel-body-small">
				<section class="panel">
					<table class="table table-striped table-hover table-hide-edit ecjiaf-tlf">
						<thead>
							<tr data-sorthref='{RC_Uri::url("goods/merchant/init", "{if $smarty.get.type}&type={$smarty.get.type}{/if}")}'>
								<th class="table_checkbox check-list w30">
									<div class="check-item">
										<input id="checkall" type="checkbox" name="select_rows" data-toggle="selectall" data-children=".checkbox"/>
										<label for="checkall"></label>
									</div>
								</th>
								<th class="w100 text-center">{t domain="goods"}缩略图{/t}</th>
								<th data-toggle="sortby" data-sortby="goods_id">{t domain="goods"}商品名称{/t}</th>
								<th class="w130 sorting" data-toggle="sortby" data-sortby="goods_sn">{t domain="goods"}货号{/t}</th>
								<th class="w100 sorting text-center" data-toggle="sortby" data-sortby="shop_price">{t domain="goods"}价格{/t}</th>
								<th class="w70 text-center"> {t domain="goods"}上架{/t} </th>
								<th class="w70 text-center"> {t domain="goods"}精品{/t} </th>
								<th class="w70 text-center"> {t domain="goods"}新品{/t} </th>
								<th class="w70 text-center"> {t domain="goods"}热销{/t} </th>
								<!-- {if $use_storage} -->
								<th class="w70 text-center" data-toggle="sortby" data-sortby="goods_number"> {t domain="goods"}库存{/t} </th>
								<!-- {/if} --> 
								<th class="w70 sorting text-center" data-toggle="sortby" data-sortby="store_sort_order">{t domain="goods"}排序{/t}</th>
							</tr>
						</thead>
						<tbody>
							<!-- {foreach from=$goods_list.goods item=goods}-->
							<tr>
								<td class="check-list">
									<div class="check-item">
										<input id="check_{$goods.goods_id}" class="checkbox" type="checkbox" name="checkboxes[]" value="{$goods.goods_id}"/>
										<label for="check_{$goods.goods_id}"></label>
									</div>
								</td>						
								<td>
									<a href='{url path="goods/merchant/edit" args="goods_id={$goods.goods_id}"}'>
										<img class="w80 h80" alt="{$goods.goods_name}" src="{$goods.goods_thumb}">
									</a>
								</td>
								<td class="hide-edit-area {if $goods.is_promote}ecjiafc-red{/if}">
                                    {if $specifications[$goods.goods_type] neq ''}<span class="label-orange">{t domain="goods"}多货品{/t}</span>{/if}
                                    <span class="cursor_pointer ecjiaf-pre ecjiaf-wsn" data-text="textarea" data-trigger="editable" data-url="{RC_Uri::url('goods/merchant/edit_goods_name')}" data-name="goods_edit_name" data-pk="{$goods.goods_id}" data-title="{t domain="goods"}请输入商品名称{/t}">{$goods.goods_name|escape:html}</span>
									<br/>
									<div class="edit-list">
										<a class="data-pjax" href='{url path="goods/merchant/edit" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}编辑{/t}</a>&nbsp;|&nbsp;
										<a class="data-pjax" href='{url path="goods/merchant/edit_goods_desc" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}商品描述{/t}</a>&nbsp;|&nbsp;
										<a class="data-pjax" href='{url path="goods/merchant/edit_goods_attr" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}商品属性{/t}</a>&nbsp;|&nbsp;
										<a class="data-pjax" href='{url path="goods/mh_gallery/init" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}商品相册{/t}</a>&nbsp;|&nbsp;
										<a class="data-pjax" href='{url path="goods/merchant/edit_link_goods" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}关联商品{/t}</a>&nbsp;|&nbsp;
<!-- 										<a class="data-pjax" href='{url path="goods/merchant/edit_link_parts" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}关联配件{/t}</a>&nbsp;|&nbsp; -->
										<a class="data-pjax" href='{url path="goods/merchant/edit_link_article" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}关联文章{/t}</a>&nbsp;|&nbsp;
										<a target="_blank" href='{url path="goods/merchant/preview" args="id={$goods.goods_id}"}'>{t domain="goods"}预览{/t}</a>&nbsp;|&nbsp;
										{if $specifications[$goods.goods_type] neq ''}<a target="_blank" href='{url path="goods/merchant/product_list" args="goods_id={$goods.goods_id}"}'>{t domain="goods"}货品列表{/t}</a>&nbsp;|&nbsp;{/if}
										<a class="ajaxremove ecjiafc-red" data-toggle="ajaxremove" data-msg="{t domain="goods"}您确定要把该商品放入回收站吗？{/t}" href='{url path="goods/merchant/remove" args="id={$goods.goods_id}"}'>{t domain="goods"}删除{/t}</a>
									</div>
								</td>	
								
								<td>
									<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/merchant/edit_goods_sn')}" data-name="goods_edit_goods_sn" data-pk="{$goods.goods_id}" data-title="{t domain="goods"}请输入商品货号{/t}">
										{$goods.goods_sn} 
									</span>
								</td>
								<td align="center">
									<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/merchant/edit_goods_price')}" data-name="goods_price" data-pk="{$goods.goods_id}" data-title="请输入商品价格"> 
										{$goods.shop_price}
									</span> 
								</td>
								<td align="center">
									<i class="cursor_pointer fa {if $goods.is_on_sale}fa-check {else}fa-times{/if}" data-trigger="toggle_on_sale" data-url="{RC_Uri::url('goods/merchant/toggle_on_sale')}" 
									refresh-url="{RC_Uri::url('goods/merchant/init')}{if $filter.type}&type={$filter.type}{/if}{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}{if $filter.review_status}&review_status={$filter.review_status}{/if}{if $smarty.get.page}&page={$smarty.get.page}{/if}" data-id="{$goods.goods_id}"></i>
								</td>
								<td align="center">
									<i class="cursor_pointer fa {if $goods.store_best}fa-check {else}fa-times{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/merchant/toggle_best')}" refresh-url="{RC_Uri::url('goods/merchant/init')}
									{if $filter.type}&type={$filter.type}{/if}
        							{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}
        							{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}
        							{if $filter.keywords}&keywords={$filter.keywords}{/if}
        							{if $filter.review_status}&review_status={$filter.review_status}{/if}
        							{if $smarty.get.page}&page={$smarty.get.page}{/if}" data-id="{$goods.goods_id}"></i>
								</td>
								<td align="center">
									<i class="cursor_pointer fa {if $goods.store_new}fa-check {else}fa-times{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/merchant/toggle_new')}" refresh-url="{RC_Uri::url('goods/merchant/init')}
									{if $filter.type}&type={$filter.type}{/if}
        							{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}
        							{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}
        							{if $filter.keywords}&keywords={$filter.keywords}{/if}
        							{if $filter.review_status}&review_status={$filter.review_status}{/if}
        							{if $smarty.get.page}&page={$smarty.get.page}{/if}" data-id="{$goods.goods_id}"></i>
								</td>
								<td align="center">
									<i class="cursor_pointer fa {if $goods.store_hot}fa-check {else}fa-times{/if}" data-trigger="toggleState" data-url="{RC_Uri::url('goods/merchant/toggle_hot')}" refresh-url="{RC_Uri::url('goods/merchant/init')}
									{if $filter.type}&type={$filter.type}{/if}
        							{if $filter.cat_id}&cat_id={$filter.cat_id}{/if}
        							{if $filter.intro_type}&intro_type={$filter.intro_type}{/if}
        							{if $filter.keywords}&keywords={$filter.keywords}{/if}
        							{if $filter.review_status}&review_status={$filter.review_status}{/if}
        							{if $smarty.get.page}&page={$smarty.get.page}{/if}" data-id="{$goods.goods_id}"></i>
								</td>
								<!-- {if $use_storage} -->
								<td align="center">
									<span class="cursor_pointer" data-trigger="editable" data-url="{RC_Uri::url('goods/merchant/edit_goods_number')}" data-name="goods_number" data-pk="{$goods.goods_id}" data-title="{t domain="goods"}请输入库存数量{/t}">
										{$goods.goods_number}
									</span>
								</td>
								<!-- {/if} -->
								<td align="center">
									<span class="cursor_pointer" data-trigger="editable" data-placement="left" data-url="{RC_Uri::url('goods/merchant/edit_sort_order')}" data-name="sort_order" data-pk="{$goods.goods_id}" data-title="{t domain="goods"}请输入排序序号{/t}">
										{$goods.store_sort_order}
									</span>
								</td>
							</tr>
							<!-- {foreachelse}-->
							<tr>
								<td class="no-records" colspan="11">{t domain="goods"}没有找到任何记录{/t}</td>
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