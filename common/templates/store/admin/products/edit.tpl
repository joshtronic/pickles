<script type="text/javascript" src="/static/js/ajax.js" /></script>

{literal}
	<script type="text/javascript">
		// @todo Move this to an external file so that logic isn't in the template
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
			width: 135px;
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
<h3>{if isset($module.product.id)}Update{else}Add{/if} Product</h3>
<form method="post" action="/store/admin/products/save">
	<div class="float-left">
		<dl>
			<dt>SKU:</dt>
			<dd><input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>Name:</dt>
			<dd><input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>Teaser:</dt>
			<dd><input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>Description:</dt>
			<dd><input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>MSRP:</dt>
			<dd>$<input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>Price:</dt>
			<dd>$<input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>Size:</dt>
			<dd><input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
			<dt>In Stock?</dt>
			<dd><input type='checkbox' name='XXX' id='XXX' maxlength="64" /></dd>
			<dt style="clear: left">Limit Per Customer:</dt>
			<dd><input type='text' name='XXX' id='XXX' maxlength="64" value="{$module.product.XXX}" /></dd>
		</dl>
	</div>
	<div class="float-left">
		<dl>
			<dt>SKU:</dt>
			<dd><input type='text' name='billing_company' id='billing_company' maxlength="64" value="{$module.product.billing_company}" /></dd>
		</dl>
	</div>
	<br class="clear-left" /><br />
	<div class="center">
		{if isset($module.product.id)}<input type="hidden" name="id" value="{$module.product.id}" />{/if}
		<input type="reset" value="Reset Form" /><input type="button" value="Store Information" onclick="ajaxRequest(this.parentNode.parentNode{if !isset($module.product.id)}, 'clearForm'{/if}); return false;" />
	</div>
</form>
<script type="text/javascript">
	document.getElementById('billing_state').value  = "{$module.product.billing_state}";
	document.getElementById('shipping_state').value = "{$module.product.shipping_state}";
</script>
