<div class="store-subnav">
	<ul class="subnav">
		{foreach from=$config.store key=link item=label}
			<li {if $module_name.0 == 'store/'|cat:$link}class="selected"{/if}>
				<a href="/{$module_name.1}/{$link}" class="{$link}">
					{$label}{if $link == 'cart' && $module.cart.count != 0} ({$module.cart.count}){/if}
				</a>
			</li>
		{/foreach}
	</ul>
</div>
