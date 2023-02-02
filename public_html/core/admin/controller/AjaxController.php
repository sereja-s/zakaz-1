<?php

namespace core\admin\controller;

use libraries\FileEdit;

/** 
 * Ajax-контроллер админки (Выпуск №67,95) 
 */
class AjaxController extends BaseAdmin
{
	public function ajax()
	{
		//return 'ADMIN AJAX';

		// Выпуск №69 (+Выпуск №96)
		if (isset($this->ajaxData['ajax'])) {

			// +Выпуск №95 
			$this->execBase();

			// +Выпуск №96
			foreach ($this->ajaxData as $key => $item) {

				$this->ajaxData[$key] = $this->clearStr($item);
			}

			switch ($this->ajaxData['ajax']) {
					//case 'sitemap':
					//return (new CreatesitemapController())->inputData($this->ajaxData['links_counter'], false);
					//break;

					// Выпуск №95- асинхронная отправка формы на сервер
				case 'editData':
					// сформируем $_POST['return_id']
					$_POST['return_id'] = true;
					$this->checkPost();
					return json_encode(['success' => 1]);
					break;

					// Выпуск №96- асинхронный пересчет позиций вывода данных при смене родительской категории
				case 'change_parent':
					return $this->changeParent();
					break;

					// Выпуск №105 php | js | поиск по административной панели
				case 'search':
					return $this->search();
					break;

					// Выпуск №107- осуществим загрузку файлов на сервер, добавляемых через визуальный редактор TinyMce 5
				case 'wyswyg_file':
					$a = 1;
					$fileEdit = new FileEdit();
					$fileEdit->setUniqueFile(false);
					$file = $fileEdit->addFile($this->clearStr($this->ajaxData['table']) . '/content_file/');
					return ['location' => PATH . UPLOAD_DIR . $file[key($file)]];
					break;
			}
		}

		return json_encode(['success' => '0', 'message' => 'No ajax variable']);
	}

	/** 
	 * Метод работы поиска в админке ( Выпуск №105)
	 */
	protected function search()
	{
		$data = $this->clearStr($this->ajaxData['data']);

		$table = $this->clearStr($this->ajaxData['table']);

		// вызовем метод модели
		// здесь 3-ий параметр это кол-во подсказок (ссылок) показываемых при работе с поисковой строкой
		return $this->model->search($data, $table, 20);
	}

	/** 
	 * Метод работающий при смене родительской категории у элемента в админке (Выпуск №96)
	 */
	protected function changeParent()
	{
		// вернём результат запроса к административной панели
		// на вход метода: get (1- из какой таблицы вернуть данные, 2- необходимые параметры)
		return $this->model->get($this->ajaxData['table'], [
			// какие вернуть поля
			'fields' => ['COUNT(*) as count'],
			// по какому условию вернуть (здесь- где parent_id равен тому, что пришло в ячейки: $this->ajaxData['parent_id']
			'where' => ['parent_id' => $this->ajaxData['parent_id']],
			// к count не нужно делать конкатенацию
			'no_concat' => true
		])[0]['count'] + $this->ajaxData['iteration']; // вернуть то, что придёт в нулевом элементе (в ячейке: count) 
		// вернуть исходя из того, что пришло в iteration (т.е. прибавим приведённое к числу текущее значение (из ячейки: ajaxData['iteration']))
	}
}
