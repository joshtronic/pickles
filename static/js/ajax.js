var request = null;

function getForm(form) {
	var params = 'ajax=true';
	var count  = form.elements.length;

	for (var i = 0; i < count; i++) {
		element = form.elements[i];

		switch (element.type) {
			case 'hidden':
			case 'password':
			case 'text':
			case 'textarea':
				// Check if it's required
				if (element.title == 'required' && element.value == '') {
					alert('Error: The ' + element.name.replace('_', ' ') + ' field is required.');
					element.focus();
					return false;
				}

				params += '&' + element.name + '=' + encodeURI(element.value);
				break;

			case 'checkbox':
			case 'radio':
				if (element.checked) {
					params += '&' + element.name + '=' + encodeURI(element.value);
				}
				break;

			case 'select-one':
   				params += '&' + element.name + "=" +  element.options[element.selectedIndex].value;
				break;
		}
	}

	return params;
}

function createRequest() {
    try {
        request = new XMLHttpRequest();
    } catch (trymicrosoft) {
        try {
            request = new ActiveXObject("Msxml12.XMLHTTP");
        } catch (othermicrosoft) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                request = null;
            }
        }
    }

    if (request == null) {
        alert("Error creating request object!");
    }
}

function ajaxSubmit(form, customHandler, beforeOrAfter) {
	var params = '';
	var customHandler = (customHandler == null) ? null     : customHandler;
	var beforeOrAfter = (beforeOrAfter == null) ? 'before' : beforeOrAfter;

	if (params = getForm(form)) {
		createRequest();
		request.open(form.method, form.action, true);
		
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.setRequestHeader("Content-length", params.length);
		request.setRequestHeader("Connection", "close");

		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				var responseObject  = eval( "(" + request.responseText + ")" );
				var responseElement = document.createElement('div');

				if (customHandler) {
					responseElement = window[customHandler](responseObject, responseElement);
				}
				else {
					var responseMessage = document.createTextNode(responseObject.message);
					responseElement.className = responseObject.type;
					responseElement.appendChild(responseMessage);
				}
				
				if (document.getElementById('ajaxResponse') != null) {
					form.removeChild(document.getElementById('ajaxResponse'));
				}

				responseElement.id = 'ajaxResponse';
				form.insertBefore(responseElement, (beforeOrAfter == 'before') ? form.firstChild : form.lastChild);

			}
		}

		request.send(params);
	}
}
