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
					alert('Error: The ' + element.name + ' field is required.');
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

function ajaxSubmit(form) {
	var params = '';
	
	if (params = getForm(form)) {
		createRequest();
		request.open(form.method, form.action, true);
		
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.setRequestHeader("Content-length", params.length);
		request.setRequestHeader("Connection", "close");

		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				var responseObject = eval( "(" + request.responseText + ")" );

				var responseElement = document.createElement('div');
				responseElement.className = responseObject.type;

				var responseMessage = document.createTextNode(responseObject.message);

				responseElement.appendChild(responseMessage);

				form.insertBefore(responseElement, form.firstChild);
			}
		}

		request.send(params);
	}
}
