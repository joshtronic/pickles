<img src="/images/products/{$featured.id}/featured.jpg" class="float-right" style="padding-right: 20px" />
<div class="store-featured-product">
	<h1>Featured Product</h1>
	<h2>{$featured.name}</h2><br />
	{$featured.teaser}<br /><br />
	<ul>
		<li><a href="/store/cart/add/{$featured.id}" class="add-to-cart"><span>Add to Cart</span></a></li>
		<li><a href="/store/product/{$featured.id}" class="more-information"><span>More Information</span></a></li>
	</ul>
</div>
