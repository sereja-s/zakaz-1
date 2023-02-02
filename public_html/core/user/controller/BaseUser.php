<?php

namespace core\user\controller;

use core\user\model\Model;

/** 
 * Пользовательский контроллер с базовым функционалом (абстрактный класс) -Выпуск №120
 *  Методы: protected function img(); protected function alias()
 *          protected function wordsForCounter(); protected function showGoods(); protected function pagination();
 *          protected function addToCart(); protected function totalSum(); 
 *          protected function updateCart(); public function clearCart(); protected function deleteCartData()
 *          protected function &getCart();
 */
abstract class BaseUser extends \core\base\controller\BaseController
{
	protected $model;
	protected $table;

	// Выпуск №122- Пользовательская часть | Вывод данных в хедер сайта
	/** 
	 * свойство, в которое будем класть данные из таблицы: settings (настройки системы: лого, телефон, эл.почта и т.д.)
	 */
	protected $set;

	/** 
	 * свойство с данными для меню (каталог)
	 */
	protected $menu;

	/** 
	 * свойство для корзины (Выпуск №140)
	 */
	protected $cart = [];

	/** 
	 * Выпуск №129 (св-во для хлебных крошек)
	 */
	protected $breadcrumbs;

	/** 
	 * св-во в котором будем держать данные пользователя (Выпуск №145)
	 */
	protected $userData = [];

	/** 
	 * Проектные свойства (Выпуск №123)
	 */
	protected $socials;


	protected function inputData()
	{
		// инициализируем стили и скрипты На вход здесь ничего не передаём
		$this->init();

		// получим модель (если ещё не получена)
		!$this->model && $this->model = Model::instance();

		// Выпуск №122- Пользовательская часть | Вывод данных в хедер сайта
		// (св-во: $this->set будет доступно везде (без рендеринга), где будет вызываться header и footer, т.к. это св-во
		// любого обхекта класса, который наследует: BaseUser и поэтому его никуда передавать не надо)
		$this->set = $this->model->get('settings', [
			'order' => ['id'],
			'limit' => 1
		]);

		// укажежем, что если что то пришло в свойство: $this->set, то сохраним в нём только нулевой элемент массива, который пришёл (первый по очереди)
		$this->set && $this->set = $this->set[0];

		// Выпуск №142
		// получим данные для корзины
		if (!$this->isAjax() && !$this->isPost()) {

			$this->getCartData();
		}

		// получим в св-во: $this->menu, в ячейку: ['catalog'], то что хранится в соответствующей таблице БД
		$this->menu['catalog'] = $this->model->get('catalog', [
			'where' => ['visible' => 1, 'parent_id' => null],
			'order' => ['menu_position']
		]);

		// получим в св-во: $this->menu, в ячейку: ['information'], то что хранится в соответствующей таблице БД
		$this->menu['information'] = $this->model->get('information', [
			'where' => ['visible' => 1, 'show_top_menu' => 1],
			'order' => ['menu_position']
		]);

		// получим в св-во: $this->socials, то что хранится в соответствующей таблице БД
		$this->socials = $this->model->get('socials', [
			'where' => ['visible' => 1],
			'order' => ['menu_position']
		]);
	}

	protected function outputData()
	{
		// +Выпуск №129
		// в переменной сохраним результат работы ф-ии php: func_get_arg()- Возвращает указанный аргумент из списка 
		// аргументов пользовательской функции (здесь- порядковый номер: 0)
		$args = func_get_arg(0);
		$vars = $args ? $args : [];

		// +Выпуск №129 (добавили в шаблон путь к файлу с хлебными крошками)
		$this->breadcrumbs = $this->render(TEMPLATE . 'include/breadcrumbs');

		if (!$this->content) {

			//if(!$this->template) { $this->template = ADMIN_TEMPLATE . 'show'; }

			$this->content = $this->render($this->template, $vars);
		}

		$this->header = $this->render(TEMPLATE . 'include/header', $vars);
		$this->footer = $this->render(TEMPLATE . 'include/footer', $vars);

		return $this->render(TEMPLATE . 'layout/default');
	}

