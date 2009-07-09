<script type="text/javascript" src="/static/js/ajax.js" /></script>

{literal}
	<script type="text/javascript">
		// @todo Move this to an external file so that logic isn't in the template
		function clearForm(responseObject, responseElement) {
			if (responseObject != null) {
				switch (responseObject.status) {
					case 'Success':
						/*
						document.getElementById('contact_company').value    = '';
						document.getElementById('contact_first_name').value = '';
						document.getElementById('contact_last_name').value  = '';
						document.getElementById('contact_address1').value   = '';
						document.getElementById('contact_address2').value   = '';
						document.getElementById('contact_city').value       = '';
						document.getElementById('contact_state').value      = '';
						document.getElementById('contact_zip_code').value   = '';
						document.getElementById('contact_phone').value      = '';
						document.getElementById('contact_fax').value        = '';

						document.getElementById('payee_company').value      = '';
						document.getElementById('payee_first_name').value   = '';
						document.getElementById('payee_last_name').value    = '';
						document.getElementById('payee_address1').value     = '';
						document.getElementById('payee_address2').value     = '';
						document.getElementById('payee_city').value         = '';
						document.getElementById('payee_state').value        = '';
						document.getElementById('payee_zip_code').value     = '';
						document.getElementById('payee_phone').value        = '';
						document.getElementById('payee_fax').value          = '';

						document.getElementById('email').value              = '';
						document.getElementById('tax_id').value             = '';
						document.getElementById('tax_class').value          = '';
						document.getElementById('commission_rate').value    = '';
						*/
						
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
		form div {
			margin-top: 10px;
		}
		dl {
			margin-top: 5px;
			width: 650px;
		}
		dl dt {
			float: left;
			padding-top: 4px;
			text-align: right;
			width: 100px;
		}
		dl dd {
			float: left;
			width: 510px;
			padding: 2px;
		}
		dl dd input {
			width: 250px;
		}
		dl dd textarea {
			width: 500px;
			height: 50px;
		}

		table#applicable, table#rules { 
			margin-top: 0px
		}
		table#applicable td, table#rules td {
			padding: 0px;
		}
	</style>
{/literal}
<h3>{if isset($module.discount.id)}Update{else}Add{/if} Discount</h3>
<form method="post" action="/store/admin/discounts/save">
	<div class="float-left">
		<dl>
			<dt><span class="pink">*</span>Name:</dt>
			<dd>
				<input type="text" name="name" id="name" style="margin-right: 53px" value="{$module.discount.name}" title="required" />
				Coupon Code:
				<input type="text" name="coupon" id="coupon" style="width: 100px;" value="{$module.discount.coupon}" /> 
			</dd>
			<dt>Description:</dt>
			<dd><textarea name="description" id="description" style="height: 100px">{$module.discount.description}</textarea></dd>
			<dt><span class="pink">*</span>Valid From:</dt>
			<dd>
				{html_select_date prefix='valid_from_'}
				to
				{html_select_date prefix='valid_through_'}
			</dd>
			<dt><span class="pink">*</span>Applicable:</dt>
			<dd>
				<table cellspacing="0" cellpadding="0" id="applicable">
					<tr>
						<td><input type="checkbox" checked="checked" style="" disabled="disabled" /> All Customers</td>
						<!--td><input type="checkbox" checked="checked" style="" disabled="disabled" /> All Categories</td-->
						<td><input type="checkbox" checked="checked" style="" disabled="disabled" /> All Products</td>
					</tr>
					<tr>
						<td>(CTRL + click to select multiple)</td>
						<!--td>(CTRL + click to select multiple)</td-->
						<td>(CTRL + click to select multiple)</td>
					</tr>
					<tr>
						<td style="vertical-align: top; padding-right: 10px;">
							<select multiple="multiple" id="customers" name="customers" style="height: 200px; width: 250px;" disabled="disabled">
								{html_options options=$module.customers}
							</select>
						</td>
						<!--td style="vertical-align: top; padding-right: 10px;">
							<select multiple="multiple" id="categories" name="categories" style="height: 200px; width: 230px;" disabled="disabled">
								{html_options options=$module.categories|truncate:32}
							</select>
						</td-->
						<td style="vertical-align: top;">
							<select multiple="multiple" id="products" name="products" style="height: 200px; width: 450px;">
								{html_options options=$module.products}
							</select>
						</td>
					</tr>
				</table>
			</dd>
			<dt>Max Usage:</dt>
			<dd>
				<input type="text" style="width: 40px" name="max_customer_usage" />
				Per Customer
				<input type="text" style="width: 40px" name="max_order_usage" />
				Per Order
			</dd>
			<dt><span class="pink">*</span>Remaining:</dt>
			<dd>
				<input type="radio" checked="checked" name="remaining_usages" value="unlimited" /> Unlimited <input type="radio" name="remaining_usages" value="other" /> Other <input type="text" style="width: 60px;" name="remaining_usages_count" /> Uses <span style="color: #666; margin-left: 20px;">Already Used {$module.discount.usage_count} times</span>
				<input type="hidden" name="usage_count" value="{$module.discount.usage_count}" />
			</dd>
			<dt><span class="pink">*</span>Rules:</dt>
			<dd>
				<table id="rules" style="width: 700px">
					<tr>
						<th>Applied To</td>
						<th>Amount</th>
						<th>Min Subtotal</th>
						<th>Min Items</th>
						<th>Max Discount</th>
						<th>
							<img src="/static/contrib/silk/icons/add.png" onclick="alert('The ability to add more than one rule to the discount is currently unavailable');" />
						</th>
					</tr>
					<tr>
						<td class="center">
							<select name="applied_to" id="applied_to">
								{html_options options=$module.applied_to_options selected=$module.rules.0.applied_to}
							</select>
						</td>
						<td class="center">
							<select name="amount_type" id="amount_type">
								{html_options options=$module.amount_type_options selected=$module.rules.0.amount_type}
							</select>
							<input type="text" name="amount" id="amount" style="width: 60px" value="{$module.rules.0.amount}" />
						</td>
						<td class="center">$<input type="text" name="min_subtotal" id="minimum_subtotal" style="width: 60px" value="{$module.rules.0.min_subtotal}" /></td>
						<td class="center"><input type="text" name="min_items" id="minimum_items" style="width: 60px" value="{$module.rules.0.min_items}" /></td>
						<td class="center">$<input type="text" name="max_discount" id="maximum_discount" style="width: 60px" value="{$module.rules.0.max_discount}" /></td>
					</tr>
				</table>
			</dd>
		</dl>
	</div>
	<br style="clear: left" /><br />
	<div class="center">
		{if isset($module.discount.id)}
			<input type="hidden" name="id" value="{$module.discount.id}" />
			<input type="hidden" name="sequence" value="{$module.discount.sequence}" />
		{/if}
		<input type="reset" value="Reset Form" /><input type="button" value="Store Information" onclick="ajaxRequest(this.parentNode.parentNode{if !isset($module.discount.id)}, 'clearForm'{/if}); return false;" />
	</div>
</form>
{literal}
<script type="text/javascript">
	var select_box   = document.getElementById('products');
	var option_count = select_box.options.length;

	for (var i = 0; i < option_count; i++) {
		{/literal}
		{foreach from=$module.xrefs.PRODUCT item=product_id}
			if (select_box.options[i].value == {$product_id}) {literal}{{/literal}
				select_box.options[i].selected = 'selected';
			{literal}}{/literal}
		{/foreach}
		{literal}
	}
</script>
{/literal}
