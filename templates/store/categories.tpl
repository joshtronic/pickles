<div id="store-categories">
	{foreach from=$categories item=category}
		<div id="{$category.name|lower|replace:' ':'-'}">
			<h1 id="">{$category.name}</h1>
		</div>
		<ul>
			{foreach from=$category.subcategories item=subcategory}
				<li><a href="/store/category/{$subcategory.name|regex_replace:'/&[a-z]+;/':''|replace:'  ':' '|strip_tags:false|lower|replace:' ':'-'}">{$subcategory.name}</a></li>
			{/foreach}
		</ul><br />
	{/foreach}
</div>