	/** 
	 * Метод для удобного заполнения пути к изображению в файлах (Выпуск №120, 121)
	 */
	protected function img($img = '', $tag = false)
	{
		// если картинка отсутствует и есть папка с изображениями по умолчанию
		if (!$img && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . DEFAULT_IMAGE_DIRECTORY)) {

			// scandir() — возвращает список файлов и каталогов внутри указанного пути
			$dir = scandir($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . DEFAULT_IMAGE_DIRECTORY);

			// preg_grep() — возвращает записи массива, соответствующие шаблону ( или регулярному выражению)
			// в переменную: $imgArr положим то что в названии будет указывать на IndexController далее точка и какое то 
			// расширение, если такого нет, то будем искать файл с названием: default далее точка и какое то расширение
			$imgArr = preg_grep('/' . $this->getController() . '\./i', $dir) ?: preg_grep('/default\./i', $dir);

			// если в переменную: $imgArr что то пришло, то в переменную $img сохраним выражение, где 
			// array_shift()— возвращает массив поданный на вход, исключив первый элемент (все ключи числового массива 
			// будут изменены, чтобы начать отсчет с нуля) +Выпуск №121
			$imgArr && $img = DEFAULT_IMAGE_DIRECTORY . '/' . array_shift($imgArr);
		}

		// Выпуск №121
		if ($img) {

			// сформируем путь к изображению
			$path = PATH . UPLOAD_DIR . $img;

			// если в параметрах передали: $tag = false
			if (!$tag) {

				// то вернём путь 
				return $path;
			}

			// если в параметрах передали: $tag = true, то вернём:
			echo '<img src="' . $path . '" alt="image" title="image">';
		}

