<footer class="footer" id="contact">
	<div class="footer__row">
		<div class="footer__adress">
			<div><?= $this->set['address'] ?></div>
			<div><?= $this->set['address_big'] ?></div>
			<div><a href="tel:<?= preg_replace('/[^+\d]/', '', $this->set['phone']) ?>"><?= $this->set['phone'] ?></a></div><br>
			<div><a href="mailto:<?= $this->set['email'] ?>"><?= $this->set['email'] ?></a></div>
		</div>
		<div class="footer__social-wrapper">
			<div class="footer__social twitter-ico" style='background: url("<?= PATH . TEMPLATE ?>assets/img/bottom/twitter.svg") center center/contain no-repeat;'></div>
			<div class="footer__social facebook-ico" style='background: url("<?= PATH . TEMPLATE ?>assets/img/bottom/facebook.svg") center center/contain no-repeat;'></div>
			<div class="footer__social pinterest-ico" style='background: url("<?= PATH . TEMPLATE ?>assets/img/bottom/pinterest.svg") center center/contain no-repeat;'></div>
		</div>
		<div class="footer__yebo">
			<span>проект Sait_postroen</span>
			<!-- <img src="<?= PATH . TEMPLATE ?>assets/img/bottom/Y_E_B_O_Logo.png" alt=""> -->
		</div>
	</div>
</footer>

<div class="arrow" id="arrowTop" hidden>
	<svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M21.22 10.61L10.61 -4.63778e-07L1.44357e-06 10.61L2.6288 13.2388L10.61 5.25759L18.5912 13.2388L21.22 10.61Z" fill="#445154" />
	</svg>
</div>
</div>

<?php $this->getScripts() ?>

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="./assets/js/script.js"></script>
<script src="./assets/js/OwlCarousel2-2.3.4/dist/owl.carousel.min.js"></script> -->


</body>

</html>