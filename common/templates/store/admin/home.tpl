{literal}
<style>
	fieldset {
		width: 200px;
		border: 1px solid black;
		padding: 10px;
		background-color: #E9BFFF;
		;
	}
	fieldset legend {
		font-weight: bold;
		font-size: 14pt;
	}
	fieldset dl dt {
		float: left;
		clear: left;
		width: 60px;
		text-align: right;
		padding-right: 10px;
	}
	fieldset dl dd {
		float: left;
	}
</style>
{/literal}
<div style="float: left">The best is yet to come... under construction.</div>
<fieldset class="float-right">
	<legend>Statistics</legend>
	<b>Sales:</b>
	<dl>
		<dt>Today:</dt>
		<dd>${$module.statistics.sales_today|number_format:2}</dd>
		<dt>Y-T-D:</dt>
		<dd>${$module.statistics.sales_ytd|number_format:2}</dd>
		<dt>M-T-D:</dt>
		<dd>${$module.statistics.sales_mtd|number_format:2}</dd>
	</dl>
	<br clear="both" /><br />
	<b>Orders:</b>
	<dl>
		<dt>Today:</dt>
		<dd>{$module.statistics.orders_today}</dd>
		<dt>Y-T-D:</dt>
		<dd>{$module.statistics.orders_ytd}</dd>
		<dt>M-T-D:</dt>
		<dd>{$module.statistics.orders_mtd}</dd>
	</dl>
	<br clear="both" /><br />
	<b>Customers:</b>
	<dl>
		<dt>Total:</dt>
		<dd>{$module.statistics.total_customers}</dd>
	</dl>
</fieldset>
<br clear="both" />
