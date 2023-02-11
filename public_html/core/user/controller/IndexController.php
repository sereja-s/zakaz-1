<?php

namespace core\user\controller;

/** 
 * Индексный контроллер пользовательской части
 */
class IndexController extends BaseUser
{
	protected function inputData()
	{
		// Выпуск №120
		parent::inputData();

		$comments = $this->model->get('comments', [

			'order_direction' => ['DESC'],
			'limit' => 5
		]);

		return compact('comments');

			//=============================================================================================================//

			// Выпуск №128 - массив преимуществ
			/* 	$advantages = $this->model->get('advantages', [
			'where' => ['visible' => 1],
			'order' => ['menu_position'],
			'limit' => 6
		]); */

			// Выпуск №128 | Вывод новостей
			/* $news = $this->model->get('news', [
			'where' => ['visible' => 1],
			'order' => ['date'],
			'order_direction' => ['DESC'],
			'limit' => 3
		]) */;

		/* return compact('advantages', 'news'); */
	}
}
