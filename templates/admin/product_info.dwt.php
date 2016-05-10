<?php defined('IN_ECJIA') or exit('No permission resources.');?>
<!-- {extends file="ecjia.dwt.php"} -->

<!-- {block name="footer"} -->
<script type="text/javascript">
	ecjia.admin.product.init();
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

            <form class="form-horizontal" action="{$form_action}{if $code}&extension_code={$code}{/if}" method="post" enctype="multipart/form-data" name="theForm">
                <div class="row-fluid">
                    <div class="control-group formSep">
                        <table>
                            <tr>
                                <!-- {foreach from=$attribute item=attribute_value} -->
                                <td>{$attribute_value.attr_name}</td>
                                <!--  {/foreach} -->
                                <td>{$lang.goods_sn}</td>
                                <td>{$lang.goods_number}</td>
                                <td>&nbsp;</td>
                            </tr>

                            <!-- {foreach from=$product_list item=product name=foo_product} -->
                            <tr class="attr_row">
                                <!-- {foreach from=$attribute item=attribute_value key=attribute_key} -->
                                <td>
                                    <select name="attr[{$attribute_value.attr_id}][]">
                                    	<option value="" selected>{$lang.select_please}</option>
                                        <!-- {foreach from=$attribute_value.attr_values item=value} -->
                                          	<!-- {foreach $product.goods_attr item=goods_attr} -->
                                            <option value="{$value}"{if $goods_attr eq $value}selected="selected"{/if}>{$value}</option>
                                        	<!-- {/foreach} -->
                                        <!-- {/foreach} -->
                                    </select>
                                </td>
                                <!-- {/foreach} -->
                                <td><input type="text" name="product_sn[]" value="{$product.product_sn}" size="20"/></td>
                                <td><input type="text" name="product_number[]" value="{$product.product_number}" size="10"/></td>
                                <!-- {if $smarty.foreach.foo_product.first} -->
                                <td><a class="no-underline" data-toggle="clone-obj" data-parent=".attr_row" href="javascript:;"><i class="fontello-icon-plus"></i></a> </td>
                                <!-- {else} -->
                                <td><a class="no-underline" data-toggle="remove-obj" data-parent=".attr_row" href="javascript:;"><i class="fontello-icon-cancel ecjiafc-red"></i></a> </td>
                                <!-- {/if} -->
                            </tr>
                            <!-- {foreachelse} -->
                            <tr class="attr_row">
                                <!-- {foreach from=$attribute item=attribute_value key=attribute_key} -->
                                <td>
                                    <select name="attr[{$attribute_value.attr_id}][]">
                                        <option value="" selected>{$lang.select_please}</option>
                                        <!-- {foreach from=$attribute_value.attr_values item=value} -->
                                        <option value="{$value}">{$value}</option>
                                        <!-- {/foreach} -->
                                    </select>
                                </td>
                                <!-- {/foreach} -->
                                <td><input type="text" name="product_sn[]" value="{$product.product_sn}" size="20"/></td>
                                <td><input type="text" name="product_number[]" value="{$product.product_number}" size="10"/></td>
                                <td><a class="no-underline" data-toggle="clone-obj" data-parent=".attr_row" href="javascript:;"><i class="fontello-icon-plus"></i></a> </td>
                            </tr>
                            <!-- {/foreach} -->
                        </table>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <input type="hidden" name="goods_id" value="{$goods_id}" />
                        <input type="hidden" name="act" value="product_add_execute" />
                        <input type="submit" name="submit" value="{t}保存{/t}" class="btn btn-gebo" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- {/block} -->