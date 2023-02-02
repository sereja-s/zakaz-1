<?php

namespace core\admin\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use libraries\FileEdit;

/** 
 * Класс отвечает за сборку шаблона админки (шапку и подвал сайта)
 * 
 * Методы: protected function sendNoCacheHeaders(); protected function execBase(): protected function createTableData();
 * 		  protected function expansion(); protected function createOutputData(); protected function createRadio();
 * 		  protected function checkPost(); protected function countChar();
 * 		  protected function emptyFields(); protected function clearPostFields(); protected function editData();
 *         protected function checkExceptFields(); protected function createFiles(); protected function sortingFiles();
 *         protected function createAlias(); protected function updateMenuPosition(); protected function checkAlias();
 *         protected function createOrderData(); protected function createManyToMany(); 
 *         protected function checkManyToMany();
 *         protected function createForeignProperty(); protected function createForeignData(); 
 *         protected function createMenuPosition(); protected function checkOldAlias(); protected function checkFiles();
 */
abstract class BaseAdmin extends BaseController
{
	// свойство через которое мы будем обращаться и вызывать методы модели
	protected $model;

	// свойство показывает какую таблицу данных подключить
	protected $table;
	protected $columns;

	// свойство для хранения основных (внешних) данных в админке
	protected $foreignData;

	protected $adminPath;

	// определим свойство в которую придёт боковое меню админки
	protected $menu;
	// свойство для хранения заголовка страницы
	protected $title;

	// свойство для хранения ссылок
	protected $alias;
	// свойство для хранения файлов
	protected $fileArray;

	// свойство для хранения нформационных сообщений
	protected $messages;
	protected $settings;

	protected $translate;
	protected $blocks = [];

	protected $templateArr;
	protected $formTemplates;
	// свойство (флаг) для запрета на удаление данных из таблиц БД
	protected $noDelete;

	protected function inputData()
	{

		// проверка версии браузера (Выпуск №91)
		if (!MS_MODE) {

			// информация о заголовках в которых хранится тип браузера передаётся в ячейке: $_SERVER['HTTP_USER_AGENT]
			// ищем в этой ячейке: буквы (msie) или слово (trident), любые символы 1-н или более раз, буквы (rv), могут 
			// быть или не быть пробелы, двоеточие
			if (preg_match('/msie|trident.+?rv\s*:/i', $_SERVER['HTTP_USER_AGENT'])) {

				exit('Вы используете устаревшую версию браузера. Пожалуйста обновитесь до актуальной версии');
			}
		}

		// +Выпуск №118
		// вызовем метод который отвечает за проверку авторизации На вход: флаг: $type устанавливаем в значение: true 
		// (Это заблокирует админку для не авторизованных пользователей) 		
		$this->checkAuth(true);

		// выполним инициализацию скриптов и стилей
		// вызовем сответствующий метод с ключём true, чтобы понять,что это административная панель
		$this->init(true);

		// укажем в переменной необходимое нам название (заголовок) страницы
		$this->title = 'VG engine';


		// Получим и созраним в переменной model объект модели, чтобы дальше могли ей пользоваться
		// сначала сделаем проверку: если никто раньше (до вызова этого метода) эту модель не установил
		if (!$this->model) {
			// обратимся к классу Model (использует шаблон Singleton) и вызовем его статический метод instance() 
			// Результат работы метода (а именно объект модели) сохраним в переменной: model
			$this->model = Model::instance();
		}

		// аналогично проверяем меню
		if (!$this->menu) {
			// то обратимся к классу Settings и вывзовем его статический метод get(), передав на вход название свойства: projectTables Результат сохраним в свойстве: menu
			$this->menu = Settings::get('projectTables');
		}

		if (!$this->adminPath) {

			$this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';
		}

		// если свойство: templateArr ещё не заполнено
		if (!$this->templateArr) {

			// получаем это свойство и сохраняем в одноимённой переменной
			$this->templateArr = Settings::get('templateArr');
		}
		// аналогично проверяем и получаем свойство: formTemplates
		if (!$this->formTemplates) {

			$this->formTemplates = Settings::get('formTemplates');
		}

		if (!$this->messages) {

			$this->messages = include $_SERVER['DOCUMENT_ROOT'] . PATH . Settings::get('messages') . 'informationMessages.php';
		}

		// вызовем метод, отправляющий браузеру заголовки файлов (здесь- админка), которые не надо кешировать 
		$this->sendNoCacheHeaders();
	}

	protected function outputData()
	{
		// Выпуск №38
		if (!$this->content) {
			// получим в переменную: $args, не указанный на входе в protected function outputData() аргумент (т.е. вернём 
			// нулевой элемент массива, который может прийти на вход protected function outputData())
			$args = func_get_arg(0);

			$vars = $args ? $args : [];

			//if(!$this->template) { $this->template = ADMIN_TEMPLATE . 'show'; }

			// обращаемся к св-ву: content и сохраняем результат работы метода: render
			// (т.е. формируем контент)
			// метод: render() по умолчанию подключит метод того класса, который его вызвал (здесь класс: ShowController, т.е. подгрузится шаблон админки: show.php)
			$this->content = $this->render($this->template, $vars);
		}

		$this->header = $this->render(ADMIN_TEMPLATE . 'include/header');
		$this->footer = $this->render(ADMIN_TEMPLATE . 'include/footer');

		return $this->render(ADMIN_TEMPLATE . 'layout/default');
	}

	/** 
	 * Метод отправит ответы (заголоки) файлов которые не надо кешировать браузеру 
	 */
	protected function sendNoCacheHeaders()
	{
		// испоьзуем ф-ию header() для отправки заголовка (поданного ей на вход) браузеру

		// Формируем заголовок ответа последней модификации контента: название- Last-Modified:, пробел, далее 
		// конкатенируем результат работы ф-ии: gmdate(), которая вернёт отформатированные по гринвичу дату и время 
		// (поданные на вход), затем указываем пробел и строку: GMT
		header("Last-Modified: " . gmdate("D, d m Y H:i:s") . " GMT");

		// формируем заголовок, который принуждает модуль браузера, отвечающий за кеш, отправлять запрос на наш сервер 
		// каждый раз для валидации тех данных, которые в этом кеше хранятся
		header("Cache-Control: no-cache, must-revalidate");

		// в заголовке укажем браузеру максимальный период свежести контента (у нас значение ноль, т.е. браузер будет 
		// понимать, что надо загрузить контент с нашего сервера, а не показать кешированный)
		header("Cache-Control: max-age=0");

		// заголовок для браузера Internet Explorer (что бы загружал контент с нашего сервера, а не показывал кешированный)
		header("Cache-Control: post-check=0, pre-check=0");
	}

	/** 
	 * Метод вызывает собственный входной метод inputData() своего класса (BaseAdmin)
	 */
	protected function execBase()
	{
		self::inputData();
	}

	/** 	 
	 * Метод заполнит свойства 
	 * (определяет из какой таблицы в БД брать данные и выбирает колонки(поля) из данной таблицы)
	 */
	protected function createTableData($settings = false)
	{
		// если свойство table нигде не было заполнено
		if (!$this->table) {

			// проверим есть ли что то в свойстве parameters
			if ($this->parameters) {

				// то в свойство table запишем массив ключей, которые вернёт ф-ия php: array_keys() из поданного ей на вход массива
				// (здесь- вернуть нужно только нулевой элемент массива ключей)
				$this->table = array_keys($this->parameters)[0];
				// иначе
			} else {

				// если не пришёл объект настроек плагина
				if (!$settings) {

					// в переменную запишем результат работы ф-ии: instance(), которая вернёт объект этого класса
					$settings = Settings::instance();
				}

				// то в св-во table получим свойство из настроек: defaultTable, что бы понимать из какой таблицы загрузятся 
				//данные по умолчанию (если в контроллер ничего не пришло)
				$this->table = $settings::get('defaultTable');
			}
		}

		// нам нужны поля из базы данных
		// в св-ве columns сохраним результат работы метода showColumns(), на вход которого подаём таблицу, поля которой нам нужны
		$this->columns = $this->model->showColumns($this->table);

		// если поля не пришли из БД
		if (!$this->columns) {
			// выбросим исключение
			new RouteException('Не найдены поля в таблице: ' . $this->table, 2);
		}
	}

