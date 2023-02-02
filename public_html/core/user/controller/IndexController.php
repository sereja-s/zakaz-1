<?php

namespace core\user\controller;

/** 
 * Индексный контроллер пользовательской части
 */
class IndexController extends BaseUser
{
	protected $name;

	protected function inputData()
	{
		// Выпуск №120
		parent::inputData();

		// Выпуск №124- Пользовательская часть | вывод акций (слайдер под верхним меню)
		$sales = $this->model->get('sales', [
			'where' => ['visible' => 1],
			'order' => ['menu_position']
		]);

		// Выпуск №128 - массив преимуществ
		$advantages = $this->model->get('advantages', [
			'where' => ['visible' => 1],
			'order' => ['menu_position'],
			'limit' => 6
		]);

		// Выпуск №128 | Вывод новостей
		$news = $this->model->get('news', [
			'where' => ['visible' => 1],
			'order' => ['date'],
			'order_direction' => ['DESC'],
			'limit' => 3
		]);

		// Выпуск №126
		// массив предложений (главная страница) +Выпуск №127
		$arrHits = [

			'hit' => [
				'name' => 'Хиты продаж',
				'icon' => '<svg><use xlink:href="' . PATH . TEMPLATE . 'assets/img/icons.svg#hit"</use></svg>'
			],
			'hot' => [
				'name' => 'Горячие предложения',
				'icon' => '<svg><use xlink:href="' . PATH . TEMPLATE . 'assets/img/icons.svg#hot"</use></svg>'
			],
			'sale' => [
				'name' => 'Распродажа',
				'icon' => '%'
			],
			'new' => [
				'name' => 'Новинки',
				'icon' => 'н'
			],

		];

		$goods = [];

		foreach ($arrHits as $type => $item) {

			$goods[$type] = $this->model->getGoods([
				'where' => [$type => 1, 'visible' => 1], // +Выпуск №127
				'limit' => 7 // выводим не более 7 товаров у которых включены соответствующие предложения
			]);
		}

		// Выпуск №125
		//$goods = $this->model->getGoods();

		// собираем переменные в массив и возвращаем в шаблон, что бы они стали доступными при выводе
		return compact('sales', 'arrHits', 'goods', 'advantages', 'news');
	}
}
