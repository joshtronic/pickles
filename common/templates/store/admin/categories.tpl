<h3>Categories</h3>
<div class="float-right">
	<img src="/static/contrib/silk/icons/add.png" style="margin: 0 5px -4px 0;"/><a href="/store/admin/categories/edit">Add New Category</a>
</div>
{if is_array($module.categories)}
	<table style="width: 100%">
		<tr>
			<th class="left">Name</th>
			<th>Sort</th>
			<th>Products</th>
			<th>Visible</th>
			<th></th>
		</tr>
		{foreach from=$module.categories item=category}
			<tr class="center">
				<td class="left bold">{$category.name}</td>
				<td class="bold">{$category.weight}</td>
				<td>{$category.product_count}</td>
				<td><img src="/static/contrib/silk/icons/{if $category.visible == 'Y'}tick{else}cross{/if}.png" /></td>
				<td>
					<a href="/store/admin/categories/edit/{$category.id}"><img src="/static/contrib/silk/icons/pencil.png" alt="Edit Discount" title="Edit Discount" /></a>
					<a href="/store/admin/categories/delete/{$category.id}" onclick="return confirm('Are you sure you want to delete {$category.first_name} {$category.last_name}?')"><img src="/static/contrib/silk/icons/delete.png" alt="Delete Discount" title="Delete Discount" /></a>
				</td>
			</tr>
			{if isset($category.children)}
				{foreach from=$category.children item=child}
					<tr class="center">
						<td class="left">&nbsp;&nbsp;&nbsp;&nbsp;{$child.name}</td>
						<td>{$child.weight}</td>
						<td>{$child.product_count}</td>
						<td><img src="/static/contrib/silk/icons/{if $child.visible == 'Y'}tick{else}cross{/if}.png" /></td>
						<td>
							<a href="/store/admin/categories/edit/{$child.id}"><img src="/static/contrib/silk/icons/pencil.png" alt="Edit Discount" title="Edit Discount" /></a>
							<a href="/store/admin/categories/delete/{$child.id}" onclick="return confirm('Are you sure you want to delete {$child.first_name} {$child.last_name}?')"><img src="/static/contrib/silk/icons/delete.png" alt="Delete Discount" title="Delete Discount" /></a>
						</td>
					</tr>
				{/foreach}
			{/if}
		{/foreach}
	</table>
{/if}
