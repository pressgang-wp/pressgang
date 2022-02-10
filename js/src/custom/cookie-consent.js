const consentEl = document.getElementsByClassName('cookie-consent');

if (consentEl) {

	if (localStorage.getItem('cookie-consent')) {
		consentEl.style.display==="none";
	}

	consentEl.getElementsByClassName('.btn').addEventListener('click', function () {

		localStorage.setItem('cookie-consent', true);

		consentEl.style.opacity = '0';
		consentEl.addEventListener('transitionend', () => consentEl.remove());

		return false;
	});

}
