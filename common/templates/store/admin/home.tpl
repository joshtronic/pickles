{literal}
<style>
	table#stats {
		border: 1px solid #999;
		width: 250px;
		float: right;
	}
</style>
{/literal}
<div style="float: left; padding-right: 80px;">
	<img src="/static/contrib/silk/icons/package.png" style="float: left; padding-right: 10px" />
	<ol style="float: left">
		<li><h3><a href="/store/admin/orders">Orders</a></h3></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/pending">Pending</a></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/approved">Approved</a></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/partially-shipped">Partially Shipped</a></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/shipped/completed">Shipped/Completed</a></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/backorder">Backorder</a></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/declined">Declined</a></li>
		<li>&nbsp; <a href="/store/admin/orders/filter/void">Void</a></li>
	</ol>
</div>
<div style="float: left">
	<b>Today's Orders</b><br /><br />
	There are <b>No New Orders</b> today.
</div>
<table id="stats">
	<tr><th colspan="2">Statistics</th></tr>
	<tr>
		<td>Today's Sales:</td>
		<td>${$module.statistics.sales_today|number_format:2}</td>
	</tr>
	<tr>
		<td>Year-To-Date Sales:</td>
		<td>${$module.statistics.sales_ytd|number_format:2}</td>
	</tr>
	<tr>
		<td>Month-To-Date Sales:</td>
		<td>${$module.statistics.sales_mtd|number_format:2}</td>
	</tr>
	<tr>
		<td>Today Orders:</td>
		<td>{$module.statistics.orders_today}</td>
	</tr>
	<tr>
		<td>Year-To-Date Orders:</td>
		<td>{$module.statistics.orders_ytd}</td>
	</tr>
	<tr>
		<td>Month-To-Date Orders:</td>
		<td>{$module.statistics.orders_mtd}</td>
	</tr>
	<tr>
		<td>Customers:</td>
		<td>{$module.statistics.total_customers}</td>
	</tr>
</table>
<br clear="both" />
