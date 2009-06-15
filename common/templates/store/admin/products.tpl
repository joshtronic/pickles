<h3>Products</h3>
<div class="float-right">
	<img src="/static/contrib/silk/icons/add.png" style="margin: 0 5px -4px 0;"/><a href="/store/admin/products/edit">Add New Product</a>
</div>
{if is_array($module.products)}
	<table style="width: 100%">
		<tr>
			<th class="left">SKU</th>
			<th class="left">Name</th>
			<th>Photo</th>
			<th class="left">Price</th>
			<th>In Stock?</th>
			<th></th>
		</tr>
		{foreach from=$module.products item=product}
			<tr class="left">
				<td>{$product.sku}</td>
				<td>{$product.name}</td>
				<td class="center"><img src="/static/contrib/silk/icons/{if $category.visible == 'Y'}tick{else}cross{/if}.png" /></td>
				<td>${$product.price}</td>
				<td class="center"><img src="/static/contrib/silk/icons/{if $product.in_stock == 'Y'}tick{else}cross{/if}.png" /></td>
				<td>
					<a href="/store/admin/products/edit/{$product.id}"><img src="/static/contrib/silk/icons/pencil.png" alt="Edit Product" title="Edit Product" /></a>
					<a href="/store/admin/products/delete/{$product.id}" onclick="return confirm('Are you sure you want to delete {$product.first_name} {$product.last_name}?')"><img src="/static/contrib/silk/icons/delete.png" alt="Delete Product" title="Delete Product" /></a>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}
