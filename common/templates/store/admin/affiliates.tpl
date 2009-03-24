<h3>Affiliates</h3>
<div class="float-right">
	<img src="/static/contrib/silk/icons/user_add.png" style="margin: 0 5px -4px 0;"/><a href="/store/admin/affiliates/edit">Add New Affiliate</a>
</div>
{if is_array($module.affiliates)}
	<table style="width: 100%">
		<tr>
			<th class="left">Affiliate</th>
			<th class="left">Phone</th>
			<th class="left">Email</th>
			<th>Commission</th>
			<th>Orders</th>
			<th>Balance</th>
			<th>Options</th>
		</tr>
		{foreach from=$module.affiliates item=affiliate}
			<tr class="center">
				<td class="left">{$affiliate.last_name}, {$affiliate.first_name}</td>
				<td class="left">{$affiliate.phone}</td>
				<td class="left">{$affiliate.email}</td>
				<td>{$affiliate.commission_rate}%</td>
				<td>{$affiliate.order_count}</td>
				<td>${$affiliate.unpaid_balance}</td>
				<td>
					<a href="/store/admin/affiliates/edit/{$affiliate.id}"><img src="/static/contrib/silk/icons/user_edit.png" alt="Edit Affiliate" title="Edit Affiliate" /></a>
					<a href=""><img src="/static/contrib/silk/icons/money.png" alt="Pay Affiliate" title="Pay Affiliate" /></a>
					<a href=""><img src="/static/contrib/silk/icons/user_delete.png" alt="Delete Affiliate" title="Delete Affiliate" /></a>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
