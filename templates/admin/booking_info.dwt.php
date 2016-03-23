<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
ecjia.admin.goods_booking.info();
</script>
<!-- {/block} -->

<!-- {block name="main_content"} -->
<div>
	<h3 class="heading">
		<!-- {if $ur_here}{$ur_here}{/if} -->
		<!-- {if $action_link} -->
			<a class="btn plus_or_reply data-pjax" href="{$action_link.href}" ><i class="fontello-icon-reply"></i>{$action_link.text}</a>
		<!-- {/if} -->
	</h3>
</div>
<div class="row-fluid edit-page">
	<div class="span12">
		<div id="accordion2" class="foldable-list">
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle acc-in" data-toggle="collapse" href="#collapseOne"><strong>{$lang.booking}</strong></a>
				</div>
				<div class="accordion-body in collapse" id="collapseOne">
					<table class="table table-oddtd m_b0" >
						<tbody class="first-td-no-leftbd"> 
							<tr>
								<td width="25%"><strong>{$lang.user_name}：</strong></td>
								<td width="25%">{$booking.user_name|escape}</td>
								<td width="25%"><strong>{$lang.booking_time}：</strong></td>
								<td width="25%">{$booking.booking_time}</td>
							</tr>
							<tr>
								<td width="25%"><strong>{$lang.goods_name}：</strong></td>
								<td width="25%"><a href='{url path="goods/admin/preview" args="id={$booking.goods_id}"}' target="_blank" title="查看商品详情">{$booking.goods_name}</a></td>
								<td width="25%"><strong>{$lang.number}：</strong></td>
								<td width="25%">{$booking.goods_number}</td>
							</tr>
							<tr>
								<td width="25%"><strong>{$lang.desc}：</strong></td>
								<td width="75%" colspan="3">{$booking.goods_desc|escape|nl2br}</td>
							</tr>
							<tr>
								<td width="25%"><strong>{$lang.link_man}：</strong></td>
								<td width="25%">{$booking.link_man|escape} {if $booking.email} &lt; {$booking.email|escape} &gt;{/if}</td>
								<td width="25%"><strong>{$lang.tel}：</strong></td>
								<td width="25%">{$booking.tel|escape}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			{if $booking.is_dispose}
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle acc-in" data-toggle="collapse" href="#collapseTwo"><strong>{$lang.dispose_info}</strong></a>
				</div>
				<div class="accordion-body in collapse" id="collapseTwo">
					<table class="table table-oddtd m_b0" >
						<tbody class="first-td-no-leftbd"> 
							<tr>
								<td width="25%"><strong>{$lang.dispose_user}：</strong></td>
								<td width="25%">{$booking.dispose_user}</td>
								<td width="25%"><strong>{$lang.dispose_time}：</strong></td>
								<td width="25%">{$booking.dispose_time}</td>
							</tr>
							<tr>
								<td width="25%"><strong>{$lang.note}：</strong></td>
								<td width="75%" colspan="3">{$booking.dispose_note}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			{/if}
		</div>
		<div class="row-fluid">
			<!-- reply content list -->
			<div class="span12">
				<form class="form-horizontal well" method="post"action="{$form_action}" name="theForm">
					<fieldset>
						<h3 class="heading">
							<strong>{$lang.note}</strong>
						</h3>
						<div class="control-group">
							<textarea cols="30" rows="5" name="dispose_note" class="span10">{$booking.dispose_note}</textarea>
						</div>
						<div class=" chk_radio ">
							<input type="checkbox" name="send_email_notice" value="1" style="opacity: 0;">
							<span class="replyemail">邮件通知</span>&nbsp;&nbsp;
							{if $booking.is_dispose}<a class="ecjiaf-csp" id="sticky_a" data-url="{$form_action}"><strong>{$lang.remail}</strong></a>{/if}
						</div><br>
						<div class="control-group">
							<button class="btn btn-gebo" type="submit">{t}我来处理{/t}</button>
							<input type="hidden" name="rec_id" value="{$booking.rec_id}" >
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- {/block} -->
