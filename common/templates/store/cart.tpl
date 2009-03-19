<div class="content-left">
	{include file="../../pickles/common/templates/store/navigation.tpl"}<br /><br />
	{include file="../../pickles/common/templates/store/categories.tpl"}
</div>
<div class="content-right store-cart">
	<div class="your-cart">
		<h1>Your Cart</h1>
	</div>
	{if is_array($module.cart.products) && count($module.cart.products) > 0}
		<form method="POST" action="/store/cart/" name="cart">
			<table class="product-list">
				<tr>
					<th class="product-quantity">Qty.</th>
					<th class="product-sku">SKU</th>
					<th class="product-description">Product Description</th>
					<th class="product-price">Price</th>
					<th class="product-total">Total</th>
				</tr>
				{foreach from=$module.cart.products key=id item=product}
					<tr>
						<td class="product-quantity">
							<input type="text" class="product-quantity" value="{$product.quantity}" name="quantity[{$id}]" /><br />
							<span style="font-size: 7pt"><a href="/store/cart/remove/{$id}">Remove</a></span>
						</td>
						<td class="product-sku">{$product.sku}</td>
						<td class="product-description">{$product.name}</td>
						<td class="product-price">
							${$product.price|number_format:2}
							{if is_array($module.discounts) && array_key_exists($id, $module.discounts)}
								<div style="color: #090">
									-${$module.discounts.$id.price|number_format:2}
								</div>
							{/if}
						</td>
						<td class="product-total">
							${$product.total|number_format:2}
							{if is_array($module.discounts) && array_key_exists($id, $module.discounts)}
								<div style="color: #090">
									-${$module.discounts.$id.total|number_format:2}
								</div>
							{/if}
						</td>
					</tr>
				{/foreach}
				<tr>
					<td colspan="3">
						<br />
						<b>Discount Code:</b> <input type="text" size="12" name="coupon" /> <input type="submit" value="Apply" onclick="document.cart.action += 'discount/apply';" />
					</td>
					<td class="right">
						<b>Subtotal:</b><br />
						{if $module.cart.discount}{/if}
						<b>Shipping:</b><br />
						<b>Total:</b>
					</td>
					<td class="right">
						${$module.cart.subtotal|number_format:2}<br />
						$4.99<br />
						${$module.cart.subtotal+4.99|number_format:2}<br />
					</td>
				</tr>
				<tr>
					<td colspan="3">
						<br />
						<input type="submit" value="Update Cart" onclick="document.cart.action += 'update';" />
						<input type="submit" value="Empty Cart" onclick="document.cart.action += 'empty'" />
						<input type="button" value="Continue Shopping" onclick="location.href='/store';" />
					</td>
					<td colspan="2" class="right">
						<br />
						<input type="button" value="Checkout" onclick="location.href='/store/cart/checkout';" />
					</td>
				</tr>
			</table>
		</form>
	{else}
		You have no items in your shopping cart.
	{/if}
</div>