	/** 
	 * Метод проектного расширения функционала фреймверка Позволяет вносить изменения в проект (из файла созданного в папке: core\admin\expansion) не меняя основной код фреймверка
	 * (На вход подаём массив аргументов (как пустой массив): $args = [] и свойство settings (возможные модификации для проекта) со значением по умолчанию: false)	
	 */
	protected function expansion($args = [], $settings = false)
	{
		// в массив: $filename сохраним результат работы ф-ии php: explode(), которая разделит строку (имя, поданной на вход 
		// таблицы: $this->table), по заданному разделителю, также поданного на вход 1-ым параметром (здесь- _ )
		$filename = explode('_', $this->table);
		// объявим переменную $className и запишем в неё пустую строку
		$className = '';

		// пробежимся по полученному массиву: $filename
		foreach ($filename as $item) {
			// и в переменную $className добавим результат работы ф-ии php: ucfirst(), которая делает первый символ, полученной 
			// на вход строки заглавным (т.е. получили имя класса)
			$className .= ucfirst($item);
		}

		// если настройки: $settings не пришли
		if (!$settings) {
			// в переменной: $path сохраним результат работы статического метода: get(), класса: Settings (т.е. получим свойство: expansion, название которого поданно на вход)
			$path = Settings::get('expansion');

			//ф-ия php: is_object() — определяет, является ли переменная(поданная на вход) объектом
		} else if (is_object($settings)) {

			$path = $settings::get('expansion'); // здесь- $settings является объектом
		} else {

			// иначе в переменную запишем строку: $settings
			$path = $settings;
		}

		// запишем в переменную: $class полное имя класса (вместе с namespace)
		$class = $path . $className . 'Expansion';

		// проверим ф-ей php: is_readable() читается ли файл, название которого подано (сформировано) в виде строки на входе 
		// (т.е. укажем полный путь) на входе
		if (is_readable($_SERVER['DOCUMENT_ROOT'] . PATH . $class . '.php')) {

			// в переменой $class сохраним результат работы ф-ии php: str_replace(), которая в переменной: $class, 
			// поданной на вход ищет символ: / и заменяет его на символом: \ (экранированным в параметре ф-ии символом: \)
			$class = str_replace('/', '\\', $class);

			// отработаем этот класс по шаблону Singleton (шаблон проектирования, гарантирующий, что в однопоточном 
			// приложении будет единственный экземпляр некоторого класса), используя метод: instance() Результат работы сохраним в переменной: $exp
			$exp = $class::instance();

			// запускаем цикл по текущему объекту: $this (будем перебирать свойства и их значения)
			foreach ($this as $name => $value) {

				// динамически создадим (или изменим) свойства у объкта класса

				// обратимся к ссылке на объект, которая хранится в переменной: $exp
				// в перемнную: $name придёт строка и св-во назавётся так же как строка, которая хранится в переменной: $name
				// (т.е. в $exp->$name мы сохранили ссылку на каждое из свойств, которые сейчас хранятся в $this (в текущем объекте))
				$exp->$name = &$this->$name;
			}

			// объект хранится в переменной: $exp, у него вызовем метод: expansion() (он описан в классе: TeachersExpansion)
			// на вход пердадим массив аргументов: $args
			// (т.е. вернём ссылку на объект класса (здесь- TeachersExpansion))
			return $exp->expansion($args);
		} else {

			// подключаем файл
			$file = $_SERVER['DOCUMENT_ROOT'] . PATH . $path . $this->table . '.php';

			// ф-ия php: extract() — импорт переменных в текущую таблицу символов из массива: $args, поданного на вход
			// (т.е. создаём переменные из массива, поданного на вход)
			extract($args);

			if (is_readable($file)) {

				return include $file;
			}
		}
		return false;
	}

	/** 
	 * Метод формирует расположение выходных данных по 3-м блокам в админке
	 */
	protected function createOutputData($settings = false)
	{
		if (!$settings) {
			$settings = Settings::instance();
		}

		// получим блоки, исходя из того, что в $settings находится некий объект настроек
		$blocks = $settings::get('blockNeedle');

		// получим из настроек свойство: translate и сохраним (этот массив с переводом) в обявленном в этом классе св-ве: translate
		$this->translate = $settings::get('translate');

		// в св-ве: $blocks хранится массив из трёх блоков и обходить его будем через несколько циков Сформируем свойства 
		// в цикле, таким образом, чтобы в его нулевой элемент свалились все поля, которые пришли из базы данных

		// если массив с блоками не пришёл или пришёл не массив
		if (!$blocks || !is_array($blocks)) {

			// пробежимся по массиву в св-ве: $this->columns, в котором сейчас находятся поля таблицы (названия полей с их 
			// характеристиками)
			foreach ($this->columns as $name => $item) {

				// если то, что пришло в переменную: name строго равно: id_row (искусственно созданное нами поле, чтобы знать где находится идентификатор в таблице в БД (не добавляется в результирующий массив))
				if ($name === 'id_row') {
					// переходим на другую итерацию цикла (ни чего нам с этим полем делать не нужно, эта строка будет нам нужна дальше в шаблонах)
					continue;
				}

				// если в массиве с переводом нет ячейки с именем, которое содержится в переменной: $name
				if (!$this->translate[$name]) {
					// добавим name в массив: translate[] (и будем выводить название поля как наазвание таблицы)
					// (обращаемся к $this->translate, его ячейке [$name], далее обращаемся к её первой(здесь- единственной) ячейке, т.е. нулевой) и записываем: $name
					$this->translate[$name][] = $name;
				}

				// Сформируем св-во, которое будет осуществлять распределение, подключение шаблонов
				// в св-ве: blocks в нулевом элементе, сформируется массив, куда последовательно попадёт, то что хранится в переменной: $name
				$this->blocks[0][] = $name;
			}

			return;
		}

		// Определим из массива в свойстве: $blocks, блок по умолчанию У нас это будет тот, который указан первым в 
		// массиве (в нулевой ячейке): т.е. ключ: vg-rows Назначим его по умолчанию, используя ф-ию php: array_keys(), 
		// которая возвращает все ключи (в виде массива) массива поданного на вход: $blocks и далее обращаемся к его нулевому элементу		
		$default = array_keys($blocks)[0];

		foreach ($this->columns as $name => $item) {

			if ($name === 'id_row') {

				continue;
			}

			// далее нужно проверить: произошла ли вставка
			// сначала объявим некий флаг в переменной: insert и установим начальное значение: false
			$insert = false;

			// пробежимся по блокам
			foreach ($blocks as $block => $value) {

				// проверим: существует ли в св-ве: $this->blocks ключ, который идёт по порядку: $block (что бы соблюдался порядок вывода)
				// если не существует
				if (!array_key_exists($block, $this->blocks)) {
					// будем создавать такой элемент массива
					// сохраним в массиве (в $this->blocks) его ячейке: $block, пустой массив
					$this->blocks[$block] = [];
				}

				// проверим существует ли $name (переменная определена) в массиве: $value
				if (in_array($name, $value)) {
					// в ячейку: $block, в массиве (в $this->blocks) последовательно добавим переменную $name
					$this->blocks[$block][] = $name;
					//т.к. вставка произошла, поставим флаг в переменной: $insert в значение: true
					$insert = true;
					// далее обрываем цикл
					break;
				}
			}

			// проверим произошла ли вставка
			// если вставка не произошла
			if (!$insert) {

				// положим поле $name в блок по умолчанию (в ячейку: $default, в массиве (в $this->blocks) последовательно добавим переменную $name)
				$this->blocks[$default][] = $name;
			}

			if (!$this->translate[$name]) {

				$this->translate[$name][] = $name;
			}
		}

		return;
	}

	/** 
	 * Метод формирует ключи и значения для input type radio (кнопок переключателей в админке (да, нет и т.д.))
	 */
	protected function createRadio($settings = false)
	{
		if (!$settings) {
			$settings = Settings::instance();
		}

		// получим св-во: radio из настроек
		$radio = $settings::get('radio');

		if ($radio) {
			foreach ($this->columns as $name => $item) {

				// проверим существует ли в св-ве: radio, которое пришло, то поле , которое нам необхдимо шаблониизировать (здесь- visible) если такое поле есть (и в него что то пришло)
				if ($radio[$name]) {

					// то сохраним его в массиве: foreignData его ячейке: name
					$this->foreignData[$name] = $radio[$name];
				}
			}
		}
	}

