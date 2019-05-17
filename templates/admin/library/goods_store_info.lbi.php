<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<div class="row-fluid preview-merchant-info">
	<div class="merchant-info">
		<div class="span5 merchant-info-logo">
			<div class="shop-logo-thumb">
		        <img src="{$goods.shop_logo}" >
		    </div>
		    <div class="goods-preview-store-info">
		        {$goods.merchants_name}
		        <div style="text-align:center;margin-top:15px;"><span class="goods-promote">{t domain='goods'}{$goods.manage_mode}{/t}</span></div>
		    </div>
		</div>
		<div class="separate-line"></div>
		<div class="span7 merchant-info-name">
			<div class="store-other-info">
	        	{t domain="goods"}营业时间：{/t}
	        	<span class="store-grade">{$goods.shop_trade_time}</span>
	    	</div>
	        <div class="store-other-info">
	        	{t domain="goods"}商家电话：{/t}
	        	<span class="store-grade">{$goods.shop_kf_mobile}</span>
	        </div>
	        <div class="store-other-info">
	        	{t domain="goods"}商家地址：{/t}
	        	<span class="store-grade">{$goods.shop_address}</span>
	        </div>
		</div>
	</div>
</div>