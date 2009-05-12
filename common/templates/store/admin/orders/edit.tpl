<script type="text/javascript" src="/static/js/ajax.js" /></script>
<!--script type="text/javascript" src="/js/processOrder.js" /></script-->

{assign var='order' value=$module.order}

<h3>Order #{$order.order_id}</h3>
<h3>Date: {$order.order_time}</h3>
	
{literal}
	<script type="text/javascript">
		function returnToList(responseObject, responseElement) {
			if (responseObject != null) {
				switch (responseObject.status) {
					case 'Success':
						document.location.href = '/store/admin/orders';
						break;

					default:
						//alert(responseObject.message);
						break;
				}
			}

			var responseMessage = document.createTextNode(responseObject.message);
			responseElement.className = responseObject.type;
			responseElement.appendChild(responseMessage);

			return responseElement;
		}
	</script>
	<style>
		form div { margin-top: 10px }
		dd, dt { float: left }
		dt { clear: left; width: 90px; padding-right: 10px }
		dl.status dt { width: 120px }
	</style>
{/literal}

<form method="post" action="/store/admin/orders/save">
	{if $order.total_amount > 0}
		<div class="float-left">
			<b>Bill To:</b><br />
			{if $order.billing_company}{$order.billing_company}<br />{/if}
			{$order.billing_first_name} {$order.billing_last_name}<br />
			{$order.billing_address1}<br />
			{if $order.billing_address2}{$order.billing_address2}<br />{/if}
			{$order.billing_city}, {$order.billing_state} {$order.billing_zip_code}<br /><br />
			{$order.billing_phone}
			{if $order.billing_fax != ''}<br />Fax: {$order.billing_fax}{/if}<br /><br />
			<b>Email:</b> {mailto address=$order.email}
		</div>
	{/if}
	<div class="float-left" style="padding-left: 50px">
		<b>Ship To:</b><br />
		{if $order.shipping_company}{$order.shipping_company}<br />{/if}
		{$order.shipping_first_name} {$order.shipping_last_name}<br />
		{$order.shipping_address1}<br />
		{if $order.shipping_address2}{$order.shipping_address2}<br />{/if}
		{$order.shipping_city}, {$order.shipping_state} {$order.shipping_zip_code}<br /><br />
		{$order.shipping_phone}
		{if $order.shipping_fax != ''}<br />Fax: {$order.shipping_fax}{/if}<br /><br />
		<b>Shipping Method:</b> {$order.shipping_method}<br />
		<b>Weight:</b> {$order.weight|number_format:2} lbs
	</div>
	{if $order.total_amount > 0}
		<div class="float-left" style="padding-left: 50px">
			<b>Payment Information:</b><br />
			<dl>
				<dt>Card Type:</dt>
				<dd>{$order.cc_type}</dd>
				<dt>Card Number:</dt>
				<dd>XXXX-XXXX-XXXX-{$order.cc_last4}</dd>
				<dt>Expiration:</dt>
				<dd>{$order.cc_expiration|date_format:'%m/%Y'}</dd>
				<dt>Transaction #:</dt>
				<dd>{$order.transaction_id}</dd>
			</dl>
		</div>
	{/if}
	<br class="clear-left" />
	<div>
		<table class="product-list">
			<tr>
				<th class="product-quantity">Qty.</th>
				<th class="product-sku">SKU</th>
				<th class="product-description">Product Description</th>
				<th class="product-price">Price</th>
				<th class="product-total">Total</th>
			</tr>
			{foreach from=$order.products key=id item=product}
				<tr>
					<td class="product-quantity">{$product.quantity}</td>
					<td class="product-sku" style="text-align: center">{$product.sku}</td>
					<td class="product-description">{$product.name}</td>
					<td class="product-price">
						${$product.price|number_format:2}
					</td>
					<td class="product-total">
						${$product.price*$product.quantity|number_format:2}
					</td>
				</tr>
			{/foreach}
			<tr>
				<td colspan="3">
				</td>
				<td class="right">
					<b>Subtotal:</b><br />
					<b>Shipping:</b><br />
					<b>Total:</b>
				</td>
				<td class="right">
					${$order.total_amount-$order.shipping_amount|number_format:2}<br />
					${$order.shipping_amount}<br />
					${$order.total_amount|number_format:2}<br />
				</td>
			</tr>
		</table>
	</div>
	<br class="clear-left" />
	<div class="float-left" style="width: 500px">
		<dl class="status">
			<dt>Order Status:</dt>
			<dd>{html_options name='status' options=$module.statuses selected=$order.status_id}</dd>
			<dt>Shipping Method:</dt>
			<dd>{html_options name='shipping_method' options=$module.shipping_methods selected=$order.shipping_method}</dd>
			<dt>Tracking Number:</dt>
			<dd><input type="text" name="tracking_number" id="tracking_number" value="{$order.tracking_number}" /></dd>
			<dt>Note:</dt>
			<dd><textarea id="shipping_note" name="shipping_note" style="width: 350px">{$order.shipping_note}</textarea></dd>
		</dl>
	</div>
	<div class="float-right">
		<b>Resend Receipt to:</b> <input type="text" name="email" id="email" value="{$order.email}" /> <input type="button" value="Send" onclick="alert('not yet');"/><br /><br /><br />
	</div>
	<br class="clear-left" />
	<br class="clear-right" />
	<div class="center" style="width: 500px">
		<input type="hidden" name="id" value="{$order.order_id}" />
		<input type="button" value="Save &amp; Return to List" onclick="ajaxRequest(this.parentNode.parentNode, 'returnToList'); return false;" />
		<input type="button" value="Save &amp; Print Packing Slip" onclick="alert('almost'); return false; ajaxRequest(this.parentNode.parentNode); /*, 'print');*/ return false;" />
	</div>
</form>