	/** 
	 * Метод, для определения пришло ли что-нибудь через массив: Post (если пришло, то будет вызван метод добавления данных в БД)
	 */
	protected function checkPost($settings = false)
	{
		// если что то пришло постом
		if ($this->isPost()) {

			// вызываем метод валидатор: clearPostFields()
			// (проверит заполнение полей в админке на соответствие указанным нами правилам в настройках): 
			$this->clearPostFields($settings);

			// узнаем в какую таблицу нам надо добавлять данные и сохраним её в свойство:
			$this->table = $this->clearStr($_POST['table']);
			// после того как получили таблицу (заполнили свойство: $this->table), поле $_POST['table'] нам больше не нужно
			// (разрегестрируем эту ячейку массива)
			// unset() удаляет перечисленные переменные
			unset($_POST['table']);

			// проверим заполнено ли свойство $this->table, т.е. пришло ли у нас что то с именем таблицы
			if ($this->table) {

				// вызовем метод: createTableData(), что бы заполнить свойство: $this->columns (узнаем какие поля есть в таблице)
				$this->createTableData($settings);

				// вызовем метод добавления данных в БД
				$this->editData();
			}
		}
	}



	/** 
	 * Метод валидатора (проверка на допустимое количество символов в поле шаблона админки)
	 */
	protected function countChar($str, $counter, $answer, $arr)
	{
		// если количество символов в строке, больше счётчика (поданных на вход) 
		if (mb_strlen($str) > $counter) {

			// делаем замену символа: $1, на то что хранится в переменной: $answer (т.е. на название соответствующего поля 
			// из шаблона админки), в сообщении: $this->messages['count'] (из соответствующего файла информационных 
			// сообщений, путь к которому прописан в Settings.php)
			$str_res = mb_str_replace('$1', $answer, $this->messages['count']);

			// аналогично $2 меняем на количество символов счётчика, поданного на вход в полученной выше строке 
			$str_res = mb_str_replace('$2', $counter, $str_res);

			// в ячейку суперглобального массива сохраним: div с классом: 'ошибка', внутри которого вставим сообщение об 
			// ошибке при проверке поля на допустимое количество символов из полученной строки (с уже подставленными значениями: $1 и $2)
			$_SESSION['res']['answer'] = '<div class="error">' . $str_res . '</div>';

			$this->addSessionData($arr);
		}
	}

	/** 
	 * Метод валидатора (проверка на пустоту поля в шаблоне админки)
	 */
	protected function emptyFields($str, $answer, $arr = [])
	{
		if (empty($str)) {

			// в ячейку суперглобального массива сохраним: div с классом: 'ошибка', внутри которого вставим сообщение об 
			// ошибке при проверке поля на пустоту (из соответствующего файла информационных сообщений, путь к которому прописан в Settings.php)			
			$_SESSION['res']['answer'] = '<div class="error">' . $this->messages['empty'] . ' ' . $answer . '</div>';

			// вызовем метод, который будет добавлять данные в сессионный массив			
			$this->addSessionData($arr);
		}
	}

	/** 
	 * Основной метод валидатора
	 */
	protected function clearPostFields($settings, &$arr = [])
	{
		if (!$arr) {

			$arr = &$_POST;
		}
		if (!$settings) {

			$settings = Settings::instance();
		}

		$id = $_POST[$this->columns['id_row']] ?: false;

		// получим свойство для валидации
		$validate = $settings::get('validation');

		// если свойство: $this->translate не заполнено
		if (!$this->translate) {

			// получим его (что бы потом сравнивать с анологичными полями в свойстве: $validate и при нахождении совпадений, переводить их)
			$this->translate = $settings::get('translate');
		}

		foreach ($arr as $key => $item) {

			if (is_array($item)) {

				$this->clearPostFields($settings, $item);
			} else {

				// проверим только ли из чисел состоит строка
				if (is_numeric($item)) {

					$arr[$key]  = $this->clearNum($item);
				}

				if ($validate) {
					// если в массиве: $validate (его ячейке: $key) что то есть
					if ($validate[$key]) {

						if ($this->translate[$key]) {
							// сформируем переменную: $answer (базовый ответ, который будем отдавать пользователю)
							$answer = $this->translate[$key][0];
						} else {

							$answer = $key;
						}

						// сначала проверим есть ли свойство: crypt (шифрование)
						if ($validate[$key]['crypt']) {

							if ($id) {

								// если пришло пустое поле
								if (empty($item)) {

									// разрегистрируем ячейку массива:
									unset($arr[$key]);

									continue;
								}

								// захешируем то, что лежит в $item и сохраним в ячейке массива:
								$arr[$key] = md5($item);
							}
						}

						// делаем валидацию по остальным свойствам (если они указаны в настройках)
						if ($validate[$key]['empty']) {

							$this->emptyFields($item, $answer, $arr);
						}

						if ($validate[$key]['trim']) {

							$arr[$key] = trim($item);
						}

						if ($validate[$key]['int']) {

							$arr[$key] = $this->clearNum($item);
						}

						if ($validate[$key]['count']) {

							$this->countChar($item, $validate[$key]['count'], $answer, $arr);
						}
					}
				}
			}
		}

		return true;
	}

	/** 
	 * Метод редактирования данных (добавления: add, редактирования: edit)
	 * (На вход подаётся параметр, показывающий возвращать ли id)
	 */
	protected function editData($returnId = false)
	{
		$id = false;
		$method = 'add';

		// +Выпуск №95 
		if (!empty($_POST['return_id'])) {

			$returnId = true;
		}

		if ($_POST[$this->columns['id_row']]) {

			// проверим: is_numeric() — определяет, является ли переменная числом или числовой строкой
			$id = is_numeric($_POST[$this->columns['id_row']]) ?
				$this->clearNum($_POST[$this->columns['id_row']]) :
				$this->clearStr($_POST[$this->columns['id_row']]);

			if ($id) {

				$where = [$this->columns['id_row'] => $id];

				$method = 'edit';
			}
		}

		foreach ($this->columns as $key => $item) {

			if ($key === 'id_row') continue;

			if ($item['Type'] === 'date' || $item['Type'] === 'datetime') {

				// сначала выполнится сравнение: !$_POST[$key] (если он пустой, сюда придёт не false (будет равно true)) и 
				// только тогда выполнится строка: $_POST[$key] = 'NOW()'
				// (в MySQL функция NOW() возвращает текущую дату и время)
				!$_POST[$key] && $_POST[$key] = 'NOW()';
			}
		}

		// вызовем метод создания файлов
		$this->createFiles($id);

		// вызовем метод создания ссылок (ЧПУ)
		$this->createAlias($id);

		// вызовем метод формирования позиции вывода записей из базы данных
		$this->updateMenuPosition($id);

		// в переменную сохраним резултат работы метода, исключающего поля из добавления в БД
		$except = $this->checkExceptFields();

		// в переменную: $res_id попадёт результат работы объкта модели: model, (метода, который хранится в переменной: $method (или edit или add)) В параметрах передадим: с какой таблицей мы работаем: $this->table
		$res_id = $this->model->$method($this->table, [
			// далее укажем ячейки массива и что в них будет храниться
			'files' => $this->fileArray,
			'where' => $where,
			'return_id' => true, // для edit не учитывается
			'except' => $except // поля исключений (если такие будут)
		]);

		// если в переменной: $id ничего нет и если $method === 'add' (т.е добавляли данные)
		if (!$id && $method === 'add') {

			// создадим в суперглобальном массиве ячейку: $_POST[$this->columns['id_row']] и сохраним там переменную: $res_id
			$_POST[$this->columns['id_row']] = $res_id;

			// Сформируем ответы (сообщения)

			// ответ успеха добавления:
			$answerSuccess = $this->messages['addSuccess'];

			// ответ ошибки добавления:
			$answerFail = $this->messages['addFail'];

			// иначе (при редактировании данных)
		} else {

			// ответ успеха редактирования:
			$answerSuccess = $this->messages['editSuccess'];

			// ответ ошибки редактирования:
			$answerFail = $this->messages['editFail'];
		}

		// Выпуск №86- Метод для добавления связей многие ко многим в БД
		// (метод, который будет добавлять данные в таблицу связей, для организации связей таблиц в базе данных через третью таблицу)
		$this->checkManyToMany();

		// вызовем метод для работы с расширениями (на вход передадим все объявленные переменные (используем метод php: get_defined_vars()))
		$this->expansion(get_defined_vars());

		// в переменной сохраним результат работы метода проверки ссылок
		// на вход подаём: $_POST[$this->columns['id_row']] т.к. id там будет в любом случае
		$result = $this->checkAlias($_POST[$this->columns['id_row']]);

		if ($res_id) {

			// в ячейку суперглобального массива сохраним: div с классом: 'успех', внутри которого вставим сообщение об 
			// успехе добавления или редактирования (из соответствующего файла информационных сообщений, путь к которому прописан в Settings.php)
			$_SESSION['res']['answer'] = '<div class="success">' . $answerSuccess . '</div>>';

			// если нам не нужно возвращать: Id
			if (!$returnId) {

				// сделаем возврат на ту же самую страницу и дальше код выполняться не будет
				$this->redirect();
			}

			// если нам нужно возвращать: Id (т.е. $returnId пришло),то
			return $_POST[$this->columns['id_row']];

			// иначе (если $res_id не пришёл)
		} else {

			// в ячейку суперглобального массива сохраним: div с классом: 'ошибка', внутри которого вставим сообщение об 
			// ошибке при добавления или редактирования (из соответствующего файла информационных сообщений, путь к которому прописан в Settings.php)
			$_SESSION['res']['answer'] = '<div class="error">' . $answerFail . '</div>>';

			// и аналогично при
			if (!$returnId) {

				// сделаем возврат на ту же самую страницу
				$this->redirect();
			}
		}
	}

