const userId = document.body.dataset.userId;

if (userId) {
	const ws = new WebSocket(`ws://${window.location.hostname}:${document.body.dataset.wsPort}/?userId=${userId}`);

	const notificationButton = document.getElementById('notification-button');

	notificationButton?.addEventListener('click', () => {
		clearNotificationAlert();
	});

	ws.onmessage = function (event) {
		const data = JSON.parse(event.data);
		const inbox = document.getElementById('notification-list');
		if (inbox) {
			const entry = document.createElement('div');
			entry.className = `notification notification-${data.type} unread`;
			entry.textContent = data.message;
			inbox.prepend(entry);
		}

		let badge = document.getElementById('notification-badge');
		if (badge) {
			badge.textContent = parseInt(badge.textContent, 10) + 1;
		} else if (notificationButton) {
			badge = document.createElement('span'); badge.className = 'badge';
			badge.textContent = '1';
			notificationButton.appendChild(badge);
		}
	};
}