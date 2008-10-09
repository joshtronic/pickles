<div id="store-top-sellers">
	<h1>Top Sellers</h1>
	<div class="float-left" style="width: 175px;">
		{section name=product loop=$top_sellers start=0 loop=5 step=1}
			<a href="/store/product/{$top_sellers[product].id}"><b>{$smarty.section.product.index+1}.</b> {$top_sellers[product].name}</a><br />
		{/section}
	</div>
	<div class="float-left" style="width: 175px; padding-left: 10px;">
		{section name=product loop=$top_sellers start=5 loop=10 step=1}
			<a href="/store/product/{$top_sellers[product].id}"><b>{$smarty.section.product.index+1}.</b> {$top_sellers[product].name}</a><br />
		{/section}
	</div>
</div>
