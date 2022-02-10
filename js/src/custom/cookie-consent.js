const consentEl = document.getElementsByClassName('cookie-consent')[0];

if (consentEl) {

	if (localStorage.getItem('cookie-consent')) {
		consentEl.remove();
	}

	consentEl.getElementsByTagName('button')[0].addEventListener('click', function () {

		localStorage.setItem('cookie-consent', true);

		consentEl.style.opacity = '0';
		consentEl.addEventListener('transitionend', () => consentEl.remove());

		return false;
	});

}
