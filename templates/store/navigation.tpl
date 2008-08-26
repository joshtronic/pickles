<div id="store-subnav">
	<ol id="subnav">
		{foreach from=$subnav key=link item=label}
			<li {if $model == 'store/'|cat:$link}class="selected"{/if}><a href="/{$section}/{$link}">{$label}</a></li>
		{/foreach}
	</ol>
</div>