	/** 
	 * Метод, исключающий поля из добавления в БД
	 */
	protected function checkExceptFields($arr = [])
	{
		if (!$arr) {
			$arr = $_POST;
		}

		$except = [];

		if ($arr) {

			foreach ($arr as $key => $item) {

				// если поля: columns[$key] не существует или оно пустое
				if (!$this->columns[$key]) {

					// исключим поле: [$key] из добавления в БД
					$except[] = $key;
				}
			}
		}

		return $except;
	}

	/** 
	 * Метод создания файлов
	 * (метод ничего не возвращает, а просто заполняет свойство: $fileArray, которое в методе: protected function editData() передаём в модель)
	 */
	protected function createFiles($id)
	{

		$fileEdit = new FileEdit();

		// в массив сохраним результат работы метода: addFile (объекта класса: FileEdit) Выпуск №107
		$this->fileArray = $fileEdit->addFile($this->table);

		// Выпуск №104 | создание метода сортировки изображений
		if ($id) {

			$this->checkFiles($id);
		}

		if (!empty($_POST['js-sorting']) && $this->fileArray) {

			foreach ($_POST['js-sorting'] as $key => $item) {

				if (!empty($item) && !empty($this->fileArray[$key])) {

					$fileArr = json_decode($item);

					if ($fileArr) {

						$this->fileArray[$key] = $this->sortingFiles($fileArr, $this->fileArray[$key]);
					}
				}
			}
		}
	}

	/** 
	 * Метод сортировки файлов (изображений) Выпуск №104 
	 */
	protected function sortingFiles($fileArr, $arr)
	{
		$res = [];

		foreach ($fileArr as $file) {

			if (!is_numeric($file)) {

				// обрежем название файла
				$file = substr($file, strlen(PATH . UPLOAD_DIR));
			} else {

				$file = $arr[$file];
			}

			if ($file && in_array($file, $arr)) {

				$res[] = $file;
			}
		}

		return $res;
	}

	/** 
	 * Метод создания ссылок (ЧПУ)
	 */
	protected function createAlias($id = false)
	{
		// работать метод должен только, если в данной таблице есть поле: alias
		if ($this->columns['alias']) {

			// если в посте поле: alias не приходит (т.е. пользователь админки это поле не заполнил), то мы должны его сформировать автоматически
			if (!$_POST['alias']) {

				// если ячейка: ['name'] в посте есть
				if ($_POST['name']) {

					// то сформируем переменную: alias_str
					$alias_str = $this->clearStr($_POST['name']);

					// иначе пройдёмся по всему посту и найдём что то, что связано с именем
				} else {

					foreach ($_POST as $key => $item) {

						// если в ключе: $key слово: name встречается и что то пришло в $item (не пусто)
						if (strpos($key, 'name') !== false && $item) {

							$alias_str = $this->clearStr($item);

							break;
						}
					}
				}
				// иначе (если в посте поле: alias пришло)
			} else {

				// сначала обработаем: $_POST (его ячейку: ['alias']): $this->clearStr($_POST['alias']), потом перезапишем 
				// эти данные в исходном: $_POST['alias'] и затем сохраним в переменой: $alias_str
				$alias_str = $_POST['alias'] = $this->clearStr($_POST['alias']);
			}

			// создадим объект класса: TextModify (библиотеки для работы с текстом)
			$textModify = new \libraries\TextModify();

			// в переменной: $alias (ссылка) сохраним результат работы метода: translit(), на вход которого подаём, то что хранится в переменной: $alias_str
			$alias = $textModify->translit($alias_str);

			// Проверим: не существует ли в таблице с которой мы работаем ещё такой ссылки

			// в переменную: $where (её ячейку: ['alias']) положим то, что хранится в переменной: $alias
			$where['alias'] = $alias;

			// в переменную: $operand положим знак равенства (=)
			$operand[] = '=';

			// Так как у нас один и тот же метод является обработчиком как для добавления данных, так и для редактирования,
			// то если пришёл id значит идентификатор есть и мы редактируем данные, значит нам надо сделать ещё одну проверку:
			if ($id) {

				$where[$this->columns['id_row']] = $id;

				$operand[] = '<>';
			}

			// условие в итоге будет выглядеть так: $where['alias'] = $alias и если id есть добавляем: AND WHERE (условно говоря id) не равно $id (т.е. мы ищем: $alias не в ячейке: $where[$this->columns['id_row']])

			// в переменной: $res_alias сохраним результат работы метода модели: get() Вернём нулевой элемент массива, 
			// сформированного из поданных на вход таблицы: $this->table и массива условий
			// (имеем: если в $res_alias что то пришло, значит этот alias нам не подходит, т.к. подобный уже существует)
			$res_alias = $this->model->get($this->table, [
				'fields' => ['alias'],
				'where' => $where,
				'operand' => $operand,
				'limit' => '1'
			])[0];

			if (!$res_alias) {

				// перезапишем: $_POST['alias']
				$_POST['alias'] = $alias;

				// иначе
			} else {

				// в свойство: $this->alias запишем $alias (это будет необходимо для метода редактирования дальше мы будем 
				// работать с этим свойством)
				$this->alias = $alias;

				// а $_POST['alias'] необходимо очистить, что бы туда не было попадания не нужной нам строки, не было 
				// дублирования  одного и того же alias страницы перезапишем его пустым
				$_POST['alias'] = '';
			}

			// если всё отработало хорошо и что то пришло в $_POST['alias'] и есть id (т.е. работаем в системе редактирования)
			if ($_POST['alias'] && $id) {

				// здесь- ф-ия php: method_exists() — проверяет, существует ли в нашем объекте: $this метод :
				//  checkOldAlias(), то вызовем этот метод (для хранения старых ссылок (для корректной работы с 
				// поисковыми системами если сменился alias страницы) это нужно для SEO)
				method_exists($this, 'checkOldAlias') && $this->checkOldAlias($id);
			}
		}
	}

