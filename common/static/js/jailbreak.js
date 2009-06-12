window.onload = function() {
	if (top.location != location) {
		top.location.href = document.location.href;
	}
}
