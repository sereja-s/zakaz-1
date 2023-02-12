<!-- Приветствие -->

<section class="generation">
	<div class="generation__logo logo" style="text-align: center;"><img src="<?= $this->img($this->set['promo_img']) ?>" alt="<?= $this->info['keywords'] ?>"></div>
	<div class="generation__title title" style="font-family: Arial, sans-serif;"><?= $this->info['name'] ?></div>
	<div class="generation__text"><?= $this->info['content'] ?></div>
	<div class="img" style="text-align:center">
		<img src="<?= $this->img($this->info['img']) ?>" alt="<?= $this->info['keywords'] ?>">
	</div>
</section>

<!-- Стройка -->

<section class="vintage" id="<?= $this->section1['section_id'] ?>">
	<div class="vintage__row">
		<div class="vintage__img-part" style='background: url("<?= $this->img($this->section1['img']) ?>") center top/cover no-repeat;'>
		</div>
		<div class="vintage__text-part">
			<div class="vintage__text-part-wrapper text-part-wrapper">
				<div class="vintage__title title"><?= $this->section1['name'] ?></div>
				<div class="vintage__text text"><?= $this->section1['content'] ?></div>
			</div>
			<div class="vintage__text-part-img" style='background: url("<?= $this->img($this->section1['img_horizontal']) ?>") center top/cover no-repeat;'>
			</div>
		</div>
	</div>
</section>

<!-- Сварка -->

<section class="boriosa" id="<?= $this->section2['section_id'] ?>">
	<div class="boriosa__row">
		<div class="boriosa__text-part">
			<div class="boriosa__text-part-wrapper text-part-wrapper">
				<div class="boriosa__title title"><?= $this->section2['name'] ?></div>
				<div class="boriosa__text text"><?= $this->section2['content'] ?></div>
			</div>
			<div class="boriosa__text-part-img" style='background: url("<?= $this->img($this->section2['img_horizontal']) ?>") center top/cover no-repeat;'>
			</div>
		</div>
		<div class="boriosa__img-part" style='background: url("<?= $this->img($this->section2['img']) ?>") center top/cover no-repeat;'>
		</div>
	</div>
</section>

<!-- Сантехника -->

<section class="retro" id="<?= $this->section3['section_id'] ?>">
	<div class="retro__row">
		<div class="retro__img-part-left" style='background: url("<?= $this->img($this->section3['img_vertical1']) ?>") center top/cover no-repeat;'>
		</div>
		<div class="retro__text-part">
			<div class="retro__text-part-wrapper text-part-wrapper">
				<div class="retro__title title"><?= $this->section3['name'] ?></div>
				<div class="retro__text text"><?= $this->section3['content'] ?></div>
			</div>
			<div class="retro__text-part-img" style='background: url("<?= $this->img($this->section3['img_horizontal']) ?>") center top/cover no-repeat;'>
			</div>
		</div>
		<div class="retro__img-part-right" style='background: url("<?= $this->img($this->section3['img_vertical2']) ?>") center top/cover no-repeat;'></div>
	</div>
</section>

<!-- Злектрика -->

<section class="vintage" id="<?= $this->section4['section_id'] ?>">
	<div class="vintage__row">
		<div class="vintage__img-part" style='background: url("<?= $this->img($this->section4['img']) ?>") center top/cover no-repeat;'>
		</div>
		<div class="vintage__text-part">
			<div class="vintage__text-part-wrapper text-part-wrapper">
				<div class="vintage__title title"><?= $this->section4['name'] ?></div>
				<div class="vintage__text text"><?= $this->section4['content'] ?></div>
			</div>
			<div class="vintage__text-part-img" style='background: url("<?= $this->img($this->section4['img_horizontal']) ?>") center top/cover no-repeat;'>
			</div>
		</div>
	</div>
</section>

<!-- секция-5 -->

<?php if (!empty($this->section5['name'])) : ?>

	<section class="boriosa" id="<?= $this->section5['section_id'] ?>">
		<div class="boriosa__row">
			<div class="boriosa__text-part">
				<div class="boriosa__text-part-wrapper text-part-wrapper">

					<div class="boriosa__title title"><?= $this->section5['name'] ?></div>
					<div class="boriosa__text text"><?= $this->section5['content'] ?></div>
				</div>
				<div class="boriosa__text-part-img" style='background: url("<?= $this->img($this->section5['img_horizontal']) ?>") center top/cover no-repeat;'>
				</div>
			</div>
			<div class="boriosa__img-part" style='background: url("<?= $this->img($this->section5['img']) ?>") center top/cover no-repeat;'>

			</div>
		</div>
	</section>

