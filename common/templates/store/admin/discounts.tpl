<h3>Discounts</h3>
<div class="float-right">
	<img src="/static/contrib/silk/icons/add.png" style="margin: 0 5px -4px 0;"/><a href="/store/admin/discounts/edit">Add New Discount</a>
</div>
{if is_array($module.discounts)}
	<table style="width: 100%">
		<tr>
			<th class="left">Name</th>
			<th class="left">Code</th>
			<th class="left">Description</th>
			<th>Products</th>
			<th>Categories</th>
			<th>Customers</th>
			<th>Expiration</th>
			<th></th>
		</tr>
		{foreach from=$module.discounts item=discount}
			<tr class="center">
				<td class="left">{$discount.name}</td>
				<td class="left">{$discount.coupon}</td>
				<td class="left">{$discount.description}</td>
				<td>{if $discount.all_products == 'Y'}All{else}Select{/if}</td>
				<td>{if $discount.all_categories == 'Y'}All{else}Select{/if}</td>
				<td>{if $discount.all_customers == 'Y'}All{else}Select{/if}</td>
				<td>{if $discount.valid_through == null}<em>Never</em>{else}{$discount.valid_through}{/if}</td>
				<td>
					<a href="/store/admin/discounts/edit/{$discount.id}"><img src="/static/contrib/silk/icons/pencil.png" alt="Edit Discount" title="Edit Discount" /></a>
					<a href="/store/admin/discounts/delete/{$discount.id}" onclick="return confirm('Are you sure you want to delete {$discount.first_name} {$discount.last_name}?')"><img src="/static/contrib/silk/icons/delete.png" alt="Delete Discount" title="Delete Discount" /></a>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
