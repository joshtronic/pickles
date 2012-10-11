window.onload = function()
{
	if (window.jQuery)
	{
		$(document).ready(function()
		{
			$('.tip').tooltip({
				'placement': 'top',
				'trigger'  : 'hover'
			});

			if ($('.content form').length)
			{
				$('.content form input:eq(0)').focus();
			}

			$('.content form input, .content form button').focus(function()
			{
				$('.content form label span.label').hide().removeClass('hidden');
				$(this).parent('.well').children().find('span').show();
			});
		});
	}
}
