<?php

namespace core\base\settings;

// класс настроек плагинов
class ShopSettings
{
	// импорт трейта для подключения и работы с плагинами
	use BaseSettings;

	private $routes = [
		'plugins' => [
			//'path' => 'lalala/',			
			'dir' => false,
			'routes' => [
				//'product' => 'goods'
			]
		]

		//'p' => [4, 5, 6]
	];

	private $templateArr = [
		'text' => ['price', 'short', 'name'],
		'textarea' => ['goods_content']
	];
}
