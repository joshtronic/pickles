<h1>Store Administration</h1>
<ol class="store-admin-navigation">
	{foreach from=$module.options item=option name=options}
		<li class="{if ($smarty.foreach.options.first && $module_name.3 == '') || ($module_name.3 == $option)}selected{/if}"><a href="/store/admin/{$option|replace:' ':'-'}">{$option|ucwords}</a></li>
	{/foreach}
	<li class="logout">
		<a href="/store/admin/logout">Logout</a><img src="/static/contrib/silk/icons/door_in.png" alt="Logout" title="Logout" style="margin: 0 0 -3px 5px;" />
	</li>
</ol>
<div class="store-admin-content">
	{include file="../../pickles/common/templates/$module_name[0].tpl"}
</div>
