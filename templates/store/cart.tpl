<div class="content-left">
	{include file="../../pickles/templates/store/navigation.tpl"}<br /><br />
	{include file="../../pickles/templates/store/categories.tpl"}
</div>
<div class="content-right store-cart">
	<div class="your-cart">
		<h1>Your Cart</h1>
	</div>
	{if is_array($cart.products)}
		<table class="product-list">
			<tr>
				<th class="product-quantity">Qty.</th>
				<th class="product-sku">SKU</th>
				<th class="product-description">Product Description</th>
				<th class="product-price">Price</th>
				<th class="product-total">Total</th>
			</tr>
			{foreach from=$cart.products item=product}
				<tr>
					<td class="product-quantity"><input type="text" class="product-quantity" value="{$product.quantity}" /></td>
					<td class="product-sku">{$product.sku}</td>
					<td class="product-description">{$product.name}</td>
					<td class="product-price">${$product.price}</td>
					<td class="product-total">${$product.total}</td>
			{/foreach}
			<tr>
				<td colspan="5">
					
				</td>
			</tr>
		</table>
	{else}
		You have no items in your shopping cart.
	{/if}
<div>
