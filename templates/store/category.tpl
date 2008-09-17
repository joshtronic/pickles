<div class="content-left">
	{include file="../../pickles/templates/store/navigation.tpl"}<br /><br />
	{include file="../../pickles/templates/store/categories.tpl"}
</div>
<div class="content-right store-category">
	<div class="{$category.permalink}">
		<h1>{$category.name}</h1>
	</div>
	<div class="center">
		{$category.description}
	</div>
	<div class="breadcrumbs">
		<a href="/store">Store Home</a> > <a href="/store/category/{$category.permalink}">{$category.name}</a>
	</div>
</div>
