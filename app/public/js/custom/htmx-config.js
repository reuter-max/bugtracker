document.addEventListener('htmx:beforeSwap', function (event) {
	// Formular-Fehler (401 Login, 422 Validierung) sollen trotzdem
	// den Inhalt austauschen, nicht nur "echte" Server-Fehler (500 etc.)
	if (event.detail.xhr.status === 401 || event.detail.xhr.status === 422) {
		event.detail.shouldSwap = true;
		event.detail.isError = false;
	}
});

document.body.addEventListener('htmx:configRequest', function (event) {
	const unsafeMethods = ['post', 'put', 'delete', 'patch'];
	if (unsafeMethods.includes(event.detail.verb)) {
		event.detail.headers['X-CSRF-Name'] = document.body.dataset.csrfName;
		event.detail.headers['X-CSRF-Value'] = document.body.dataset.csrfValue;
	}
});

// Modal automatisch oeffnen, sobald htmx Inhalt in #modal-content laedt
document.addEventListener('htmx:afterSwap', function (event) {
	if (event.target.id === 'modal-content' && event.target.innerHTML.trim() !== '') {
		document.getElementById('modal').showModal();
	}
});

// Server kann per "HX-Trigger: closeModal"-Header das Schliessen ausloesen
document.addEventListener('closeModal', function () {
	document.getElementById('modal').close();
});