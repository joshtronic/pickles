<div class="store-subnav">
	<ul class="subnav">
		{foreach from=$config.store key=link item=label}
			<li {if $module.0 == 'store/'|cat:$link}class="selected"{/if}>
				<a href="/{$section}/{$link}" class="{$link}">
					{$label}{if $link == 'cart' && $cart.count != 0} ({$cart.count}){/if}
				</a>
			</li>
		{/foreach}
	</ul>
</div>
