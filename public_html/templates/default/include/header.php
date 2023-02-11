<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= $this->set['name'] ?></title>

	<?php $this->getStyles() ?>

	<!-- <link rel="stylesheet" href="./assets/css/style.css" />
	<link rel="stylesheet" href="./assets/css/owl.carousel.min.css">
	<link rel="stylesheet" href="./assets/css/owl.theme.default.min.css"> -->

	<link rel="shortcut icon" href="<?= PATH . TEMPLATE ?>assets/img/favicon.ico" type="image/x-icon">
	<link rel="icon" href="<?= PATH . TEMPLATE ?>assets/img/favicon.ico" type="image/x-icon">

	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="robots" content="">
</head>

<body>
	<div class="wrapper">
		<header class="header" style='background: url("<?= $this->img($this->set['bg_img']) ?>") center top/cover no-repeat fixed;'>
			<div class="header__nav-wrapper">
				<nav class="header__nav">
					<ul id="top-navigation">
						<li><a class="header__link" href="#<?= $this->section1['section_id'] ?>"><?= $this->section1['name'] ?></a></li>
						<li><a class="header__link" href="#<?= $this->section2['section_id'] ?>"><?= $this->section2['name'] ?></a></li>
						<li><a href="<?= $this->alias() ?>"><img class="header__logo" id="header__logo" src="<?= $this->img($this->set['img']) ?>" alt="<?= $this->set['name'] ?>"></a></li>
						<li><a class="header__link" href="#<?= $this->section3['section_id'] ?>"><?= $this->section3['name'] ?></a></li>
						<li><a class="header__link" href="#<?= $this->section4['section_id'] ?>"><?= $this->section4['name'] ?></a></li>

						<?php if (!empty($this->section5['name'])) : ?>
							<li><a class="header__link" href="#<?= $this->section5['section_id'] ?>"><?= $this->section5['name'] ?></a></li>
						<?php endif; ?>

						<?php if (!empty($this->section6['name'])) : ?>
							<li><a class="header__link" href="#<?= $this->section6['section_id'] ?>"><?= $this->section6['name'] ?></a></li>
						<?php endif; ?>

						<?php if (!empty($this->section7['name'])) : ?>
							<li><a class="header__link" href="#<?= $this->section7['section_id'] ?>"><?= $this->section7['name'] ?></a></li>
						<?php endif; ?>

						<!-- <li><a class="header__link" href="#contact">Контактная информация</a></li> -->
					</ul>
				</nav>
			</div>
			<div class="menu-icon" onclick="openNav()">
				<div class="menu-icon__wrapper">
					<div class="menu-icon__top" id="menu-icon__top"></div>
					<div class="menu-icon__middle" id="menu-icon__middle"></div>
					<div class="menu-icon__bottom" id="menu-icon__bottom"></div>
				</div>
			</div>
			<div class="tagline"><a href="#contact" style="text-decoration: none; color: #FFFFFF"><?= $this->set['name'] ?> | контакты</a></div>
		</header>
		<div class="main-part">