	/** 
	 * Метод контроллера для формирования позиции вывода записей из базы данных (Выпуск №87)
	 * (На вход- необязательныый параметр: $id)
	 */
	protected function updateMenuPosition($id = false)
	{
		if (isset($_POST['menu_position'])) {

			// в переменную ставим иначально значение: false
			// (переменная будет формироваться исходя из того: придёт ли $id)
			$where = false;

			if ($id && $this->columns['id_row']) {

				// в переменную: $where сохраним массив с ячейкой: ['id_row'] со значением: $id (так сформируем инструкцию для запроса)
				$where = [$this->columns['id_row'] => $id];
			}

			//  проверим пришёл ли parent_id (есть ли родитель), относительно которого осуществляется глобальная сортировка
			if (array_key_exists('parent_id', $_POST)) {

				$this->model->updateMenuPosition($this->table, 'menu_position', $where, $_POST['menu_position'], ['where' => 'parent_id']);

				// иначе
			} else {
				$this->model->updateMenuPosition($this->table, 'menu_position', $where, $_POST['menu_position']);
			}
		}
	}

	/** 
	 * Метод проверки на отсутствие одинаковых ссылок(ЧПУ) 
	 */
	protected function checkAlias($id)
	{
		if ($id) {

			if ($this->alias) {

				$this->alias .= '-' . $id;

				// вызываем метод модели для редактирования
				$this->model->edit($this->table, [
					'fields' => ['alias' => $this->alias],
					'where' => [$this->columns['id_row'] => $id]
				]);

				return true;
			}
		}

		return false;
	}

	/** 
	 * Метод выбирает данные из админки
	 * 
	 * (Выпуск №81 автоматизация связей многие ко многим часть 1)
	 */
	protected function createOrderData($table)
	{

		$columns = $this->model->showColumns($table);

		if (!$columns) {
			throw new RouteException('Отсутствуют поля в таблице ' . $table);
		}

		$name = '';
		$order_name = '';

		// если есть поле: name
		if ($columns['name']) {

			// то сохраняем это название в переменнных
			$order_name = $name = 'name';
		} else {

			foreach ($columns as $key => $value) {

				// (в строке (в $key) ищем вхождение подстроки: name)
				if (strpos($key, 'name') !== false) {

					$order_name = $key;

					$name = $key . ' as name';
				}
			}
			if (!$name) {

				$name = $columns['id_row'] . ' as name';
			}
		}

		$parent_id = '';
		// объявим массив для сортировки
		$order = [];

		// если есть родитель (поле: parent_id), то надо делать сортировки по родителю
		if ($columns['parent_id']) {

			$order[] = $parent_id = 'parent_id';
		}

		// если есть поле: menu_position, то делаем сортировку и по нему
		if ($columns['menu_position']) {

			$order[] = 'menu_position';
		} else {

			// если указанных полей нет, сортировка будет производиться по имени
			$order[] = $order_name;
		}

		// функция: compact()- принимает имена переменных и возвращает ассоцативный массив, в котором ячейками являются 
		// имена текущих переменных
		return compact('name', 'parent_id', 'order', 'columns');
	}

