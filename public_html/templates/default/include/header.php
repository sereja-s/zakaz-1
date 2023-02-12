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

	<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">

	<meta name="description" content="<?= $this->info['description'] ?>">
	<meta name="keywords" content="<?= $this->info['keywords'] ?>">
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