<?php

namespace core\base\controller;

use core\base\settings\Settings;

/**
 * Класс распределит запросы учитывая административная это панель или пользовательская часть сайта (Выпуск №67)
 * Методы: public function route(); protected function generateToken()
 */
class BaseAjax extends BaseController
{
	/** 
	 * Метод определит какой контроллер подключать 
	 */
	public function route()
	{
		$route = Settings::get('routes');

		$controller = $route['user']['path'] . 'AjaxController';
		// данные могут прийти из js могут прийти ПОСТом или ГЕТом
		// сделаем проверку каким способои данные пришли
		$data = $this->isPost() ? $_POST : $_GET;


		// +Выпуск №116
		// сделаем проверку и сгенерируем уникальный токен (применим на странице: login.php для защиты от роботизированного подбора пароля)
		if (!empty($data['ajax']) && $data['ajax'] === 'token') {

			return $this->generateToken();
		}


		// +Выпуск №95 (асинхронная отправка формы на сервер)
		// заменим слеши на экранированные (где менять- передадим 3-им параметром)
		$httpReferer = str_replace('/', '\/', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . PATH . $route['admin']['alias']);

		// если существует ячейка: $data['ADMIN_MODE'] или шаблон указанный 1-ым параметром найден в ячейке (2-ой параметр)
		if (isset($data['ADMIN_MODE']) || preg_match('/^' . $httpReferer . '(\/?|$)/', $_SERVER['HTTP_REFERER'])) {

			// разрегистрируем (удалим) ячейку: $data['ADMIN_MODE'] У нас она просто флаг
			unset($data['ADMIN_MODE']);

			// и переопределяем переменную: $controller (подключаем: AjaxController) Иначе это будет пользовательский контроллер
			$controller = $route['admin']['path'] . 'AjaxController';
		}

		// Выпуск №69
		$controller = str_replace('/', '\\', $controller);

		$ajax = new $controller;

		// (+Выпуск №96)
		// данные будем заносить в св-во: ajaxData 
		$ajax->ajaxData = $data;

		// +Выпуск №96
		// в переменную сохраним результат работы метода: ajax()
		$res = $ajax->ajax();

		if (is_array($res) || is_object($res)) {

			$res = json_encode($res);
		} elseif (is_int($res)) {

			// приведём результат к числу с плавающей точкой (что бы сервер не ругался)
			$res = (float)$res;
		}

		return $res;
	}

	/** 
	 * Метод генерирующая уникальный токен (Выпуск №116)
	 */
	protected function generateToken()
	{
		return $_SESSION['token'] = md5(mt_rand(0, 999999) . microtime());
	}
}
