<h3>Orders</h3>
{if is_array($module.orders)}
	<table style="width: 100%">
		<tr>
			<th class="left" nowrap="nowrap">Order #</th>
			<th class="left">Customer</th>
			<th class="left" nowrap="nowrap">Order Date</th>
			<th class="left">Total</th>
			<th>Status</th>
			<th nowrap="nowrap">Last Update</th>
			<th nowrap="nowrap">Shipping Method</th>
			<th>Weight</th>
			<th>Transaction #</th>
		</tr>
		{foreach from=$module.orders item=order}
			<tr class="center">
				<td class="left">
					<a href="/store/admin/orders/edit/{$order.order_id}" title="View / Update Order">{$order.order_id}</a></td>
				<td class="left">
					{if $order.customer_id != ''}<a href="/store/admin/customers/view/{$order.customer_id}">{/if}{$order.customer_name}{if $order.customer_id != ''}</a>{/if}
				</td>
				<td class="left">{$order.order_time}</td>
				<td class="left">${$order.total_amount}</td>
				<td>{if $order.status != ''}{$order.status}{else}<em>Unknown</em>{/if}</td>
				<td>{if $order.last_update != ''}{$order.last_update|date_format:'%m/%d/%Y'}{else}<em>Unknown</em>{/if}</td>
				<td>{$order.shipping_method}</td>
				<td>{$order.weight|number_format:2} lbs</td>
				<td>{if $order.transaction_id != ''}{$order.transaction_id}{else}<em>n/a</em>{/if}</td>
			</tr>
		{/foreach}
	</table>
{/if}
