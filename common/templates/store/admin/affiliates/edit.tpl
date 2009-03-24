<script type="text/javascript" src="/static/js/ajax.js" /></script>
<script type="text/javascript" src="/js/processOrder.js" /></script>

{literal}
	<script type="text/javascript">
		// @todo Move this to an external file so that logic isn't in the template
		function copyAddress(that) {
			if (that.checked == true) {
				// Copy the values
				document.getElementById('payee_company').value    = document.getElementById('contact_company').value;
				document.getElementById('payee_first_name').value = document.getElementById('contact_first_name').value;
				document.getElementById('payee_last_name').value  = document.getElementById('contact_last_name').value;
				document.getElementById('payee_address1').value   = document.getElementById('contact_address1').value;
				document.getElementById('payee_address2').value   = document.getElementById('contact_address2').value;
				document.getElementById('payee_city').value       = document.getElementById('contact_city').value;
				document.getElementById('payee_state').value      = document.getElementById('contact_state').value;
				document.getElementById('payee_zip_code').value   = document.getElementById('contact_zip_code').value;
				document.getElementById('payee_phone').value      = document.getElementById('contact_phone').value;
				document.getElementById('payee_fax').value        = document.getElementById('contact_fax').value;
				
				// Disable the fields
				document.getElementById('payee_company').disabled    = true; 
				document.getElementById('payee_first_name').disabled = true; 
				document.getElementById('payee_last_name').disabled  = true; 
				document.getElementById('payee_address1').disabled   = true; 
				document.getElementById('payee_address2').disabled   = true; 
				document.getElementById('payee_city').disabled       = true; 
				document.getElementById('payee_state').disabled      = true; 
				document.getElementById('payee_zip_code').disabled   = true; 
				document.getElementById('payee_phone').disabled      = true; 
				document.getElementById('payee_fax').disabled        = true; 
			}
			else {
				// Clear the values
				document.getElementById('payee_company').value    = '';
				document.getElementById('payee_first_name').value = '';
				document.getElementById('payee_last_name').value  = '';
				document.getElementById('payee_address1').value   = '';
				document.getElementById('payee_address2').value   = '';
				document.getElementById('payee_city').value       = '';
				document.getElementById('payee_state').value      = '';
				document.getElementById('payee_zip_code').value   = '';
				document.getElementById('payee_phone').value      = '';
				document.getElementById('payee_fax').value        = '';
				
				// Enable the fields
				document.getElementById('payee_company').disabled    = false; 
				document.getElementById('payee_first_name').disabled = false; 
				document.getElementById('payee_last_name').disabled  = false; 
				document.getElementById('payee_address1').disabled   = false; 
				document.getElementById('payee_address2').disabled   = false; 
				document.getElementById('payee_city').disabled       = false; 
				document.getElementById('payee_state').disabled      = false; 
				document.getElementById('payee_zip_code').disabled   = false; 
				document.getElementById('payee_phone').disabled      = false; 
				document.getElementById('payee_fax').disabled        = false; 
			}
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
		dl dd input {
			margin: 2px;
			width: 250px;
		}

	</style>
{/literal}
<h3>{if isset($module.affiliate.id)}Update{else}Add{/if} Affiliate</h3>
<form method="post" action="/store/admin/affiliates/save">
	<div class="right" style="margin-bottom: -10px;">
		Same as contact information?
		<input id="payee_same_as_contact" type="checkbox" onclick="copyAddress(this);" name="payee_same_as_contact" style="margin-top: -2px" />
	</div>
	<div class="float-left">
		<b>Contact Information:</b>
		<dl>
			<dt>Company:</dt>
			<dd><input type='text' name='contact_company' id='contact_company' maxlength="64" value="{$module.affiliate.contact_company}" /></dd>
			<dt><span class="pink">*</span>First Name:</dt>
			<dd><input type='text' name='contact_first_name' id='contact_first_name' title="required" maxlength="50" value="{$module.affiliate.contact_first_name}" /></dd>
			<dt><span class="pink">*</span>Last Name:</dt>
			<dd><input type='text' name='contact_last_name' id='contact_last_name' title="required" maxlength="50" value="{$module.affiliate.contact_last_name}" /></dd>
			<dt><span class="pink">*</span>Address:</dt>
			<dd>
				<input type='text' name='contact_address1' id='contact_address1' title="required" maxlength="64" value="{$module.affiliate.contact_address1}" /><br />
				<input type='text' name='contact_address2' id='contact_address2' maxlength="64" value="{$module.affiliate.contact_address1}" />
			</dd>
			<dt><span class="pink">*</span>City:</dt>
			<dd><input type='text' name='contact_city' id='contact_city' title="required" maxlength="64" value="{$module.affiliate.contact_city}" /></dd>
			<dt><span class="pink">*</span>State:</dt>
			<dd>{html_select_state prefix="contact_" title="required"}</dd>
			<dt><span class="pink">*</span>ZIP Code:</dt>
			<dd><input type='text' name='contact_zip_code' id='contact_zip_code' style="width: 50px;" title="required" maxlength="5" value="{$module.affiliate.contact_zip_code}" /></dd>
			<dt><span class="pink">*</span>Phone:</dt>
			<dd><input type="text" name="contact_phone" id="contact_phone" style="width: 150px" maxlength="32" title="required" value="{$module.affiliate.contact_phone}" /></dd>
			<dt>Fax:</dt>
			<dd><input type="text" name="contact_fax" id="contact_fax" style="width: 150px" maxlength="32" value="{$module.affiliate.contact_fax}" /></dd>
		</dl>
		<br class="clear-left" /><br />
		<dl>
			<dt>Email:</dt>
			<dd><input type="text" name="email" id="email" maxlength="255" value="{$module.affiliate.email}" /></dd>
		</dl>
	</div>
	<div class="float-right">
		<b>Payee Information:</b>
		<dl>
			<dt>Company:</dt>
			<dd><input type='text' name='payee_company' id='payee_company' maxlength="64" value="{$module.affiliate.payee_company}" /></dd>
			<dt><span class="pink">*</span>First Name:</dt>
			<dd><input type='text' name='payee_first_name' id='payee_first_name' title="required" maxlength="50" value="{$module.affiliate.payee_first_name}" /></dd>
			<dt><span class="pink">*</span>Last Name:</dt>
			<dd><input type='text' name='payee_last_name' id='payee_last_name' title="required" maxlength="50" value="{$module.affiliate.payee_last_name}" /></dd>
			<dt><span class="pink">*</span>Address:</dt>
			<dd>
				<input type='text' name='payee_address1' id='payee_address1' title="required" maxlength="64" value="{$module.affiliate.payee_address1}" /><br />
				<input type='text' name='payee_address2' id='payee_address2' maxlength="64" value="{$module.affiliate.payee_address1}" />
			</dd>
			<dt><span class="pink">*</span>City:</dt>
			<dd><input type='text' name='payee_city' id='payee_city' title="required" maxlength="64" value="{$module.affiliate.payee_city}" /></dd>
			<dt><span class="pink">*</span>State:</dt>
			<dd>{html_select_state prefix="payee_" title="required"}</dd>
			<dt><span class="pink">*</span>ZIP Code:</dt>
			<dd><input type='text' name='payee_zip_code' id='payee_zip_code' style="width: 50px;" title="required" maxlength="5" value="{$module.affiliate.payee_zip_code}" /></dd>
			<dt><span class="pink">*</span>Phone:</dt>
			<dd><input type="text" name="payee_phone" id="payee_phone" style="width: 150px" maxlength="32" title="required" value="{$module.affiliate.payee_phone}" /></dd>
			<dt>Fax:</dt>
			<dd><input type="text" name="payee_fax" id="payee_fax" style="width: 150px" maxlength="32" value="{$module.affiliate.payee_fax}" /></dd>
		</dl>
	</div>
	<br class="clear-left" /><br />
	<div class="float-left">
		<b>Tax Information:</b>
		<dl>
			<dt><span class="pink">*</span>Tax ID:</dt>
			<dd><input type="input" id="tax_id" name="tax_id" title="required" maxlength="12" value="{$module.affiliate.tax_id}" /></dd>
			<dt><span class="pink">*</span>Tax Class:</dt>
			<dd>
				<select name="tax_class" id="tax_class" title="required">
					<option value="">-- Select a Class --</option>
					<option value="I"{if $module.affiliate.tax_class == 'I'} selected{/if}>Individual</option>
					<option value="C"{if $module.affiliate.tax_class == 'C'} selected{/if}>Corporation</option>
					<option value="P"{if $module.affiliate.tax_class == 'P'} selected{/if}>Partnership</option>
				</select>
			</dd>
		</dl>
	</div>
	<div class="float-right">
		<b>Commission:</b>
		<dl>
			<dt><span class="pink">*</span>Rate:</dt>
			<dd><input type="input" id="commission_rate" name="commission_rate" title="required" style="width: 50px" value="{$module.affiliate.commission_rate}" />%</dd>
		</dl>
	</div>
	<br class="clear-left" />
	<div class="center">
		{if isset($module.affiliate.id)}<input type="hidden" name="id" value="{$module.affiliate.id}" />{/if}
		<input type="reset" value="Reset Form" /><input type="button" value="Store Information" onclick="ajaxRequest(this.parentNode.parentNode); return false;" />
	</div>
</form>
<script type="text/javascript">
	document.getElementById('contact_state').value = "{$module.affiliate.contact_state}";
	document.getElementById('payee_state').value   = "{$module.affiliate.payee_state}";
</script>
