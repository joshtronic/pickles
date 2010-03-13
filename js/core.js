$(document).ready(function()
{
	// Catches forms being submitted
	$('form input[type=submit]').click(function()
	{
		// Grabs the form
		var form = $(this).parents('form').get();

		// Checks that it's valid
		// @todo Check that the validation plugin is available
		if ($(form).valid() == true)
		{
			// Hides the buttons on the form
			$('button, input', form).hide();

			// @todo Hide the form and add a throbber

			// Copies any CKEditor data to the same named form element
			if (typeof(CKEDITOR) != 'undefined')
			{
				if (typeof(CKEDITOR.instances) != 'undefined')
				{
					for (var instance in CKEDITOR.instances)
					{
						var data = CKEDITOR.instances[instance].getData();

						if (data != '')
						{
							$('#' + instance).val(data);
						}
					}
				}
			}

			$.ajax({
				'type':     $(form).attr('method'),
				'url':      $(form).attr('action'),
				'data':     $(form).serialize(),
				'dataType': 'json',

				'success': function(data, textStatus, XMLHttpRequest)
				{
					if (data.status != 'success' && typeof(data.message) != 'undefined')
					{
						alert('Error: ' + data.message);
						$('button', form).show();
					}
					else if (data.status == 'success')
					{
						if (typeof(data.message) != 'undefined')
						{
							$('input',    form).val('');
							$('select',   form).val('');
							$('textarea', form).val('');

							alert(data.message);

							$('button, input', form).show();
						}

						if (typeof(data.url) != 'undefined')
						{
							parent.location.href = data.url;
						}

						if (typeof(data.callback) != 'undefined')
						{
							window[data.callback](data);
							$('button', form).show();
						}
					}
					else
					{
						alert('Error: ' + data);
						$('button, input', form).show();
					}
				},

				'error': function(XMLHttpRequest, textStatus, errorThrown)
				{

				}
			});
		}
	});

	// Forces forms to return false on submit
	$('form').submit(function(){ return false; });

	// Automatically applies zebra stripes to tables
	$('table tr:even td').addClass('even');
	$('table tr:odd td').addClass('odd');
});

// Automatically tab to next element when max length is reached
function autoTab(element)
{
	if ($(element).val().length >= $(element).attr('maxlength'))
	{
		$(element).next().focus();
	}
}

// Disable Enter Key
function disableEnterKey(e)
{
	var key;     
	
	if(window.event)
	{
		key = window.event.keyCode; // IE
	}
	else
	{
		key = e.which; // Firefox
	}

	return (key != 13);
}

// Truncate a string and optionally create a roll over
function truncate(string, length, hover)
{
	if (string.length > length)
	{
		if (hover == true)
		{
			string = '<span title="' + string + '" style="cursor:help">' + string.substring(0, length) + '...</span>';
		}
		else
		{
			string = string.substring(0, length) + '...';
		}
	}

	return string;
}
