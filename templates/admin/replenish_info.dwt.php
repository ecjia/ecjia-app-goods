<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.replenish.edit();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		{if $action_link}
		<a href="{$action_link.href}" class="btn plus_or_reply data-pjax"><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		{/if}
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<div class="tabbable">
			<ul class="nav nav-tabs">
				<li class="tab active"><a href="#tab1">{t}批量补货{/t}</a></li>
				<li class="tab"><a class="data-pjax" href='{RC_Uri::url("goods/admin_virtual_card/batch_card_add", "goods_id={$goods_id}")}'>{t}批量上传{/t}</a></li>
			</ul>
		</div>
		<div class="tab-content">
			<div class="tab-pane active" id="tab1">
				<div class="row-fluid">
					<div class="span12">
						<form class="form-horizontal" method="post" action="{$form_action}" name="theForm">
							{if !$smarty.get.card_id}
							<div class="f_legend">{$card.goods_name|escape:html}</div>
							<div class="card_div m_t5">
								<label class="ecjiafd-inline">{$lang.lab_card_sn}：</label>
								<input class="W200" type="text" name="card_sn[]" value="{$card.card_sn}"/>
								<label class="ecjiafd-inline m_l10">{$lang.lab_card_password}：</label>
								<input class="W200" type="text" name="card_password[]" value="{$card.card_password}" />
								<label class="ecjiafd-inline m_l10">{t}截止日期：{/t}</label>
								<input class="date" name="end_date[]" type="text" value="{$card.end_date}" >
								{if !$smarty.get.card_id}
								<span>
									<a class="f_ l_h30 t_l" href="javascript:;" data-toggle="clone-obj" data-parent=".card_div">
										<i class="fontello-icon-plus"></i>
									</a>
								</span>
								{/if}
							</div>
							{else}
							{if $smarty.get.batch}
							<div class="f_legend">{$goods_name|escape:html}</div>
							
								<!-- {foreach from=$card item=batch_card} -->
								<div class="card_div m_t5">
									<label class="ecjiafd-inline">{$lang.lab_card_sn}：</label>
									<input class="W200" type="text" name="card_sn[]" value="{$batch_card.card_sn}"/>
									<label class="ecjiafd-inline m_l10">{$lang.lab_card_password}：</label>
									<input class="W200" type="text" name="card_password[]" value="{$batch_card.card_password}" />
									<label class="ecjiafd-inline m_l10">{t}截止日期：{/t}</label>
									<input class="date" name="end_date[]" type="text" value="{$batch_card.end_date}" >
								</div>
								<!-- {/foreach} -->
							
							{else}
							<div class="f_legend">{$card.goods_name|escape:html}</div>
							<label class="ecjiafd-inline">{$lang.lab_card_sn}：</label>
								<input class="W200" type="text" name="card_sn[]" value="{$card.card_sn}"/>
								<label class="ecjiafd-inline m_l10">{$lang.lab_card_password}：</label>
								<input class="W200" type="text" name="card_password[]" value="{$card.card_password}" />
								<label class="ecjiafd-inline m_l10">{t}截止日期：{/t}</label>
								<input class="date" name="end_date[]" type="text" value="{$card.end_date}" >
							{/if}
							{/if}
							<div class="m_t10">
								<button class="btn btn-gebo" type="submit">{$lang.button_submit}</button>
								<input type="hidden" name="goods_id" value="{$goods_id}" />
								<input type="hidden" name="old_card_sn" value="{$card.card_sn}" />
								<!-- {if $card.card_id} -->
								<input type="hidden" name="card_id" value="{$card.card_id}" />
								<!-- {/if} -->
								<input type="hidden" name="batch" value="{$smarty.get.batch}" />
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- {/block} -->