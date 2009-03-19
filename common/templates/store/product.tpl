<div class="content-left">
	{include file="../../pickles/common/templates/store/navigation.tpl"}<br /><br />
	{include file="../../pickles/common/templates/store/categories.tpl"}
</div>
<div class="content-right store-category">
	<div class="{$module.category.permalink}">
		<h1>{$module.category.name}</h1>
	</div>
	<div class="center">
		{$module.category.description}
	</div>
	<div class="breadcrumbs">
		<a href="/store">Shopping Home</a> &gt; <a href="/store/category/{$module.category.permalink}">{$module.category.name}</a>
	</div>
	<div>
		{foreach from=$module.products item=product name=products}
			<div class="float-left" style="width: 200px; margin: 3px">
				<img src="/images/products/{$product.id}/small.jpg" class="float-left" style="padding-right: 5px" />
				<div class="float-left" style="width: 120px">
					<a href="/store/product/{$product.id}">{$product.name}</a><br /><br />
					{$product.teaser}<br /><br />
					<b>${$product.price}</b><br /><br />
					<ul><li><a href="/store/cart/add/{$product.id}" class="add-to-cart"><span>Add to Cart</span></a></li></ul>
					<br /><br />
				</div>
			</div>
			{if $smarty.foreach.products.iteration % 3 == 0}<br class="clear" />{/if}
		{/foreach}
	</div>
</div>