	/** 
	 * Метод, создаёт связи многие ко многим
	 * 
	 * (Выпуск №81-№84 автоматизация связей многие ко многим часть 1, 2, 3, 4)
	 */
	protected function createManyToMany($settings = false)
	{
		if (!$settings) {
			// в переменную: $settings сохраним или свойство: $this->settings (если оно есть и заполнено) или результат работы метода: instance()
			$settings = $this->settings ?: Settings::instance();
		}

		// получим свойство с массивом, который содержит название таблиц связаных в БД
		$manyToMany = $settings::get('manyToMany');

		// получим блок (свойство): blockNeedle (что бы иметь возможность размещать элемент в админке )
		$blocks = $settings::get('blockNeedle');

		// если что то есть в массиве в переменной:
		if ($manyToMany) {

			// обходим его в цикле
			foreach ($manyToMany as $mTable => $tables) {

				// сохраним в переменной результаты поиска по массиву (ищем нашу таблицу среди всех таблиц массива в $tables (в значениях массива (из св-ва: private $manyToMany)))
				// переменная: $targetKey может быть 0 или 1 
				$targetKey = array_search($this->table, $tables);

				// если $targetKey есть (нашёлся), значит искомая таблица должна связываться с какой то другой
				if ($targetKey !== false) {

					// Определим ключ другой таблицы с которой (с которой должна связаться найденная таблица)

					// сохраним в переменной: $otherKey ноль, если переменная: $targetKey равна единице (т.е. нашлась)
					// иначе (если в $targetKeyлежит 0) в $otherKey положим 1-цу
					$otherKey = $targetKey ? 0 : 1;

					// Определим нужно ли нам делать вывод данных связанной таблицы (шаблона: checkboxlist в админке)

					// сохраним в переменной содержимое ячейки: checkboxlist массива шаблонов: templateArr (из Settings.php)
					$checkBoxList = $settings::get('templateArr')['checkboxlist'];

					if (!$checkBoxList || !in_array($tables[$otherKey], $checkBoxList)) {

						continue;
					}

					// если ячейки: $tables[$otherKey] в массиве (в св-ве: $translate) нет (т.е. название таблицы не имеет перевода)
					if (!$this->translate[$tables[$otherKey]]) {

						// если в свойстве: private $projectTables (в полученных проектных таблицах) есть ячейка: $tables[$otherKey]
						if ($settings::get('projectTables')[$tables[$otherKey]]) {

							// то заполним св-во: $translate (его ячейку: $tables[$otherKey])
							// сохраним в нём массив из полученного св-ва: projectTables его ячейки: $tables[$otherKey]['name']
							$this->translate[$tables[$otherKey]] = [$settings::get('projectTables')[$tables[$otherKey]]['name']];
						}
					}

					// в переменной: $orderData сохраним результат работы метода: createOrderData() (на вход ему 
					// передаём: другую таблицу с которой делаем связь и данные которой нам надо получить ( из ячейки: $tables[$otherKey]))
					$orderData = $this->createOrderData($tables[$otherKey]);

					$insert = false;

					// Опишем код, который будет размещать в нужном нам блоке админки, поле шаблона: checkboxlist (здесь- 
					// filters), для связанных с ним таблиц (здесь- goods)) 

					// если что то есть в переменной: $blocks
					if ($blocks) {

						foreach ($blocks as $key => $item) {

							// если в массиве из $item есть $tables[$otherKey]
							// (т.е если мы объявили $tables[$otherKey] для шаблонизации)
							if (in_array($tables[$otherKey], $item)) {

								// исходя из св-ва: $this->blocks (его ячейки: [$key] в его элементе) сформируется наш шаблон
								// (т.е. узнаем в каком блоке разместить)
								$this->blocks[$key][] = $tables[$otherKey];

								$insert = true;

								// завершим работу цикла
								break;
							}
						}
					}

					if (!$insert) {

						// получим первую ячейку св-ва: $this->blocks и к нему  мы добавим элемент: $tables[$otherKey]
						$this->blocks[array_keys($this->blocks)[0]][] = $tables[$otherKey];
					}


					$foreign = [];

					// Если заполнено св-во: $data (т.е. мы работаем в режиме редактирования) В него попадают все данные той или иной записи БД То нам необходимо получить данные, которые хранятся в полях связанныъ таблиц
					if ($this->data) {

						// в переменную: $res сохраним результат работы метода: get() нашей модели На вход ему 
						// передаём:1- откуда данные должны получить (наша таблица связи: $mTable, 2- какие данные нужно 
						// получить (только данные связанные с другой таблицей) далее передаём поля и условия(инструкции))
						$res = $this->model->get($mTable, [
							// Соглашение об именовании: поля в БД 3-й (связующей) таблицы необходимо называть в виде: 
							// название таблицы с которой поле связано_имя поля с первичным ключём таблицы с которой поле 
							// связано (здесь- goods_id и filters_id)

							// Формируем назване полей в запросе к 3-й (связующей) таблице:
							// имеем: в $tables[$otherKey]- название таблицы, далее нижнее подчёркивание и затем в $orderData
							// ['columns']['id_row']]- то что является полем id 
							'fields' => [$tables[$otherKey] . '_' . $orderData['columns']['id_row']],
							// Формируем инструкцию:
							// в $this->table- название таблицы, в $this->columns['id_row']- первичный ключ текущей 
							// таблицы с которой работаем 
							'where' => [$this->table . '_' . $this->columns['id_row'] => $this->data[$this->columns['id_row']]]
						]);

						if ($res) {

							foreach ($res as $item) {

								// сформируем массив связей, которые есть с текущей таблицей
								// в каждом $item лежит название, которое пришло в 'fields' ранее
								// (т.е. в $foreign[] будут идентификаторы всех полей, которые есть не в текущей таблице, а в таблице связанной)
								$foreign[] = $item[$tables[$otherKey] . '_' . $orderData['columns']['id_row']];
							}
						}
					}


					// если в массиве (в св-ве: private $manyToMany) существует ячейка: $tables['type'] и в ней что то лежит отличное от null
					if (isset($tables['type'])) {

						// в переменную мы должны получить данные из другой (внешней) таблицы
						$data = $this->model->get($tables[$otherKey], [
							'fields' => [
								$orderData['columns']['id_row'] . ' as id', // поле получаем как id
								$orderData['name'],
								$orderData['parent_id'] // получим родительский идентификатор
							],
							// отсортируем всё по тому, что лежит в ячейке
							'order' => $orderData['order']
						]);


						if ($data) {

							$this->foreignData[$tables[$otherKey]][$tables[$otherKey]]['name'] = 'Выбрать';

							foreach ($data as $item) {
								// Проверим что мы хотим получать дочерние элементы(child) или родительские категории(root)

								// если содержимое в ячейке: $tables['type'] строго равено строке: root и что то пришло в   
								// ячейку: $orderData['parent_id']
								if ($tables['type'] === 'root' && $orderData['parent_id']) {

									// если выполнится условие, то этот элемент является названием группы (категории), т.е. корневой раздел
									if ($item[$orderData['parent_id']] === null) {

										// то в свойство, которое уже есть ($foreignData), в его ячейку: $tables[$otherKey],
										// далее для удобства вывода шаблона продублируем эту ячейку ещё раз
										// затем в ней создадим ячейку: ['sub'] и в её элемент (последовательно) положим: 
										// $item (то что там лежит), т.е. все родители будут помещены в массив в ячейке: ['sub']
										$this->foreignData[$tables[$otherKey]][$tables[$otherKey]]['sub'][] = $item;
									}

									// если содержимое в ячейке: $tables['type'] строго равено строке: child и существует 
									// ячейка: $orderData['parent_id']
								} elseif ($tables['type'] === 'child' && $orderData['parent_id']) {

									// если выполнится условие, то этот элемент на кого то ссылается								
									if ($item[$orderData['parent_id']] !== null) {

										// и мы хотим видеть только детей (дочерние элементы)
										$this->foreignData[$tables[$otherKey]][$tables[$otherKey]]['sub'][] = $item;
									}

									// иначе (если в поле type занесено, что то произвольное), все 
									// элементы (родительские и дочерние) будут сохранены в одном месте
								} else {

									$this->foreignData[$tables[$otherKey]][$tables[$otherKey]]['sub'][] = $item;
								}

								// далее занесём элементы (если у нас есть $data, а значит все нужные идентификаторы уже 
								// лежат в массиве: $foreign)
								// найдём $item['id'] в массиве (они там есть т.к. мы их ранее выбирали as id)
								if (in_array($item['id'], $foreign)) {

									$this->data[$tables[$otherKey]][$tables[$otherKey]][] = $item['id'];
								}
							}
						}

						//exit;

						// если ячейки: $tables['type'] нет, а ячейка: $orderData['parent_id'] есть
					} elseif ($orderData['parent_id']) {

						$parent = $tables[$otherKey];

						// Далее мы должны получить (так же как, когда мы получали внешние данные) данные из служебной базы mysql: information_shema (поля: COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME)

						// (если $keys вернёт false, значит $parent ссылается на самого себя и мы внешний ключ ссылки на 
						// самого себя не делаем)
						$keys = $this->model->showForeignKeys($tables[$otherKey]);

						//exit;

						if ($keys) {

							foreach ($keys as $item) {

								if ($item['COLUMN_NAME'] === 'parent_id') {

									// то в родительскую категорию сохраним имя таблицы с которой произведена связь
									$parent = $item['REFERENCED_TABLE_NAME'];

									break;
								}
							}
						}

						// если parent_id находится в этой же таблице (ссылается на поле в своей таблице)
						if ($parent === $tables[$otherKey]) {

							// получим данные из таблицы
							$data = $this->model->get($tables[$otherKey], [
								'fields' => [
									$orderData['columns']['id_row'] . ' as id',
									$orderData['name'],
									$orderData['parent_id']
								],
								'order' => $orderData['order']
							]);

							if ($data) {
								// т.к. система вложенностей родительских категорий может быть различной (многоуровневой)
								// запустим цикл с проверкой:
								// в переменную: $key будет попадать то что возвращает ф-ия: key(), исходя из переменной: $data
								// ф-ия: key() вернёт ключ массива, поданного на вход (при этом указатель данного массива не сдвигает)
								// (цикл отрабатывает пока в переменную: $key не придёт null)
								while (($key = key($data)) !== null) {

									// если нет ячейки: $data[$key]['parent_id'], то это корневая (родительская) категория
									if (!$data[$key]['parent_id']) {

										// то мы положим её в массив: $foreignData, в качестве имени в соответствующую ячейку,
										// которое потом будем показывать (здесь id нам не нужен, т.к. нам нужно значение, а не название)
										$this->foreignData[$tables[$otherKey]][$data[$key]['id']]['name'] = $data[$key]['name'];

										// разрегистрируем переменную (больше не нужна)
										unset($data[$key]);

										// сбросим указатель массива обратно на начало (чтобы каждый раз обходили его с нуля)
										reset($data);

										// переходим на следующую итерацию цикла
										continue;

										// иначе
									} else {

										// если родительская категория уже в массив попала (ячейка: parent_id уже есть т.е. 
										// родительский раздел уже создан)
										if ($this->foreignData[$tables[$otherKey]][$data[$key][$orderData['parent_id']]]) {

											// обратимся к ячейке: ['sub'] того массива (его ячейки), указанного в условии
											// и в неё (в ячейку с ключём, в её ячейку: ['id']) положим: ячейку: $data[$key]
											$this->foreignData[$tables[$otherKey]][$data[$key][$orderData['parent_id']]]['sub'][$data[$key]['id']] = $data[$key];

											// проверим есть ли ячейка: $data[$key]['id'] во внешних ключах (в переменной $foreign)
											if (in_array($data[$key]['id'], $foreign)) {

												// получим ячейку: parent_id и в неё добавим содержимое ячейки: $data[$key]['id']
												$this->data[$tables[$otherKey]][$data[$key][$orderData['parent_id']]][] = $data[$key]['id'];
											}

											// разрегистрируем переменную (больше не нужна)
											unset($data[$key]);

											// сбросим указатель массива обратно на начало (чтобы каждый раз обходили его с нуля)
											reset($data);

											// переходим на следующую итерацию цикла
											continue;

											// иначе мы должны сгенерировать такую ячейку
										} else {

											// пройдёмся в цикле по массиву в ячейке: foreignData[$tables[$otherKey]], чтобы получить идентификаторы
											foreach ($this->foreignData[$tables[$otherKey]] as $id => $item) {

												// формируем переменную: $parent_id сохраняя в неё, то что есть 
												// в ячейке: $data[$key][$orderData['parent_id']]
												// (т.е. получим родителя)
												$parent_id = $data[$key][$orderData['parent_id']];

												// если у нас ячейка: $item['sub'] заполнена и не null, также 
												// ячейка: $item['sub'][$parent_id] тоже не null
												if (isset($item['sub']) && $item['sub'] && isset($item['sub'][$parent_id])) {

													$this->foreignData[$tables[$otherKey]][$id]['sub'][$data[$key]['id']] = $data[$key];

													if (in_array($data[$key]['id'], $foreign)) {

														$this->data[$tables[$otherKey]][$id][] = $data[$key]['id'];
													}

													unset($data[$key]);

													reset($data);

													// выйдем их цикла: foreach и перейдём на следующую итерацию цикла: while
													continue 2;
												}
											}
										}

										// переместим указатель на каждой итерации цикла: while (если условия цикла: while не 
										// выполнились, будем смотреть дальше)
										next($data);
									}
								}
							}

							//exit;

							// иначе: если parent_id (родители) находится в другой таблице (т.е. в $parent)
						} else {

							// получим данные из Бд для родительской таблицы
							$parentOrderData = $this->createOrderData($parent);

							$data = $this->model->get($parent, [
								// получим имена (названия родительских категорий которые есть)
								'fields' => [$parentOrderData['name']],
								// для формирования структурированного массива, в ячейку массива: 'join' кладём
								'join' => [
									// в ячейку (названую также как таблица с которой связываемся), массив 
									$tables[$otherKey] => [
										// укажем поля которые необходимо получить
										'fields' => [$orderData['columns']['id_row'] . ' as id', $orderData['name']],
										// укажем по какому признаку мы стыкуем эти таблицы
										'on' => [$parentOrderData['columns']['id_row'], $orderData['parent_id']]
									]
								],
								// флаг структуризации (нам нужны подготовленные(структурированные) данные)
								'join_structure' => true
							]);

							//exit;

							// Имея в запросе join_structure, корректно заполним св-во: $this->foreignData

							foreach ($data as $key => $item) {

								if (isset($item['join'][$tables[$otherKey]]) && $item['join'][$tables[$otherKey]]) {

									$this->foreignData[$tables[$otherKey]][$key]['name'] = $item['name'];

									$this->foreignData[$tables[$otherKey]][$key]['sub'] = $item['join'][$tables[$otherKey]];



									foreach ($item['join'][$tables[$otherKey]] as $value) {

										if (in_array($value['id'], $foreign)) {

											$this->data[$tables[$otherKey]][$key][] = $value['id'];
										}
									}
								}
							}
						}

						// иначе если ячейки: $tables['type'] нет (не прописали в св-ве: private $manyToMany (в базовых 
						// настройках)) и $orderData['parent_id'] тоже нет (отсутствует в соответствующей таблице БД такое поле)
					} else {

						// получим все данные из того, что есть (придут все подряд в одном массиве)
						$data = $this->model->get($tables[$otherKey], [
							'fields' => [$orderData['columns']['id_row'] . ' as id', $orderData['name'], $orderData['parent_id']],
							'order' => $orderData['order']
						]);

						// если данные пришли
						if ($data) {

							// заполним ячейку: ['name'] массива в св-ве: $foreignData 
							$this->foreignData[$tables[$otherKey]][$tables[$otherKey]]['name'] = 'Выбрать';

							foreach ($data as $item) {

								$this->foreignData[$tables[$otherKey]][$tables[$otherKey]]['sub'][] = $item;

								if (in_array($item['id'], $foreign)) {

									$this->data[$tables[$otherKey]][$tables[$otherKey]][] = $item['id'];
								}
							}
						}
					}
				}
			}
		}

		//exit;
	}

