<?php

namespace core\admin\controller;

use core\base\exceptions\RouteException;
use mysql_xdevapi\Exception;

/** 
 * Контроллер редактирования данных в административной панели
 * 
 * Методы: protected function createData()
 */
class EditController extends BaseAdmin
{
	// свойство необходимое для отправки форм для редактирования данных
	protected $action = 'edit';

	protected function inputData()
	{
		if (!$this->userId) {
			$this->execBase();
		}

		// вызываются все методы, применяемые в AddController (т.к. они адаптированы и для редактирования)
		$this->checkPost();

		$this->createTableData();

		// вызовем метод, который получает данные из БД для редактирования (Выпуск №88)		 
		$this->createData();

		$this->createForeignData();

		$this->createMenuPosition();

		$this->createRadio();

		$this->createOutputData();

		$this->createManyToMany();

		// сохраним в свойство путь к шаблону, которое будет подано на вход методу: render (в методе: outputData (class 
		// BaseAdmin)) и у нас на EditController будет подгружаться: add-шаблон, который со свойством: protected $action = 
		// 'edit' позволит редактировать данные
		$this->template = ADMIN_TEMPLATE . 'add';

		return $this->expansion();
	}

	/** 
	 * Метод получает данные из БД для редактирования (Выпуск №88)
	 */
	protected function createData()
	{
		//exit;

		// очистим и получим $id в переменную
		// is_numeric()— определяет, является ли переменная числом или числовой строкой
		$id = is_numeric($this->parameters[$this->table]) ?
			$this->clearNum($this->parameters[$this->table]) :
			$this->clearStr($this->parameters[$this->table]);

		if (!$id) {
			throw new RouteException('Не корректный идентификатор - ' . $id .
				' при редактировании таблицы - ' . $this->table);
		}

		// получим данные для редактирования по id
		$this->data = $this->model->get($this->table, [
			'where' => [$this->columns['id_row'] => $id]
		]);

		// после получения данных, сохраним в свойство: $this->data то, что лежит в нулевой ячейке: data[0]
		$this->data && $this->data = $this->data[0];
	}
}
