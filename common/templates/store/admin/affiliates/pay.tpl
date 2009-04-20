<script type="text/javascript" src="/static/js/ajax.js" /></script>
<!--script type="text/javascript" src="/js/processOrder.js" /></script-->

{literal}
	<style>
		form div {
			margin-top: 10px;
		}
	</style>

	<script type="text/javascript">
		function updateTotal(responseObject, responseElement) {
			if (responseObject != null) {
				switch (responseObject.status) {
					case 'Success':
						var unpaid_balance = (document.getElementById('unpaid_balance').innerHTML.substr(1) - responseObject.amount).toFixed(2);

						document.getElementById('unpaid_balance').innerHTML = '$' + unpaid_balance;
						document.getElementById('amount').value = unpaid_balance;
						document.getElementById('number').value = '';
						{/literal}
						document.getElementById('date_mm').value = '{$smarty.now|date_format:'%m'}';
						document.getElementById('date_dd').value = '{$smarty.now|date_format:'%d'}';
						document.getElementById('date_ccyy').value = '{$smarty.now|date_format:'%Y'}';
						{literal}
						document.getElementById('notes').value = '';

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
{/literal}
<h3>Pay Affiliate</h3>
<form method="post" action="/store/admin/affiliates/pay/save">
	<div class="float-left">
		<b>Payee Information:</b><br /e
		<div style="padding: 0 80px 0 25px">
			{$module.affiliate.payee_company}<br />
			{$module.affiliate.payee_first_name} {$module.affiliate.payee_last_name}<br />
			{$module.affiliate.payee_address1}<br />
			{$module.affiliate.payee_address2}<br />
			{$module.affiliate.payee_city}, {$module.affiliate.payee_state} {$module.affiliate.payee_zip_code}<br /><br />
			{$module.affiliate.payee_phone}<br /><br />
			Tax ID: {$module.affiliate.tax_id} ({$module.affiliate.tax_class})<br /><br /> 
			Unpaid Balance: <span id="unpaid_balance">${$module.affiliate.unpaid_balance|number_format:2}</a>
		</div>
	</div>
	<div class="float-left">
		<b>Commission Check:</b>
		<div style="padding-left: 25px; margin-top: -10px;">
			<table>
				<tr>
					<td align="right">Check Amount:</td>
					<td>$<input type="text" style="width: 60px" value="{$module.affiliate.unpaid_balance|number_format:2}" name="amount" id="amount" /></td>
				</tr>
				<tr>
					<td align="right">Check Number:</td>
					<td><input type="text" style="width: 50px" name="number" id="number" /></td>
				</tr>
				<tr>
					<td align="right">Date Cut:</td>
					<td>
						<input type="text" style="width: 20px" value="{$smarty.now|date_format:'%m'}" name="date[mm]" id="date_mm" />/
						<input type="text" style="width: 20px" value="{$smarty.now|date_format:'%d'}" name="date[dd]" id="date_dd" />/
						<input type="text" style="width: 40px" value="{$smarty.now|date_format:'%Y'}" name="date[ccyy]" id="date_ccyy" />
					</td>
				</tr>
				<tr>
					<td align="right" style="vertical-align: top">Notes:</td>
					<td>
						<textarea name="notes" id="notes"></textarea>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<br class="clear-left" />
	<div class="center">
		{if isset($module.affiliate.id)}<input type="hidden" name="id" value="{$module.affiliate.id}" />{/if}
		<input type="reset" value="Reset Form" /><input type="button" value="Store Information" onclick="ajaxRequest(this.parentNode.parentNode, 'updateTotal'); return false;" />
	</div>
</form>
