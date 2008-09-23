<div id="store-subnav">
	<ol id="subnav">
		{foreach from=$subnav key=link item=label}
			<li {if $model == 'store/'|cat:$link}class="selected"{/if}>
				<a href="/{$section}/{$link}" class="{$link}">
					{$label}{if $link == 'cart' && $cart.count != 0} ({$cart.count}){/if}
				</a>
			</li>
		{/foreach}
	</ol>
</div>
