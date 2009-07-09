<script type="text/javascript" src="/static/js/ajax.js" /></script>

{literal}
	<script type="text/javascript">
		// @todo Move this to an external file so that logic isn't in the template
		function copyAddress(that) {
			if (that.checked == true) {
				// Copy the values
				document.getElementById('shipping_company').value       = document.getElementById('billing_company').value;
				document.getElementById('shipping_first_name').value    = document.getElementById('billing_first_name').value;
				document.getElementById('shipping_last_name').value     = document.getElementById('billing_last_name').value;
				document.getElementById('shipping_address1').value      = document.getElementById('billing_address1').value;
				document.getElementById('shipping_address2').value      = document.getElementById('billing_address2').value;
				document.getElementById('shipping_city').value          = document.getElementById('billing_city').value;
				document.getElementById('shipping_state').value         = document.getElementById('billing_state').value;
				document.getElementById('shipping_zip_code').value      = document.getElementById('billing_zip_code').value;
				document.getElementById('shipping_phone').value         = document.getElementById('billing_phone').value;
				document.getElementById('shipping_fax').value           = document.getElementById('billing_fax').value;
				
				// Disable the fields
				document.getElementById('shipping_company').disabled    = true; 
				document.getElementById('shipping_first_name').disabled = true; 
				document.getElementById('shipping_last_name').disabled  = true; 
				document.getElementById('shipping_address1').disabled   = true; 
				document.getElementById('shipping_address2').disabled   = true; 
				document.getElementById('shipping_city').disabled       = true; 
				document.getElementById('shipping_state').disabled      = true; 
				document.getElementById('shipping_zip_code').disabled   = true; 
				document.getElementById('shipping_phone').disabled      = true; 
				document.getElementById('shipping_fax').disabled        = true; 
			}
			else {
				// Clear the values
				document.getElementById('shipping_company').value       = '';
				document.getElementById('shipping_first_name').value    = '';
				document.getElementById('shipping_last_name').value     = '';
				document.getElementById('shipping_address1').value      = '';
				document.getElementById('shipping_address2').value      = '';
				document.getElementById('shipping_city').value          = '';
				document.getElementById('shipping_state').value         = '';
				document.getElementById('shipping_zip_code').value      = '';
				document.getElementById('shipping_phone').value         = '';
				document.getElementById('shipping_fax').value           = '';
				
				// Enable the fields
				document.getElementById('shipping_company').disabled    = false; 
				document.getElementById('shipping_first_name').disabled = false; 
				document.getElementById('shipping_last_name').disabled  = false; 
				document.getElementById('shipping_address1').disabled   = false; 
				document.getElementById('shipping_address2').disabled   = false; 
				document.getElementById('shipping_city').disabled       = false; 
				document.getElementById('shipping_state').disabled      = false; 
				document.getElementById('shipping_zip_code').disabled   = false; 
				document.getElementById('shipping_phone').disabled      = false; 
				document.getElementById('shipping_fax').disabled        = false; 
			}
		}

		function clearForm(responseObject, responseElement) {
			if (responseObject != null) {
				switch (responseObject.status) {
					case 'Success':
						document.getElementById('billing_company').value       = '';
						document.getElementById('billing_first_name').value    = '';
						document.getElementById('billing_last_name').value     = '';
						document.getElementById('billing_address1').value      = '';
						document.getElementById('billing_address2').value      = '';
						document.getElementById('billing_city').value          = '';
						document.getElementById('billing_state').value         = '';
						document.getElementById('billing_zip_code').value      = '';
						document.getElementById('billing_phone').value         = '';
						document.getElementById('billing_fax').value           = '';

						document.getElementById('shipping_company').value      = '';
						document.getElementById('shipping_first_name').value   = '';
						document.getElementById('shipping_last_name').value    = '';
						document.getElementById('shipping_address1').value     = '';
						document.getElementById('shipping_address2').value     = '';
						document.getElementById('shipping_city').value         = '';
						document.getElementById('shipping_state').value        = '';
						document.getElementById('shipping_zip_code').value     = '';
						document.getElementById('shipping_phone').value        = '';
						document.getElementById('shipping_fax').value          = '';

						document.getElementById('email').value                 = '';
						document.getElementById('password').value              = '';
						document.getElementById('password_verify').value       = '';
						
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
			width: 400px;
		}
		dl dt {
			float: left;
			padding-top: 4px;
			text-align: right;
			width: 100px;
		}
		dl dd {
			float: left;
			width: 240px;
		}
		dl dd input, dl dd select {
			margin: 2px;
			width: 250px;
		}

	</style>
{/literal}
<h3>{if isset($module.customer.id)}Update{else}Add{/if} Customer</h3>
<form method="post" action="/store/admin/customers/save">
	<div class="right" style="margin-bottom: -10px;">
		Same as billing information?
		<input id="shipping_same_as_billing" type="checkbox" onclick="copyAddress(this);" name="shipping_same_as_contact" style="margin-top: -2px" />
	</div>
	<div class="float-left">
		<b>Billing Information:</b>
		<dl>
			<dt>Company:</dt>
			<dd><input type='text' name='billing_company' id='billing_company' maxlength="64" value="{$module.customer.billing_company}" /></dd>
			<dt><span class="pink">*</span>First Name:</dt>
			<dd><input type='text' name='billing_first_name' id='billing_first_name' title="required" maxlength="50" value="{$module.customer.billing_first_name}" /></dd>
			<dt><span class="pink">*</span>Last Name:</dt>
			<dd><input type='text' name='billing_last_name' id='billing_last_name' title="required" maxlength="50" value="{$module.customer.billing_last_name}" /></dd>
			<dt><span class="pink">*</span>Address:</dt>
			<dd>
				<input type='text' name='billing_address1' id='billing_address1' title="required" maxlength="64" value="{$module.customer.billing_address1}" /><br />
				<input type='text' name='billing_address2' id='billing_address2' maxlength="64" value="{$module.customer.billing_address2}" />
			</dd>
			<dt><span class="pink">*</span>City:</dt>
			<dd><input type='text' name='billing_city' id='billing_city' title="required" maxlength="64" value="{$module.customer.billing_city}" /></dd>
			<dt><span class="pink">*</span>State:</dt>
			<dd>{html_select_state prefix="billing_" title="required"}</dd>
			<dt><span class="pink">*</span>ZIP Code:</dt>
			<dd><input type='text' name='billing_zip_code' id='billing_zip_code' style="width: 50px;" title="required" maxlength="5" value="{$module.customer.billing_zip_code}" /></dd>
			<dt><span class="pink">*</span>Phone:</dt>
			<dd><input type="text" name="billing_phone" id="billing_phone" style="width: 150px" maxlength="32" title="required" value="{$module.customer.billing_phone}" /></dd>
			<dt>Fax:</dt>
			<dd><input type="text" name="billing_fax" id="billing_fax" style="width: 150px" maxlength="32" value="{$module.customer.billing_fax}" /></dd>
		</dl>
	</div>
	<div class="float-right">
		<b>Shipping Information:</b>
		<dl>
			<dt>Company:</dt>
			<dd><input type='text' name='shipping_company' id='shipping_company' maxlength="64" value="{$module.customer.shipping_company}" /></dd>
			<dt><span class="pink">*</span>First Name:</dt>
			<dd><input type='text' name='shipping_first_name' id='shipping_first_name' title="required" maxlength="50" value="{$module.customer.shipping_first_name}" /></dd>
			<dt><span class="pink">*</span>Last Name:</dt>
			<dd><input type='text' name='shipping_last_name' id='shipping_last_name' title="required" maxlength="50" value="{$module.customer.shipping_last_name}" /></dd>
			<dt><span class="pink">*</span>Address:</dt>
			<dd>
				<input type='text' name='shipping_address1' id='shipping_address1' title="required" maxlength="64" value="{$module.customer.shipping_address1}" /><br />
				<input type='text' name='shipping_address2' id='shipping_address2' maxlength="64" value="{$module.customer.shipping_address2}" />
			</dd>
			<dt><span class="pink">*</span>City:</dt>
			<dd><input type='text' name='shipping_city' id='shipping_city' title="required" maxlength="64" value="{$module.customer.shipping_city}" /></dd>
			<dt><span class="pink">*</span>State:</dt>
			<dd>{html_select_state prefix="shipping_" title="required"}</dd>
			<dt><span class="pink">*</span>ZIP Code:</dt>
			<dd><input type='text' name='shipping_zip_code' id='shipping_zip_code' style="width: 50px;" title="required" maxlength="5" value="{$module.customer.shipping_zip_code}" /></dd>
			<dt><span class="pink">*</span>Phone:</dt>
			<dd><input type="text" name="shipping_phone" id="shipping_phone" style="width: 150px" maxlength="32" title="required" value="{$module.customer.shipping_phone}" /></dd>
			<dt>Fax:</dt>
			<dd><input type="text" name="shipping_fax" id="shipping_fax" style="width: 150px" maxlength="32" value="{$module.customer.shipping_fax}" /></dd>
		</dl>
	</div>
	<br class="clear-left" /><br />
	<div class="float-left">
		<b>Email Address:</b><br />
		This is what the customer uses as their login ID
		<dl>
			<dt><span class="pink">*</span>Email:</dt>
			<dd><input type="text" name="email" id="email" maxlength="255" value="{$module.customer.email}" /></dd>
		</dl>
	</div>
	<div class="float-right">
		<b>Password Change?</b><br />
		If you do not wish to update the customer's password, leave blank.
		<dl>
			<dt>Password:</dt>
			<dd><input type="password" id="password" name="password" maxlength="12" value="" /></dd>
			<dt>Verify:</dt>
			<dd><input type="password" id="password_verify" name="password_verify" maxlength="12" value="" /></dd>
		</dl>
	</div>
	<br class="clear-right" /><br />
	<div class="center">
		{if isset($module.customer.id)}<input type="hidden" name="id" value="{$module.customer.id}" />{/if}
		<input type="reset" value="Reset Form" /><input type="button" value="Store Information" onclick="ajaxRequest(this.parentNode.parentNode{if !isset($module.customer.id)}, 'clearForm'{/if}); return false;" />
	</div>
</form>
<script type="text/javascript">
	document.getElementById('billing_state').value  = "{$module.customer.billing_state}";
	document.getElementById('shipping_state').value = "{$module.customer.shipping_state}";
</script>