	/** 
	 * Метод для добавления связей многие ко многим в БД (Выпуск №86)
	 * (метод, который будет добавлять данные в таблицу связей, для организации связей таблиц в базе данных через третью таблицу)
	 */
	protected function checkManyToMany($settings = false)
	{
		if (!$settings) {

			$settings = $this->settings ?: Settings::instance();
		}

		$manyToMany = $settings::get('manyToMany');

		if ($manyToMany) {

			foreach ($manyToMany as $mTable => $tables) {

				$targetKey = array_search($this->table, $tables);

				if ($targetKey !== false) {

					$otherKey = $targetKey ? 0 : 1;

					$checkboxlist = $settings::get('templateArr')['checkboxlist'];

					if (!$checkboxlist || !in_array($tables[$otherKey], $checkboxlist)) {

						continue;
					}

					// обращаемся к объекту модели, к её методу, чтобы получить колонки таблицы, переданной на вход
					// результат сохраняем в переменной: $columns
					$columns = $this->model->showColumns($tables[$otherKey]);

					// Формируем названия ячеек в таблице связей(3-я таблица) соласно договорённости об их именовании
					$targetRow = $this->table . '_' . $this->columns['id_row'];

					$otherRow = $tables[$otherKey] . '_' . $columns['id_row'];

					// В начале, все данные из таблицы, которые связаны с этой выборкой (всё что связано с указанным id 
					// (т.е. товар и привязанные к нему фильтры)), одним запросом будем удалять
					// а потом будем добавлять те чекбоксы, которые пришли из админпанели
					// (т.к. если галочка снята, то этот чекбокс не приходит (его нет в посте), если галочка стоит, то мы 
					// должны занести это в таблицу связей)
					$this->model->delete($mTable, [
						'where' => [$targetRow => $_POST[$this->columns['id_row']]]
					]);

					// если с поста что то пришло
					if ($_POST[$tables[$otherKey]]) {

						// начинаем формировать массив для множественной вставки
						$insertArr = [];

						// подключим счётчик
						$i = 0;

						// в цикле будем формировать массив для множественной вставки
						foreach ($_POST[$tables[$otherKey]] as $value) {

							foreach ($value as $item) {

								// если в переменную что то пришло
								if ($item) {

									// в ячейку массива сохраним идентификатор из текущей таблицы
									$insertArr[$i][$targetRow] = $_POST[$this->columns['id_row']];

									// в ячейку массива сохраним идентификатор из таблицы связи
									$insertArr[$i][$otherRow] = $item;

									$i++;
								}
							}
						}

						// если в переменную что то пришло
						if ($insertArr) {

							// сделаем множественную вставку в таблицу (в $mTable) поля из массива (в $insertArr)
							$this->model->add($mTable, [
								'fields' => $insertArr
							]);
						}
					}
				}
			}
		}
	}

	/** 
	 * Метод для получения свойств внешних данных (из связанных таблиц)
	 * (На вход: 1- массив, 2- св-во, в котором будет храниться информация о корневых таблицах, полученная из файла настроек)
	 */
	protected function createForeignProperty($arr, $rootItems)
	{

		// проверим существует ли значение св-ва: table в массиве: rootItems (в ячейке: tables)
		if (in_array($this->table, $rootItems['tables'])) {
			// в ячейке: COLUMN_NAME массива: $arr, хранится имя колонки, в кот. хранится имя текущей таблицы, которая ссылается на родительскую
			$this->foreignData[$arr['COLUMN_NAME']][0]['id'] = 'NULL';
			$this->foreignData[$arr['COLUMN_NAME']][0]['name'] = $rootItems['name'];
		}

		// (Выпуск №81)
		// в ячейке: REFERENCED_TABLE_NAME массива: $arr, хранится имя таблицы на которую ссылаемся
		$orderData = $this->createOrderData($arr['REFERENCED_TABLE_NAME']);

		if ($this->data) {

			// если ссылка идёт на самих себя
			if ($arr['REFERENCED_TABLE_NAME'] === $this->table) {

				// то сформируем условия: $where и $operand[]
				$where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
				$operand[] = '<>';
			}
		}

		// сохраним выборку из БД в переменной
		$foreign = $this->model->get($arr['REFERENCED_TABLE_NAME'], [

			// сформируем массив полей (нам нужно то поле , на которое мы ссылаемся)
			'fields' => [
				// Выбираем поля, которые нам нужны
				// в ячейке: REFERENCED_COLUMN_NAME массива: $arr, хранится имя колонки (поле) на которое ссылаемся			
				$arr['REFERENCED_COLUMN_NAME'] . ' as id',
				// (+Выпуск №81) в ячейке: name будет лежать name или то что придёт с псевдонимом: name
				$orderData['name'],
				$orderData['parent_id']
			],
			'where' => $where,
			'operand' => $operand,
			'order' => $orderData['order']
		]);

		if ($foreign) {

			if ($this->foreignData[$arr['COLUMN_NAME']]) {

				foreach ($foreign as $value) {

					$this->foreignData[$arr['COLUMN_NAME']][] = $value;
				}
			} else {

				$this->foreignData[$arr['COLUMN_NAME']] = $foreign;
			}
		}
	}

