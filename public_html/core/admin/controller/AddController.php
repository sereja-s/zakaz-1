<?php

namespace core\admin\controller;

use core\base\settings\Settings;

/** 
 * Класс добавления данных
 */
class AddController extends BaseAdmin
{
	// для корректного формирования пути для добавления данных в шаблоне объявим свойство (свойство необходимое для отправки форм)
	protected $action = 'add';

	protected function inputData()
	{
		if (!$this->userId) {

			$this->execBase();
		}

		// вызовем метод, для определения пришло ли что-нибудь через массив: Post
		$this->checkPost();

		// вызовем метод, формирует колонки, которые нам нужны и выбирает имя таблицы из параметров (или берёт таблицу по умолчанию)
		$this->createTableData();

		// вызовем метод для получения внешних данных
		$this->createForeignData();

		// вызовем метод для формирования первичных данных для сортировки информации в таблицах базы данных
		$this->createMenuPosition();

		// вызовем метод для формирования ключей и значений для input type radio (кнопок переключателей (да, нет и т.д.))
		$this->createRadio();

		// вызовем метод, который будет формировать наши данные (раскидывать их по блокам)
		// (создание выходных данных)
		$this->createOutputData();

		// вызовем метод, который будет создавать связи многие ко многим 
		$this->createManyToMany();

		// вызываем метод, который будет расширять функционал нашего фреймвёрка (работа с расширениями)
		return $this->expansion();

		/* $this->data = [
			'name' => 'Masha',
			'keywords' => 'Ключевая',
			'img' => '1.png',
			'gallery_img' => json_encode(['1.jpg', '2.png'])
		]; */
		//$this->manyAdd();
		//exit;

	}

	/* protected function manyAdd()
	{

		$fields = [

			'name' => 'Zina', 'menu_position' => 11
			//1 => ['name' => 'Marina', 'img' => '7.jpg', 'menu_position' => 1],
			//2 => ['name' => 'Tania', 'img' => '8.jpg'],
		];

		$files = [
			//'img' => '9.jpg',
			'gallery_img' => ['15.jpg', '16.jpg']
		];

		$this->model->add('teachers', [

			'fields' => $fields,
			'files' => $files
		]);
	} */
}
