<?php if (!empty($data)) : ?>

	<div class="container">

		<?= $this->breadcrumbs ?>

		<h1 class="page-title h1"><?= $data['name'] ?></h1>
		<h3 style="color: #006d77; margin-top: 10px"><?= $data['short_content'] ?></h3>
	</div>

	<section class="card-main">
		<div class="container">
			<div class="card-main__wrapper">

				<div class="card-main-gallery-thumb">

					<?php if (!empty($data['gallery_img'])) : ?>

						<div class="card-main-gallery-thumb__container swiper-container">
							<div class="swiper-wrapper">

								<div class="card-main-gallery-thumb__slide swiper-slide">
									<picture class="card-main-gallery-thumb__img">
										<img src="<?= $this->img($data['img']) ?>" alt="<?= $data['name'] ?>">
									</picture>
								</div>

								<?php foreach (json_decode($data['gallery_img'], true) as $item) : ?>

									<div class="card-main-gallery-thumb__slide swiper-slide">
										<picture class="card-main-gallery-thumb__img">
											<img src="<?= $this->img($item) ?>" alt="<?= $data['name'] ?>">
										</picture>
									</div>

								<?php endforeach; ?>


							</div>
						</div>

					<?php endif; ?>

				</div>

				<div class="card-main-gallery-slider">
					<div class="card-main-gallery-slider__container swiper-container">
						<div class="swiper-wrapper">

							<div class="card-main-gallery-slider__slide swiper-slide">
								<a href="<?= $this->img($data['img']) ?>" class="card-main-gallery-slider__img" data-fancybox>
									<img src="<?= $this->img($data['img']) ?>" alt="<?= $data['name'] ?>">
								</a>
							</div>

							<?php if (!empty($data['gallery_img'])) : ?>

								<?php foreach (json_decode($data['gallery_img'], true) as $item) : ?>

									<div class="card-main-gallery-slider__slide swiper-slide">
										<a href="<?= $this->img($item) ?>" class="card-main-gallery-slider__img" data-fancybox>
											<img src="<?= $this->img($item) ?>" alt="<?= $data['name'] ?>">
										</a>
									</div>

								<?php endforeach; ?>

							<?php endif; ?>

						</div>
					</div>
				</div>
				<div class="card-main-info" data-productContainer>
					<div class="card-main-info__description">
						<div class="card-main-info-price">
							<div class="card-main-info-price__text">
								Цена:
							</div>

							<div class="card-main-info-price__num">

								<span><?= $data['price'] ?></span> руб.

							</div>

							<?php if (!empty($data['old_price'])) : ?>

								<div class="card-main-info-price__old">

									<span><?= $data['old_price'] ?></span> руб.

								</div>

							<?php endif; ?>

						</div>

						<?php if (!empty($data['article'])) : ?>

							<div class="card-main-info__number">

								Арт. <?= $data['article'] ?>

							</div>

						<?php endif; ?>


						<?php if (!empty($data['filters'])) : ?>

							<div class="card-main-info__table">

								<?php $counter = 0; ?>

								<?php foreach ($data['filters'] as $item) : ?>

									<?php

									if (++$counter > 3) break;

									?>

									<div class="card-main-info__table-row">

										<div class="card-main-info__table-item">

											<?= $item['name'] ?>

										</div>

										<div class="card-main-info__table-item">

											<?= implode(', ', array_column($item['values'], 'name')) ?>

										</div>

									</div>

								<?php endforeach; ?>

							</div>

							<?php if (count($data['filters']) > 3) : ?>

								<a href="card.html#" class="card-main-info__more more-button button2">
									Показать все
								</a>

							<?php endif; ?>

						<?php endif; ?>

					</div>
					<div class="card-main-info__sale">
						<div class="card-main-info-size">
							<label class="card-main-info-size__item js-sizeCounter" data-max="10">
								<input type="radio" name="size[]" class="visually-hidden">
								<input type="number" class="visually-hidden js-counterValue" name="size" value="1">
								<span class="card-main-info-size__head">
									Количество:
								</span>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control button card-main-info-size__control_minus js-counterDecrement" data-quantityMinus></span>
									<span class="card-main-info-size__count js-counterShow" data-quantity><?= $this->cart['goods'][$data['id']]['qty'] ?? 1 ?></span>
									<span class="card-main-info-size__control button card-main-info-size__control_plus js-counterIncrement" data-quantityPlus></span>
								</span>
							</label>
						</div>
						<div class="card-main-info__buttons">
							<a data-addToCart="<?= $data['id'] ?>" <?= !empty($this->cart['goods'][$data['id']]) ? 'data-toCartAdded' : '' ?> href="#" class="card-main-info__button button-basket button-blue button-big button">
								<svg>
									<use xlink:href="<?= PATH . TEMPLATE ?>assets/img/icons.svg#basket"></use>
								</svg>
								<span>в корзину</span>
							</a>
							<a data-addToCart="<?= $data['id'] ?>" data-onClick href="#" class="card-main-info__button button-darkcyan button-big button">
								купить сейчас
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="card-tabs">
		<div class="card-tabs__wrapper">
			<div class="card-tabs__top">
				<div class="container">
					<span class="card-tabs__background"></span>
					<div class="card-tabs__top-items">
						<div class="card-tabs__top-wrapper">

							<div class="card-tabs__toggle tabs__toggle tabs__toggle_active">
								<span class="card-tabs__toggle-text">описание</span>
							</div>

							<div class="card-tabs__toggle tabs__toggle ">
								<span class="card-tabs__toggle-text">характеристики</span>
							</div>

							<div class="card-tabs__toggle tabs__toggle ">
								<span class="card-tabs__toggle-text">Доставка и оплата</span>
							</div>

						</div>
					</div>
				</div>
			</div>
			<div class="card-tabs__bottom">
				<div class="container">
					<div class="card-tabs__bottom-wrapper">

						<div class="card-tabs-item-wrapper tabs__tab">

							<!-- описание -->

							<?= $data['content'] ?>

						</div>

						<div class="card-tabs-item-wrapper tabs__tab">
							<div class="card-main-info__table main-info card-main-indfo_toggle">

								<!-- Характеристики (фильтры) -->

								<?php if ($data['filters']) : ?>

									<?php foreach ($data['filters'] as $item) : ?>

										<div class="card-main-info__table-row">
											<div class="card-main-info__table-item">

												<?= $item['name'] ?>

											</div>
											<div class="card-main-info__table-item">

												<?= implode(', ', array_column($item['values'], 'name')) ?>

											</div>
										</div>

									<?php endforeach; ?>

								<?php endif; ?>

							</div>
						</div>

						<div class="card-tabs-item-wrapper tabs__tab">

							<!-- доставка и оплата -->

							<?= !empty($deliveryInfo['content']) ? $deliveryInfo['content'] : '' ?>

						</div>

					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="card-slider">
		<div class="container">
			<div class="card-slider__wrapper">
				<div class="card-slider__title h2">
					С этим товаром покупают
				</div>
				<div class="card-slider__buttons slider__buttons">
					<div class="card-slider__prev slider__prev slider__button button">
					</div>
					<div class="card-slider__next slider__next slider__button button">
					</div>
				</div>
				<div class="card-slider-slider">
					<div class="card-slider-slider__container swiper-container">
						<div class="swiper-wrapper">

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>

								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>

								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>
								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>
								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>
								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>
								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

							<div class="card-item swiper-slide ">
								<div class="card-item__tabs_image">
									<img src="assets/img/additional_offer.png" alt="">
								</div>
								<div class="card-item__tabs_description">
									<div class="card-item__tabs_name">
										<span>Размораживатель замков "ГЛАВДОР" GL-498,</span>
										с силиконом, 70 мл /56
									</div>
									<div class="card-item__tabs_price">
										Цена: <span class="card-item_old-price">98 руб.</span> <span class="card-item_new-price">72 руб.</span>
									</div>
								</div>
								<button class="card-item__btn">
									<svg>
										<use xlink:href="/assets/img/icons.svg#basket"></use>
									</svg>
									<span>в корзину</span>
								</button>
								<span class="card-main-info-size__body">
									<span class="card-main-info-size__control card-main-info-size__control_minus js-counterDecrement"></span>
									<span class="card-main-info-size__count js-counterShow">1</span>
									<span class="card-main-info-size__control card-main-info-size__control_plus js-counterIncrement"></span>
								</span>
								<div class="icon-offer">
									<svg>
										<use xlink:href="/assets/img/icons.svg#hot"></use>
									</svg>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="feedback feedback-internal">
		<div class="feedback__name subheader h2">оставить заявку</div>
		<form action="card.html" class="feedback__form">
			<div class="feedback__form_left">
				<input type="text" class="input-text feedback__input" placeholder="Ваше имя">
				<input type="email" class="input-text feedback__input" placeholder="E-mail">
				<input type="text" class="input-text feedback__input js-mask-phone" placeholder="Телефон">
			</div>
			<div class="feedback__form_right">
				<textarea class="input-textarea feedback__textarea" placeholder="Ваш вопрос"></textarea>
			</div>
			<div class="feedback__privacy">
				<label class="checkbox">
					<input type="checkbox" />
					<div class="checkbox__text">Соглашаюсь с правилами обработки персональных данных</div>
				</label>
			</div>
			<button type="submit" class="form-submit feedback__submit">Отправить</button>
		</form>
	</section>

<?php endif; ?>