<?php endif; ?>

<!-- секция-6 -->

<?php if (!empty($this->section6['name'])) : ?>

	<section class="retro" id="<?= $this->section6['section_id'] ?>">
		<div class="retro__row">
			<div class="retro__img-part-left" style='background: url("<?= $this->img($this->section6['img_vertical1']) ?>") center top/cover no-repeat;'>

			</div>
			<div class="retro__text-part">
				<div class="retro__text-part-wrapper text-part-wrapper">

					<div class="retro__title title"><?= $this->section6['name'] ?></div>
					<div class="retro__text text"><?= $this->section6['content'] ?></div>
				</div>
				<div class="retro__text-part-img" style='background: url("<?= $this->img($this->section6['img_horizontal']) ?>") center top/cover no-repeat;'>
				</div>
			</div>
			<div class="retro__img-part-right" style='background: url("<?= $this->img($this->section6['img_vertical2']) ?>") center top/cover no-repeat;'></div>
		</div>
	</section>

<?php endif; ?>

<!-- секция-7 -->

<?php if (!empty($this->section7['name'])) : ?>

	<section class="vintage" id="<?= $this->section7['section_id'] ?>">
		<div class="vintage__row">
			<div class="vintage__img-part" style='background: url("<?= $this->img($this->section7['img']) ?>") center top/cover no-repeat;'>

			</div>
			<div class="vintage__text-part">
				<div class="vintage__text-part-wrapper text-part-wrapper">

					<div class="vintage__title title"><?= $this->section7['name'] ?></div>
					<div class="vintage__text text"><?= $this->section7['content'] ?></div>
				</div>
				<div class="vintage__text-part-img" style='background: url("<?= $this->img($this->section7['img_horizontal']) ?>") center top/cover no-repeat;'>
				</div>
			</div>
		</div>
	</section>

<?php endif; ?>

<?php if (!empty($comments)) : ?>

	<section class="twitter" id="otzyvy" style="padding-left: 7.5vw; padding-right: 7.5vw;">
		<div class="twitter__logo"><img src="<?= $this->img($this->info['img_comments']) ?>" alt="<?= $this->info['keywords'] ?>"></div>
		<div class="twitter__slider slider">
			<div class="owl-carousel owl-theme" id="slider">

				<?php foreach ($comments as $item) : ?>

					<div class="slider__content">
						<h2 class="slider__caption"><?= $item['name_comment'] ?> <span><?= $item['number_of_years'] ?></span></h2>
						<p class="slider__text"><?= $item['content'] ?></p>
					</div>

				<?php endforeach; ?>

			</div>
		</div>
	</section>

<?php endif; ?>

<!-- <section class=" shop" id="shop_now">
						<div class="shop__row">
							<div class="shop__part-1">
								<div class="shop__part-1-top">
									<div class="overflow-container">
										<div class="img-container"></div>
									</div>
									<div class="overflow-container">
										<div class="img-container"></div>
									</div>
								</div>
								<div class="shop__part-1-bottom">
									<div class="overflow-container">
										<div class="img-container"></div>
									</div>
								</div>
							</div>
							<div class="shop__part-2">
								<div class="overflow-container">
									<div class="overflow-container"></div>
									<div class="img-container"></div>
								</div>
								<div class="overflow-container">
									<div class="img-container"></div>
								</div>
							</div>
							<div class="shop__part3">
								<div class="shop__part-3-top"><a href="#shop_now">
										<div class="overflow-container">
											<div class="img-container"></div>
										</div>
										<div class="shop-now">
											<img class="shop__ico" src="assets/img/bottom/Shape_13.png" alt="">
											<div class="shop__title">Shop now</div>
										</div>
								</div></a>
								<div class="shop__part-3-bottom">
									<div class="overflow-container">
										<div class="img-container"></div>
									</div>
									<div class="overflow-container">
										<div class="img-container"></div>
									</div>
								</div>

							</div>
						</div>
						<div class="shop__button-container">
							<button class="shop__button"><a href="#otzyvy">Читать отзывы</a></button>
						</div>
	</section> -->

<!-- <section class="email">
	<a href=""><img src="assets/img/header/header_Logo.png" alt=""></a>
	<div class="email__title">Stay on the saddle!</div>
	<form action="#">
		<input type="email" placeholder="Enter your email...">
		<div class="email__button-container">
			<button class="email__button">GO</button>
		</div>
	</form>
</section> -->