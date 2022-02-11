const consentEl = document.getElementsByClassName("cookie-consent")[0];

if (consentEl) {

	const now = new Date();

	if (localStorage.getItem("cookie-consent").expiry < now.valueOf()) {
		consentEl.remove();
	}

	consentEl.getElementsByTagName("button")[0].addEventListener("click", function () {

		const expiry = now.setTime(now.getTime() + (28 * 24 * 60 * 60 * 1000));

		const item = {
			value: true,
			expiry: expiry,
		};

		localStorage.setItem("cookie-consent", JSON.stringify(item));

		// drop a cookie for the backend
		document.cookie = "cookie-consent=true;expires=" + expiry + ";path=/";

		// remove the consent element
		consentEl.style.opacity = "0";
		consentEl.addEventListener("transitionend", () => consentEl.remove());

		return false;
	});

}
