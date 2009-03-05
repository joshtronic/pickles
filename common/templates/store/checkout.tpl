{if $status == 'Approved'}
	<img src="/images/success.jpg" alt="Transaction Successful!" /><!-- h1Transaction Successful!</h1><br /-->
	Thank you for your order, a receipt should arrive via email shortly.  Once your order has been shipped you will receive the shipment tracking information via email as well.
{else}
	<!-- In theory, this should never be seen -->
	<h1>Transaction {$status}.</h1><br />
	There was an error processing your order:<br /><br />
	<div style="padding-left: 40px; font-weight: bold;">{$message}</div><br />
	Please return to the previous page and make sure all of the information is correct.  Should you continue to have problems, please call (800) 895-4415 for futher assistance.
{/if}
<div style="height: 900px"></div>
