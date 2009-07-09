<h3>Customers</h3>
<div class="float-right">
	<img src="/static/contrib/silk/icons/add.png" style="margin: 0 5px -4px 0;"/><a href="/store/admin/customers/edit">Add New Customer</a>
</div>
{if is_array($module.customers)}
	<table style="width: 100%">
		<tr>
			<th class="left">Name</th>
			<th class="left">Phone</th>
			<th class="left">Email</th>
			<th>Orders</th>
			<th></th>
		</tr>
		{foreach from=$module.customers item=customer}
			<tr class="center">
				<td class="left"><a href="/store/admin/customers/view/{$customer.id}">{$customer.shipping_last_name}, {$customer.shipping_first_name}</a></td>
				<td class="left">{$customer.shipping_phone}</td>
				<td class="left">{mailto address=$customer.email}</td>
				<td>{$customer.order_count}</td>
				<td>
					<a href="/store/admin/customers/edit/{$customer.id}"><img src="/static/contrib/silk/icons/pencil.png" alt="Edit Customer" title="Edit Customer" /></a>
					<a href="/store/admin/customers/delete/{$customer.id}" onclick="alert('Customer deletion has not yet been implemented.'); return false; return confirm('Are you sure you want to delete {$customer.billing_first_name} {$customer.billing_last_name}?')"><img src="/static/contrib/silk/icons/delete.png" alt="Delete Customer" title="Delete Customer" /></a>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
