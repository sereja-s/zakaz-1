</main>
<footer class="footer">
	<div class="container">
		<div class="footer__wrapper">
			<div class="footer__top">
				<div class="footer__top_logo">
					<a href="<?= $this->alias() ?>"><img src="<?= $this->img($this->set['img']) ?>" alt="<?= $this->set['name'] ?>"></a>
					<!-- <img src="assets/img/Logo.svg" alt=""> -->
				</div>
				<div class="footer__top_menu">
					<ul>

						<li>
							<a href="http://somesite.ru/catalog/"><span>Каталог</span></a>
						</li>

						<li>
							<a href="http://somesite.ru/about/"><span>О нас</span></a>
						</li>

						<li>
							<a href="http://somesite.ru/delivery/"><span>Доставка и оплата</span></a>
						</li>

						<li>
							<a href="http://somesite.ru/contacts/"><span>Контакты</span></a>
						</li>

						<li>
							<a href="http://somesite.ru/news/"><span>Новости</span></a>
						</li>

						<li>
							<a href="http://somesite.ru/sitemap/"><span>Карта сайта</span></a>
						</li>

					</ul>
				</div>
				<div class="footer__top_contacts">
					<div><a href="mailto:test@test.ru">test@test.ru</a></div>
					<div><a href="tel:+74842750204">+7 (4842) 75-02-04</a></div>
					<div><a class="js-callback">Связаться с нами</a></div>
				</div>
			</div>
			<div class="footer__bottom">
				<div class="footer__bottom_copy">Copyright</div>
			</div>
		</div>
	</div>
</footer>

<div class="hide-elems">
	<svg>
		<defs>
			<linearGradient id="rainbow" x1="0" y1="0" x2="50%" y2="50%">
				<stop offset="0%" stop-color="#7282bc" />
				<stop offset="100%" stop-color="#7abfcc" />
			</linearGradient>
		</defs>
	</svg>
</div>

<!--<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.2.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.0.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/ScrollMagic.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/plugins/animation.gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/plugins/debug.addIndicators.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/TweenMax.min.js"></script>
<script src="assets/js/ScrollMagic.min.js"></script>
<script src="assets/js/animation.gsap.min.js"></script>
<script src="assets/js/bodyscrolllock/bodyScrollLock.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/script.js"></script> -->

<!-- убрать -->
<!-- <script src="assets/js/freeHost.js"></script> -->
<!-- убрать -->

<?php $this->getScripts() ?>

</body>

</html>