		return '';
	}

	/** 
	 * Метод формирования ссылок в пользовательской части (Выпуск №121)
	 */
	protected function alias($alias = '', $queryString = '')
	{

		$str = '';

		if ($queryString) {

			if (is_array($queryString)) {

				foreach ($queryString as $key => $item) {

					// к переменной: $str конкатенируем символ: знак вопроса (если в строку ничего не пришло) иначе- символ амперсанд
					$str .= (!$str ? '?' : '&');

					if (is_array($item)) {

						// к ключу конкатенируем символ квадратных скобок
						$key .= '[]';

						foreach ($item as $k => $v) {

							// +Выпуск №132
							$str .= $key . '=' . $v . (!empty($item[$k + 1]) ? '&' : '');
						}
					} else {

						$str .= $key . '=' . $item;
					}
				}

				// иначе если в переменную: $queryString пришёл не массив
			} else {

				// проверим не пришёл ли уже знак вопроса в переменную: $queryString
				if (strpos($queryString, '?') === false) {

					$str = '?' . $str;
				}

				$str .= $queryString;
			}
		}


		if (is_array($alias)) {

			$aliasStr = '';

			foreach ($alias as $key => $item) {

				// если пришёл не числовой ключ и что то пришло в переменную: $item
				if (!is_numeric($key) && $item) {

					$aliasStr .= $key . '/' . $item . '/';

					// иначе если что то пришло в переменную: $item, но ключ числовой
				} elseif ($item) {

					$aliasStr .= $item . '/';
				}
			}

			// trim() — удаление пробелов (или других символов (здесь- символ: / )) из начала и конца строки
			$alias = trim($aliasStr, '/');
		}

		if (!$alias || $alias === '/') {

			return PATH . $str;
		}

		// если в $str пришла готовая ссылка (URL c http или https), то это может быть ссылка на внешний ресурс
		if (preg_match('/^\s*https?:\/\//i', $alias)) {

			return $alias . $str;
		}

		// ищем слеш повторяющийся 2-а и более раз и меняем на единичный слеш, и выводить это будем в готовом пути
		return preg_replace('/\/{2,}/', '/', PATH . $alias . END_SLASH . $str);
	}

	/** 
	 * Метод, для автоматической подстановки слов рядом с цифрой (кол-во лет на рынке) Выпуск №124
	 */
	protected function wordsForCounter($counter, $arrElement = 'years')
	{
		$arr = [
			'years' => [
				'лет',
				'год',
				'года'
			]
		];

		if (is_array($arrElement)) {

			$arr = $arrElement;
		} else {

			// в переменную положим то что лежит в ячейке: $arr[$arrElement] (если что то в неё пришло) или возьмём 1-ый 
			// элемент массива (при этом он удаляется из массива и все ключи массива будут изменены, чтобы начать отсчет с нуля)
			$arr = $arr[$arrElement] ?? array_shift($arr);
		}

		if (!$arr)

			return null;

		// сохраним в переменную: приведённый к целому числу, обрезанный из содержимого переменной: $counter последний символ
		$char = (int)substr($counter, -1);

		// аналогично для переменной: $counter (но обрезаем с конца два символа)
		$counter = (int)substr($counter, -2);

		if (($counter >= 10 && $counter <= 20) || ($char >= 5 && $char <= 9) || !$char) {

			// вернём то что лежит в ячейке: $arr[0] (если там что то есть) или null
			return $arr[0] ?? null;
		} elseif ($char === 1) {

			return $arr[1] ?? null;
		} else {

			return $arr[2] ?? null;
		}
	}

	/** 
	 * Метод для вывода карточки товара с возможностью внесения изменений посредством изменения передаваемых параметров (Выпуск №127)
	 * На вход: 1-элемент который приходит; 2-массив параметров, согласно которым будут делаться корректировки внутри
	 *            шаблона; 3-имя шаблона (чтобы могли его переключать (по умолчанию: goodsItem))
	 */
	protected function showGoods($data, $parameters = [], $template = 'goodsItem')
	{
		if (!empty($data)) {

			echo $this->render(TEMPLATE . 'include/' . $template, compact('data', 'parameters'));
		}
	}

	/**
	 * Метод формирует ссылки пагинации при выводе карточек товаров в каталоге (Выпуск №136)	 
	 */
	protected function pagination($pages)
	{

		// найдём параметр: page в адресной строке
		$str = $_SERVER['REQUEST_URI'];

		// удалим (если есть) из адресной строки: page= и следующие за ним цифры 
		if (preg_match('/page=\d+/i', $str)) {

			$str = preg_replace('/page=\d+/i', '', $str);
		}

		// аналогично если рядом стоят: ?& или ?amp; , то заменим их на знак: ?
		if (preg_match('/(\?&)|(\?amp;)/i', $str)) {

			$str = preg_replace('/(\?&)|(\?amp;)/i', '?', $str);
		}

		$basePageStr = $str;

		if (preg_match('/\?(.)?/i', $str, $matches)) {

			if (!preg_match('/&$/', $str) && !empty($matches[1])) {

				$str .= '&';
			} else {

				$basePageStr = preg_replace('/(\?$)|(&$)/', '', $str);
			}
		} else {

			$str .= '?';
		}

		$str .= 'page=';



		$firstPageStr = !empty($pages['first']) ? ($pages['first'] === 1 ? $basePageStr : $str . $pages['first']) : '';

		$backPageStr = !empty($pages['back']) ? ($pages['back'] === 1 ? $basePageStr : $str . $pages['back']) : '';

		//$a = 1;

		if (!empty($pages['first'])) {

			echo <<<HEREDOC
			<a href="$firstPageStr" class="catalog-section-pagination__item">
									<< </a>
			HEREDOC;
		}


		if (!empty($pages['back'])) {

			echo <<<HEREDOC
			<a href="$backPageStr" class="catalog-section-pagination__item">
									< </a>
			HEREDOC;
		}


		if (!empty($pages['previous'])) {

			foreach ($pages['previous'] as $item) {

				$href = $item === 1 ? $basePageStr : $str . $item;

				echo <<<HEREDOC
				<a href="$href" class="catalog-section-pagination__item">
									$item
								</a>
				HEREDOC;
			}
		}


		if (!empty($pages['current'])) {

			echo <<<HEREDOC
			<a href="" class="catalog-section-pagination__item pagination-current">
									{$pages['current']} </a>
			HEREDOC;
		}


		if (!empty($pages['next'])) {

			foreach ($pages['next'] as $item) {

				$href = $str . $item;

				echo <<<HEREDOC
				<a href="$href" class="catalog-section-pagination__item">
									$item
								</a>
				HEREDOC;
			}
		}


		if (!empty($pages['forward'])) {

			$href = $str . $pages['forward'];

			echo <<<HEREDOC
			<a href="$href" class="catalog-section-pagination__item">
									> </a>
			HEREDOC;
		}

		if (!empty($pages['last'])) {

			$href = $str . $pages['last'];

			echo <<<HEREDOC
			<a href="$href" class="catalog-section-pagination__item">
									>> </a>
			HEREDOC;
		}
	}

	/** 
	 * Метод установки данных пользователя в форму (Выпуск №145)
	 * 
	 * на вход: 1- ключ который ищем; 2- св-во в котором ищем; 3- массив (если это не сессия)
	 */
	protected function setFormValues($key, $property = null, $arr = [])
	{
		!$arr && $arr = $_SESSION['res'] ?? [];

		return $arr[$key] ?? ($this->$property[$key] ?? '');
	}


	/** 
	 * Базовый метод добавления в корзину (Выпуск №140)
	 */
	protected function addToCart($id, $qty)
	{
		$id = $this->clearNum($id);

		$qty = $this->clearNum($qty) ?: 1;

		if (!$id) {

			return ['success' => 0, 'message' => 'Отсутствует идентификатор товара'];
		}

		// получим товар (подтверждение, что такой товар существует)
		$data = $this->model->get('goods', [
			'where' => ['id' => $id, 'visible' => 1],
			'limit' => 1
		]);

		if (!$data) {

			return ['success' => 0, 'message' => 'Отсутствует товар для добавления в корзину'];
		}

		// заберём корзину в переменную, чтобы дальше с ней работать (в одной единой переменной):
		$cart = &$this->getCart();

		//$cart['total_qty'] = 1;
		//$a = 1;

		// в корзине хранится идентификатор товара и количество
		$cart[$id] = $qty;

		// после того как добавили товар в корзину, надо проUPDATE корзину, в случае если она лежит в куках:
		$this->updateCart();

		// +Выпуск №141
		// на вход метода подаём флаг: $cartChanged = true, т.к. в корзине произошли изменения и их необходимо пересчитать
		$res = $this->getCartData(true);

		// сформируем по условию: $res['current'] т.е. текущий элемент
		if ($res && !empty($res['goods'][$id])) {

			$res['current'] = $res['goods'][$id];
		}

		return $res;
	}

	/** 
	 * Метод формирует полноценные данные о нашей корзине (Выпуск №140)
	 */
	protected function getCartData($cartChanged = false)
	{
		// если корзина получена
		if (!empty($this->cart) && !$cartChanged) {

			// вернём корзину
			return $this->cart;
		}

		// получим корзину (+Выпуск №141)
		$cart = &$this->getCart();

		// если корзина пуста:
		if (empty($cart)) {

			$this->clearCart();

			return false;
		}

		// в переменную сохраняем товары 
		// (в конце укажем диструкцию (фильтры не нужны))
		$goods = $this->model->getGoods([
			'where' => ['id' => array_keys($cart), 'visible' => 1],
			'operand' => ['IN', '=']
		], ...[false, false]);

		if (empty($goods)) {

			$this->clearCart();

			return false;
		}

		// если в корзине ($cart) есть такие идентификаторы которых нет в $goods, то какой-то товар уже отключен и надо переUPDATE корзину, иначе оставляем как есть

		$cartChanged = false;

		foreach ($cart as $id => $qty) {

			if (empty($goods[$id])) {

				unset($cart[$id]);

				$cartChanged = true;

				continue;
			}

			$this->cart['goods'][$id] = $goods[$id];

			// переложим в корзину количество:
			$this->cart['goods'][$id]['qty'] = $qty;
		}

		// если нужно UPDATE корзину (т.е. $cartChanged = true):
		if ($cartChanged) {

			$this->updateCart();
		}

		return $this->totalSum();
	}

	/** 
	 * Метод формирует общую сумму заказа (Выпуск №141)
	 */
	protected function totalSum()
	{

		if (empty($this->cart['goods'])) {

			$this->clearCart();

			return null;
		}

		// если в cart['goods'] не пусто, сформируем в корзине три ячейки дополнения к товару и установим им значение ноль:
		$this->cart['total_sum'] = $this->cart['total_old_sum'] = $this->cart['total_qty'] = 0;


		foreach ($this->cart['goods'] as $item) {

			$this->cart['total_qty'] += $item['qty'];

			$this->cart['total_sum'] += round($item['qty'] * $item['price'], 2);

			// Выпуск №143 | Пользовательская часть | Корзина товаров | ч 1
			$this->cart['total_old_sum'] += round($item['qty'] * ($item['old_price'] ?? $item['price']), 2);

			// Выпуск -№143
			/* if (!empty($item['old_price'])) {
				$this->cart['total_old_sum'] += round($item['qty'] * $item['old_price'], 2);
			} */
		}

		// Выпуск №143
		if ($this->cart['total_sum'] === $this->cart['total_old_sum']) {

			// разрегистрируем ячейку (т.е. не будем выводить перечёркнутую сумму)
			unset($this->cart['total_old_sum']);
		}

		return $this->cart;
	}

	/** 
	 * Метод обновит корзину в случае если она лежит в куках (Выпуск №140)
	 */
	protected function updateCart()
	{
		// получим корзину
		$cart = &$this->getCart();

		/* if (empty($cart)) {
			return $this->clearCart();
		} */

		if (defined('CART') && strtolower(CART) === 'cookie') {

			// поставим куку пользователю и изменим значение его корзины
			setcookie('cart', json_encode($cart), time() + 3600 * 24 * 4, PATH);
		}

		return true;
	}

	/** 
	 * Метод чистит корзину (Выпуск №141)
	 */
	public function clearCart()
	{

		unset($_COOKIE['cart'], $_SESSION['cart']);

		if (defined('CART') && strtolower(CART) === 'cookie') {

			// удалим куку (ставим время жизни куки больше чем текущая метка времени):
			setcookie('cart', '', 1, PATH);
		}

		$this->cart = [];

		return null;
	}

	/**
	 * Метод удаления данных из корзины (Выпуск №144)
	 */
	protected function deleteCartData($id)
	{
		$id = $this->clearNum($id);

		if ($id) {

			$cart = &$this->getCart();

			unset($cart[$id]);

			$this->updateCart();

			// вызываем метод с обязательным пересчётом (передаём true)
			$this->getCartData(true);
		}
	}

	// нам будет удобно работать, получив корзину единоразово (Выпуск №140)
	// (чтобы понять какой у нас массив будет, можно хранить ссылку на суперглобальные массивы, но только 
	// через передачу функции по ссылке):
	/** 
	 * Метод вернёт корзину по ссылке
	 */
	protected function &getCart()
	{
		if (!defined('CART') || strtolower(CART) !== 'cookie') {

			// то значит работаем с сессией:
			if (!isset($_SESSION['cart'])) {

				$_SESSION['cart'] = [];
			}

			return $_SESSION['cart'];
		} else {

			if (!isset($_COOKIE['cart'])) {

				$_COOKIE['cart'] = [];
			} else {

				$_COOKIE['cart'] = is_string($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : $_COOKIE['cart'];
			}

			return $_COOKIE['cart'];
		}
	}
}