	/** 
	 * Метод получает внешние данные (из связанных таблиц)
	 */
	protected function createForeignData($settings = false)
	{
		if (!$settings) {
			$settings = Settings::instance();
		}

		// св-во, в котором будет храниться информация о корневых таблицах, полученная из файла настроек
		$rootItems = $settings::get('rootItems');

		$keys = $this->model->showForeignKeys($this->table);

		// если ключи пришли
		if ($keys) {
			// запскаем по ним цикл
			foreach ($keys as $item) {
				$this->createForeignProperty($item, $rootItems);
			}

			// если ключи не пришли, но есть поле: parent_id, то
		} elseif ($this->columns['parent_id']) {

			// Формируем элементы массива:

			// имя колонки (поле), которая ссылается на внешнюю таблицу
			$arr['COLUMN_NAME'] = 'parent_id';
			// колонка,на которую ссылаемся (колонка с первичным ключём)
			$arr['REFERENCED_COLUMN_NAME'] = $this->columns['id_row'];
			// имя таблицы на которую ссылаемся
			$arr['REFERENCED_TABLE_NAME'] = $this->table;

			$this->createForeignProperty($arr, $rootItems);
		}

		return;
	}

	/** 
	 * Метод для формирования первичных данных для сортировки информации в таблицах БД (Выпуск №42)
	 */
	protected function createMenuPosition($settings = false)
	{

		// если ячейка: menu_position (в массиве: columns) существует (и в неё что то пришло)
		if ($this->columns['menu_position']) {

			// если настройки ещё не получены
			if (!$settings) {

				// то получим настройки
				$settings = Settings::instance();
			}

			// получим из файла настроек св-во: rootItems
			$rootItems = $settings::get('rootItems');

			if ($this->columns['parent_id']) {

				// если в массиве: rootItems (его ячейке: tables есть то,что хранится в свойстве: table (название таблицы))
				if (in_array($this->table, $rootItems['tables'])) {

					// в переменную положим строку
					$where = 'parent_id IS NULL OR parent_id = 0';

					// иначе
				} else {

					// запросим внешние ключи
					// (в параметры ф-ии передаём: название таюлицы и ключ (в виде строки)), в конце указываем, что в $parent нам
					// вся выборка не нужна (нужно вернуть нулевой элемент (здесь он или будет или не будет))
					$parent = $this->model->showForeignKeys($this->table, 'parent_id')[0];

					// если родитель пришёл
					if ($parent) {

						// если таблица указана в ключе (ссылается сама на себя)
						if ($this->table === $parent['REFERENCED_TABLE_NAME']) {

							$where = 'parent_id IS NULL OR parent_id = 0';
						} else {

							$columns = $this->model->showColumns($parent['REFERENCED_TABLE_NAME']);

							if ($columns['parent_id']) {

								// в элемент массива: order сохраним строку: parent_id
								$order[] = 'parent_id';
							} else {

								// сортировать нужно по тем полям, на которые идёт ссылка
								// в элемент массива: order получим то поле, которое является идентификатором
								$order[] = $parent['REFERENCED_COLUMN_NAME'];
							}

							$id = $this->model->get($parent['REFERENCED_TABLE_NAME'], [
								// укажем какие поля из поданного на вход функции: get() массива: parent (его ячейки: 
								// REFERENCED_TABLE_NAME) должны получить
								'fields' => [$parent['REFERENCED_COLUMN_NAME']],
								'order' => $order,
								'limit' => '1'
							])[0][$parent['REFERENCED_COLUMN_NAME']]; // укажем, что вернуть надо нулевой элемент той выборки, 
							// которая пришла (и то поле, которое мы запрашиваем)

							if ($id) {
								$where = ['parent_id' => $id];
							}
						}
					} else {

						$where = 'parent_id IS NULL OR parent_id = 0';
					}
				}
			}

			$menu_pos = $this->model->get($this->table, [
				// укажем какие поля из поданной на вход функции: get() таблицы (в св-ве: table) должны получить
				// (здесь в значении поля: fields указываем СУБД: посчитай всё и предоставь эту выборку с псевдонимом: count)
				'fields' => ['COUNT(*) as count'],
				'where' => $where,
				'no_concat' => true // т.е. не пристыковывать имя таблицы
			])[0]['count'] + (int)!$this->data; // укажем, что вернуть надо нулевой элемент той выборки, которая 
			// пришла (в его ячейку: count) + увеличиваем $menu_pos на 1 (т.е. означает: добавили данные (add)) 
			// (+Выпуск №88)
			// Для редактирования (edit), $menu_pos не увеличиваем
			// имеем: если $this->data пришла, то !$this->data даёт false, а значит (int)!$this->data = 0 (для edit),
			// а если $this->data не пришла, то !$this->data даёт true, а значит (int)!$this->data = 1 (для add)

			for ($i = 1; $i <= $menu_pos; $i++) {

				$this->foreignData['menu_position'][$i - 1]['id'] = $i;

				$this->foreignData['menu_position'][$i - 1]['name'] = $i;
			}
		}

		return;
	}

	/** 
	 * Метод для хранения старых ссылок (+Выпуск №95 )
	 * Необходимо для поисковых систем что бы избежать повторной индексации (например если название страницы изменилось, а контент остался тем же)
	 */
	protected function checkOldAlias($id)
	{
		$tables = $this->model->showTables();

		// проверим есть ли в массиве: $tables таблица: old_alias
		if (in_array('old_alias', $tables)) {

			// Сохраним текущую ссылку в таблице для хранения старых ссылок
			// в переменную: $old_alias получаем данные, с помощью метода модели: get() из текущей таблицы: table (подаётся на вход 1-ым параметром), какие данные (поля) необходимо получить и условие указываем 2-ым параметром
			// (вернуть нужно только нулевой элемент массива (его ячейку: ['alias']))
			$old_alias = $this->model->get($this->table, [
				'fields' => ['alias'],
				'where' => [$this->columns['id_row'] => $id]
			])[0]['alias'];

			if ($old_alias && $old_alias !== $_POST['alias']) {

				// сделаем запрос к БД на удаление: old_alias и укажем условия для удаления и к какой таблице применить
				// удаление делается на случай если такой $old_alias уже был в соответствующей таблице
				$this->model->delete('old_alias', [
					'where' => ['alias' => $old_alias, 'table_name' => $this->table]
				]);

				// такой же запрос на удаление делаем для того, что хранится в массиве: $_POST (его ячейке: ['alias'])
				$this->model->delete('old_alias', [
					'where' => ['alias' => $_POST['alias'], 'table_name' => $this->table]
				]);

				// добавим то что хранится в переменной: $old_alias в текущую таблицу: $this->table с указанием 
				// идентификатора: id (может использоваться для уникальности идентификатора ссылки)
				$this->model->add('old_alias', [
					'fields' => ['alias' => $old_alias, 'table_name' => $this->table, 'table_id' => $id]
				]);
			}
		}
	}

	/** 
	 * Метод для проверки файлов (+Выпуск №90, 95, 104)
	 */
	protected function checkFiles($id)
	{
		if ($id) {

			$arrKeys = [];

			if (!empty($this->fileArray)) {

				$arrKeys = array_keys($this->fileArray);
			}

			if (!empty($_POST['js-sorting'])) {

				// array_merge()- объединяет элементы одного или нескольких массивов таким образом, чтобы значения одного 
				// из них добавлялись в конец предыдущего. Он возвращает результирующий массив
				$arrKeys = array_merge($arrKeys, array_keys($_POST['js-sorting']));
			}

			if ($arrKeys) {

				// отфильтруем массив по уникальным значениям
				$arrKeys = array_unique($arrKeys);

				$data = $this->model->get($this->table, [
					'fields' => $arrKeys,
					'where' => [$this->columns['id_row'] => $id]
				]);

				if ($data) {

					$data = $data[0];

					foreach ($data as $key => $item) {

						if ((!empty($this->fileArray[$key]) && is_array($this->fileArray[$key])) || !empty($_POST['js-sorting'][$key])) {

							$fileArr = json_decode($item);

							if ($fileArr) {

								foreach ($fileArr as $file) {

									// добавляем файл в массив
									$this->fileArray[$key][] = $file;
								}
							}
						} elseif (!empty($this->fileArray[$key])) {

							@unlink($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $item);
						}
					}
				}
			}
		}
	}
}
