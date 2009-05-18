<script type="text/javascript" src="/static/js/ajax.js" /></script>

{assign var='order' value=$module.order}

<h3>Order #{$order.order_id}</h3>
<h3 style="padding-bottom: 15px">Date: {$order.order_time}</h3>
	
{literal}
	<script type="text/javascript">
		function returnToOrders(responseObject, responseElement) {
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
		
		function displayPackingSlip(responseObject, responseElement) {
			if (responseObject != null) {
				switch (responseObject.status) {
					case 'Success':
						var body = document.body;
						body.style.backgroundColor = '#FFF';
						body.style.color = '#000';

						body.innerHTML = responseObject.packing_slip;

						// Launch the print dialog
						window.print();

						// Prompt the user for the print status
						while (!confirm('Did the packing slip print successfully?')) {
							window.print();
						}
						
						// Loads the orders list
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
<div class="float-left" {if $order.total_amount > 0}style="padding-left: 50px"{/if}>
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
	<form method="post" action="">
		<dl class="status">
			<dt>Customer's Email:</dt>
			<dd><input type="text" name="email" id="email" value="{$order.email}" /> <input type="button" value="Resend Last Update" onclick="this.parentNode.parentNode.parentNode.action = '/store/admin/orders/send'; ajaxRequest(this.parentNode.parentNode.parentNode, null, 'after'); return false" /></dd>
			<dt>Order Status:</dt>
			<dd>{html_options name='status' options=$module.status_options selected=$order.status_id}</dd>
			<dt>Shipping Method:</dt>
			<dd>{html_options name='shipping_method' options=$module.shipping_method_options selected=$order.shipping_method}</dd>
			<dt>Tracking Number:</dt>
			<dd><input type="text" name="tracking_number" id="tracking_number" value="{$order.tracking_number}" /></dd>
			<dt>Note:</dt>
			<dd><textarea id="shipping_note" name="shipping_note" style="width: 350px"></textarea></dd>
			<dt>&nbsp;</dt>
			<dd>
				<input type="checkbox" name="email_customer" id="email_customer" checked="checked" /> Send update email to customer
			</dd>
		</dl>
		<br class="clear-left" />
		<br class="clear-right" />
		<div class="center" style="width: 500px">
			<input type="hidden" name="parameter" id="parameter" value="" />
			<input type="hidden" name="id" value="{$order.order_id}" />
			<input type="hidden" name="order" value="{$module.serialized_order|urlencode}" />
			<input type="button" value="Save &amp; Return to List" onclick="this.parentNode.parentNode.action = '/store/admin/orders/save'; ajaxRequest(this.parentNode.parentNode, 'returnToOrders', 'after'); return false;" />
			<input type="button" value="Save &amp; Print Packing Slip" onclick="this.parentNode.parentNode.action = '/store/admin/orders/print'; ajaxRequest(this.parentNode.parentNode, 'displayPackingSlip', 'after'); return false;" />
		</div>
	</form>
</div>
{if is_array($order.updates)}
	<div class="float-right" style="width: 300px;">
		<table style="margin-top: 0px;">
			<tr>
				<th class="left">Status Update</th>
				<th>Date</th>
				<th>Note</th>
			</tr>
			{foreach from=$order.updates item="update"}
				<tr>
					<td>{$module.statuses[$update.status_id]}</td>
					<td class="center">{$update.update_time|date_format:'%m/%d/%Y'}</td>
					<td class="center">{if trim($update.note) != ''}<img src="/static/contrib/silk/icons/note.png" title="{$update.note}" />{/if}</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}
<br class="clear-left" />
<br class="clear-right" />
