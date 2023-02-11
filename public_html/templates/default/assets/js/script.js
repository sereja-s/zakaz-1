// sctroll to top
arrowTop.onclick = function () {
	window.scrollTo(pageXOffset, 0);
	// после scrollTo возникнет событие "scroll", так что стрелка автоматически скроется
};
window.addEventListener('scroll', function () {
	arrowTop.hidden = (pageYOffset < document.documentElement.clientHeight);
});


// Open/close overlay navigation
function openNav() {
	document.getElementById("top-navigation").classList.toggle("active");
	document.getElementById("menu-icon__top").classList.toggle("active");
	document.getElementById("menu-icon__middle").classList.toggle("active");
	document.getElementById("menu-icon__bottom").classList.toggle("active");
}

// уменьшение размера навигации при скролле
window.addEventListener('scroll', function () {
	if (pageYOffset > 299) {
		document.getElementById("header__logo").classList.add('scrolled');
	} else {
		document.getElementById("header__logo").classList.remove('scrolled');
	}
});

// owl carousel twitter
$(document).ready(function () {
	const slider = $("#slider").owlCarousel({
		loop: true,
		autoplay: true,
		autoplayTimeout: 20000,
		smartSpeed: 10000,
		margin: 50,
		responsiveBaseWidth: '#otzyvy',
		//nav: true,
		responsive: {
			0: {
				items: 1
			}
		}
	});
});