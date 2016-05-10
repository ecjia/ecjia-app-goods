<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.link_area.init();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
		<a class="btn plus_or_reply data-pjax" href="{$action_link.href}{if $code}&extension_code={$code}{/if}" id="sticky_a"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="tabbable">
			<ul class="nav nav-tabs">
				<!-- {foreach from=$tags item=tag} -->
				<li {if $tag.active}class="active"{/if}><a{if $tag.active} href="javascript:;"{else}{if $tag.pjax} class="data-pjax"{/if} href='{$tag.href}'{/if}><!-- {$tag.name} --></a></li>
				<!-- {/foreach} -->
			</ul>
			
			<form class="form-horizontal" action='{url path="goods/admin/insert_link_area" args="goods_id={$smarty.get.goods_id}{if $code}&extension_code={$code}{/if}"}' method="post" name="theForm">
				<div class="tab-content">
					<fieldset>
						<div class="control-group choose_list span12" data-url="{url path='goods/admin/get_areaRegion_info_list'}">
							<div class="ecjiaf-cb">
								<!-- <div class="f_l"> -->
									<select class="link_area" name="ra_id">
										<option value="0">所有地区</option>
										<!-- {foreach from=$areaRegion_list item=area} -->
										<option value="{$area.ra_id}">{$area.ra_name}</option>
               							<!--  {/foreach} -->
									</select>
								<a class="btn" data-toggle="searcharea"><!-- {$lang.button_search} --></a>
							</div>
						</div>
						<div class="control-group draggable">
							<div class="ms-container " id="ms-custom-navigation">
								<div class="ms-selectable">
									<div class="search-header">
										<input class="span12" id="ms-search" type="text" placeholder="{t}筛选搜索到的地区信息{/t}" autocomplete="off">
									</div>
									<ul class="ms-list nav-list-ready">
										<li class="ms-elem-selectable disabled"><span>暂无内容</span></li>
									</ul>
								</div>
								<div class="ms-selection">
									<div class="custom-header custom-header-align">关联地区</div>
									<ul class="ms-list nav-list-content">
										<!-- {foreach from=$link_area item=val key=key} -->
										<li class="ms-elem-selection">
											<input type="hidden" value="{$val.regionId}" name="ragion[]" />
											{$val.region_name}
											<span class="edit-list"><i class="fontello-icon-minus-circled ecjiafc-red del"></i></span>
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
				</fieldset>
			</form>
		</div>
	</div>
</div>
<!-- {/block} -->