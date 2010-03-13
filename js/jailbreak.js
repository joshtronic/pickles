/**
 * Frame Break
 *
 * Include on any pages you don't want to be framed
 */
window.onload = function()
{
	if (top.location != self.location)
	{
		top.location.replace(self.location);
	}
}
