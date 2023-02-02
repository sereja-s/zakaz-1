<?php

namespace core\base\model;

use core\base\exceptions\DbException;
use core\base\model\BaseModelMethods;

/** 
 * Класс базовой модели
 * (Здесь: $crud = r - SELECT / c - INSERT / u - UPDATE / d - DELETE)
 * 
 *  Методы: protected function connect(); final public function query(); final public function get(); 
 *          protected function createPagination();
 * 			final public function add(); final public function edit(); public function delete(); 
 *          public function buildUnion(); public function getUnion();
 *          final public function showColumns(); final public function showTables()
 */
abstract class BaseModel extends BaseModelMethods
{
	protected $db;

	/** 
	 * Метод в котором будем осуществлять подключения к БД
	 */
	protected function connect()
	{
		//	ИНИЦИАЛИЗАЦИЯ ПОДКЛЮЧЕНИЯ ПРИ ПОМОЩИ ПОДКЛЮЧЕНИЯ БИБЛИОТЕКИ mysqli
		// обращаемся к свойству db этого класса и в него сохраняем объект подключения библиотеки mysqli (установим заглушку текущих ошибок- @)
		// класс mysqli (находится в глобальном пространстве имён) на вход принимает: которые хранятся в константах: 
		// HOST (имя хоста), USER (имя пользователя), PASS (пароль), DB_NAME (имя базы данных)
		$this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);

		// проверка:если у объекта класса mysqli ($this->db) в его свойстве connect_error (хранит в себе текст ошибки подключения) что то есть 
		if ($this->db->connect_error) {
			// то выбросим (сгенерируем исключение) текст, указание кода ошибки (при подключении) в свойстве connect_errno и сообщение об ошибке (при подключении) в свойстве connect_error
			throw new DbException('Ошибка подключения к базе данных: '
				. $this->db->connect_errno . ' ' . $this->db->connect_error);
		}

		// если соединение с БД удалось, установим кодировку соединения (испольуем метод библиотеки mysqli: query()), 
		// которому на вход подаётся запрос на устаноку соединения(SET NAMES UTF8)
		$this->db->query("SET NAMES UTF8");
	}

	/**
	 * @param $query
	 * @param string $crud = r - SELECT / c - INSERT / u - UPDATE / d - DELETE
	 * @param false $return_id
	 * @return array|bool|mixed
	 * @throws DbException
	 */

	// Создадим метод query() с одноимённым названием (как и метод в классе (библиотеке) mysqli, использованный ранее)
	// сделаем его финальным (не дадим возможности переопределять его в дочерних классах)	 

	/** 
	 *  Метод делает запрос к БД и возвращает его результат ($crud = r - SELECT / c - INSERT )
	 * (на вход передадим: переменную с запросом ($query), метод которым будем этот запрос осуществлять (переменная $crud со значением по умолчанию: r (read- чтение)), идентификатор вставки $return_id со значением false)	  
	 */
	final public function query($query, $crud = 'r', $return_id = false)
	{
		//Сделаем запрос к базе данных:
		// в переменную $result сохраним результат работы метода query() класса mysqli, обратившись к нему через объект этого 
		// класса, который хранится в свойстве $this->db (на вход подаётся переменная $query)
		//Т.е. в переменную $result приходит объект, содержащий в себе выборку из базы данных
		$result = $this->db->query($query);

		// Обработаем запрос
		// проерка: если у объекта класса mysqli, который хранится в свойстве $this->db, в свойстве affected_rows 
		// (эффективные ряды затронутые выборкой) хранится значение: -1
		if ($this->db->affected_rows === -1) {
			// то БД вернёт нам ошибку (выбросит исключение): текст, сам запрос, код ошибки (в запросе) в св-ве  errno 
			// объекта: $this->db, сообщение об ошибке (в запросе) в св-ве error объекта: $this->db
			throw new DbException('Ошибка в SQL запросе: '
				. $query . ' - ' . $this->db->errno . ' ' . $this->db->error);
		}

		// При помощи оператора множественного выбора switch проверим что находится в переменной $crud (проверяется 
		// равенство указанного на входе значения (здесь- $crud), значениям в каждом кейсе) 
		// каждый следующий кейс проверяется, если не выполнен(не было равенства) предыдущий
		switch ($crud) {

				// проверим кейс: r (чтение)
			case 'r':
				// если в св-во num_rows нашего объекта в $result (содержащего в себе выборку из базы данных) что то пришло из БД
				if ($result->num_rows) {

					// то в переменной $res объявляем массив
					$res = [];
					// проходимся в цикле for по данному массиву
					for ($i = 0; $i < $result->num_rows; $i++) {

						// массив $res[] заполняем тем, что вытащит метод fetch_assoc() объекта $result (т.е. в понимаемом 
						// виде вернёт массив каждого ряда выборки, который хранился в объекте $result)
						$res[] = $result->fetch_assoc();
					}
					return $res;
				}
				return false;
				break;

				// проверим кейс: с (создание)
			case 'c':

				// если переменная $return_id = true
				if ($return_id) {

					return $this->db->insert_id;
				}
				return true;
				break;

				// во всех остальных случаях 
			default:

				// выполнится код по умолчанию:
				return true;
				break;
		}
	}

	/**
	 * @param $table // пременная (таблица)
	 * @param array $set // переменная (массив данных (настроек)) Далее пример массива $set:
	 *           'fields' => ['id', 'name'],
	 * 			 'no_concat' => false/true (Если true не присоединяем имя таблицы к полям и where)- Выпуск №42
	 *           'where' =>  ['fio'=>'Smirnova', 'name'=>'Masha', 'surname'=>'Sergeevna'],
	 *           'operand' =>['=', '<>'],
	 *           'condition'=>['ADN'],
	 *           'order'=>['fio','name','surname'],
	 *           'order_direction'=>['ASC','DESC'],
	 *           'limit'=>'1'
	 *           	'join'=> [
	 * 				[
	 *           		'table'=>'join_table1',
	 *           		'fields' =>['id as j_id', 'name as j_name'],
	 *           		'type'=>'left',
	 *           		'where'=>['name'=>'Sasha'],
	 *           		'operand' =>['='],
	 *           		'condition'=>['OR'],
	 *           		'on'=>['id', 'parent_id']
	 *           		'group_condition' => 'AND',
	 *           	],
	 *             'join_table2'=>[
	 * 	            'table' => 'join_table2'  	
	 *             	'fields' =>['id as j2_id', 'name as j2_name'],
	 *             	'type'=>'left',
	 *             	'where'=>['name'=>'Sasha'],
	 *             	'operand' =>['<>'],
	 *             	'condition'=>['AND'],
	 *             	'on'=>[
	 *                   'table'=>'teachers',
	 *                   'fields'=>['id', 'parent_id']
	 *                   ]
	 *                ]
	 *             ]
	 */


	/** 
	 * Метод get (read)- получает выборку данных из БД ($crud = r - SELECT) (+Выпуск №135)
	 * (На вход: 1- таблица, 2- массив данных (если он не придёт, то будем выбирать всё из данной таблицы))
	 */
	final public function get($table, $set = [])
	{
		$fields = $this->createFields($set, $table); // переменная для полей, которые хотим получить

		$order = $this->createOrder($set, $table); // переменная для хранения результата работы метода сортировки

		// (+Выпуск №135)
		$paginationWhere = $where = $this->createWhere($set, $table); // переменная для базового запроса по условию (инструкция WHERE)

		// если в переменную: $where ничего не пришло (Выпуск №22)
		if (!$where) {
			$new_where = true;
			// иначе
		} else {
			$new_where = false;
		}

		// сохраним в переменную $join_arr массив, в который придёт запрос, сформированный по принципу JOIN
		$join_arr = $this->createJoin($set, $table, $new_where);

		// в переменную $fields добавим то, что придёт в массив $join_arr (в его ячейку fields)
		$fields .= $join_arr['fields'];
		// в переменную $join сохраним то, что придёт в массив $join_arr (в его ячейку join)
		$join = $join_arr['join'];
		// в переменную $where добавим то, что придёт в массив $join_arr (в его ячейку where)
		$where .= $join_arr['where'];

		// обработаем то, что хранится в переменной $fields и обреэаем последнюю запятую
		$fields = rtrim($fields, ',');

		// если в массив set (его ячейку limit) что то пришло, то в переменную $limit запишем 'LIMIT ' . $set['limit'], 
		// иначе запишем пустую строку
		$limit = (!empty($set['limit'])) ? 'LIMIT ' . $set['limit'] : '';

		// (+Выпуск №135)
		$this->createPagination($set, $table, $paginationWhere, $limit);


		// формируем запрос в переменной $query: Выбрать поля $fields из переменной $table, далее указываем переменные, 
		// которые придут (если они есть) $join $where $order $limit
		$query = "SELECT $fields FROM $table $join $where $order $limit";

		//exit($query);

		// (+Выпуск №111) если не пусто в ячейке: $set['return_query']
		if (!empty($set['return_query'])) {

			return $query; // вернём запрос, а не выборку
		}

		// в переменную $res вернём результат работы метода query() На вход 1- переменная: $query (2-здесь- $crud = 'r' не 
		// указываем т.к. это значение по умолчанию) Выпуск №75
		$res = $this->query($query);

		// проверим существует ли у нас флаг (join_structure) по которому мы будем определять: надо ли нам 
		// структурировать данные (результирующую выборку из БД)
		if (isset($set['join_structure']) && $set['join_structure'] && $res) {

			$res = $this->joinStructure($res, $table);
		}

		return $res;
	}


	// Выпуск №135 | Пользовательская часть постраничная навигация | часть 1
	protected function createPagination($set, $table, $where, &$limit)
	{
		if (!empty($set['pagination'])) {

			// количество элементов для показа
			$this->postNumber = isset($set['pagination']['qty']) ? (int)$set['pagination']['qty'] : QTY;

			// количество ссылок
			$this->linksNumber = isset($set['pagination']['qty_links']) ? (int)$set['pagination']['qty_links'] : QTY_LINKS;

			// текущая страница
			$this->page = !is_array($set['pagination']) ? (int)$set['pagination'] : (int)($set['pagination']['page'] ?? 1);

			if ($this->page > 0 && $this->postNumber > 0) {

				// количество записей
				$this->totalCount = $this->getTotalCount($table, $where);

				// количество страниц
				$this->numberPages = (int)ceil($this->totalCount / $this->postNumber);

				$limit = 'LIMIT ' . ($this->page - 1) * $this->postNumber . ',' . $this->postNumber;
			}
		}
	}

	/**
	 * @param $table - таблица для вставки данных
	 * @param array $set - массив параметров:
	 * fields => [поле => значение]; -если не указан, то обрабатывается $_POST[поле => значение]
	 * Разрешена передача некоторых функций (например NOW()) в качестве MySql функции обычной строкой
	 * files => [поле => значение]; -можно подать массив вида [поле => [массив значений]]
	 * except => ['исключение 1', 'исключение 2'] -исключает данные элементы массива из добавленных в запрос
	 * return_id => true | false -возвращать или нет идентификатор вставленной записи
	 *@return mixed
	 */

	// Пример запроса на добавление данных в БД (единичная вставка):
	// $query = "INSERT INTO teachers (name, surname, age) VALUES ('Masha', 'Sergeevna', '21')";

	/** 
	 * Метод добавляет данные в БД (add (create)- добавить (создать)- $crud = c - INSERT)
	 * (На вход подаём переменные: $table- таблица куда будем добавлять, $set = []- по умолчанию путой массив)	   
	 */
	final public function add($table, $set = [])
	{
		// если это массив и не пуст, то сохраним его в результат, иначе сохраним суперглобальный массив $_POST
		$set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) ? $set['fields'] : $_POST;

		// если это массив и не пуст, то сохраним его в результат, иначе вернём false
		$set['files'] = (is_array($set['files']) && !empty($set['files'])) ? $set['files'] : false;


		// если ничего не пришло в ячейки: fields и files массива $set
		if (!$set['fields'] && !$set['files']) {

			// то завершим работу скрипта
			return false;
		}


		// если что то пришло в $set['return_id'], сохраним true , иначе false
		$set['return_id'] = $set['return_id'] ? true : false;

		// сделаем проверку: теперь для ячейки except массива $set (то что пришло в неё это массив? не пустой?) тогда сохраним это, иначе вернёт false
		$set['except'] = (is_array($set['except']) && !empty($set['except'])) ? $set['except'] : false;

		// примем массив вставки
		// массив создаст метод createInsert() Результат работы метода сохраним в переменной $insert_arr
		$insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);

		// формируем запрос
		// здесь- {$insert_arr['fields']} и {$insert_arr['values']}- элементы массива (пишем их в фигурных скобках, т.к. 
		// они передаются в строку(запрос) написанный в двойных кавычках)
		$query = "INSERT INTO $table {$insert_arr['fields']} VALUES {$insert_arr['values']}";

		// вернём результат работы метода query(), которому в параметры передаём переменную $query, ключ: c (т.е. создавать), ячейку return_id массива $set
		return $this->query($query, 'c', $set['return_id']);
	}

	/** 
	 * Метод редактирует данные в БД (edit (update)- редактировать (обновить)  $crud = u - UPDATE)
	 * (На вход подаём переменные: $table- таблица куда будем добавлять, $set = []- по умолчанию путой массив)	   
	 */
	final public function edit($table, $set = [])
	{
		// начало как в final public function add()
		$set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) ? $set['fields'] : $_POST;
		$set['files'] = (is_array($set['files']) && !empty($set['files'])) ? $set['files'] : false;

		if (!$set['fields'] && !$set['files']) {
			return false;
		}

		$set['except'] = (is_array($set['except']) && !empty($set['except'])) ? $set['except'] : false;

		// Если в ячейку all_rows массива $set ничего не пришло (false)
		if (!$set['all_rows']) {

			// Если в ячейку where массива $set что то пришло (true)
			if ($set['where']) {
				$where = $this->createWhere($set);
			} else {
				$columns = $this->showColumns($table);

				if (!$columns) {
					return false;
				}

				// проверка: если первичный ключ есть (в ячеке id_row массива) $columns и в массиве: $set['fields'] есть така же ячейка как и первичный ключ (т.е. $columns['id_row'])
				if ($columns['id_row'] && $set['fields'][$columns['id_row']]) {

					// тогда создадим переменную: $where и запишем строку в которой назваание поля которое имеет первичный ключ $columns['id_row'] будет равно значению. которое пришло: $set['fields'][$columns['id_row']])
					$where = 'WHERE ' . $columns['id_row'] . '=' . $set['fields'][$columns['id_row']];
					// чтобы автоинкрементное поле id_row (обновляется автоматически) разрегистрируем (удалим) его в массиве при 
					// помощи ф-ии php: unset()
					unset($set['fields'][$columns['id_row']]);
				}
			}
		}

		// в переменную $update сохраним строку (результат работы метода createUpdate())
		$update = $this->createUpdate($set['fields'], $set['files'], $set['except']);

		// далее создадим запрос к базе данных: "UPDATE (запрос на редактирование) $table (таблица) SET (инструкция: установить)
		// далее укзываем какие поля надо установить (здесь- $update) и затем где (здесь- $where")
		$query = "UPDATE $table SET $update $where";

		// вернём результат работы метода query(), которому в параметры передаём переменную $query, ключ: u (т.е.редактироать(обновить)), 
		return $this->query($query, 'u');
	}

	/**
	 * @param $table // пременная (таблица)
	 * @param array $set // переменная (массив данных (настроек)) Далее пример массива $set:
	 *           'fields' => ['id', 'name'],
	 *           'where' =>  ['fio'=>'Smirnova', 'name'=>'Masha', 'surname'=>'Sergeevna'],
	 *           'operand' =>['=', '<>'],
	 *           'condition'=>['ADN'],	            
	 *           	'join'=> [
	 * 				[				
	 *           		'table'=>'join_table1',
	 *           		'fields' =>['id as j_id', 'name as j_name'],
	 *           		'type'=>'left',
	 *           		'where'=>['name'=>'Sasha'],
	 *           		'operand' =>['='],
	 *           		'condition'=>['OR'],
	 *           		'on'=>['id', 'parent_id']
	 *           		'group_condition' => 'AND',
	 *           	],
	 *             'join_table2'=>[	              	
	 *             	'fields' =>['id as j2_id', 'name as j2_name'],
	 *             	'type'=>'left',
	 *             	'where'=>['name'=>'Sasha'],
	 *             	'operand' =>['<>'],
	 *             	'condition'=>['AND'],
	 *             	'on'=>[
	 *                   'table'=>'teachers',
	 *                   'fields'=>['id', 'parent_id']
	 *                   ]
	 *                ]
	 *             ]
	 */

	// 
	/** 
	 * Метод удаляет данные в БД (delete- удалить  $crud = d - DELETE)
	 * (На вход подаём переменные: $table- таблица куда будем добавлять, $set = []- по умолчанию путой массив)	   
	 */
	public function delete($table, $set = [])
	{
		// обрежем концевые пробелы
		$table = trim($table);
		// сформируем переменную $where, чтобы знать откуда что то убирать
		$where = $this->createWhere($set, $table);

		$columns = $this->showColumns($table);

		if (!$columns) {
			return false;
		}

		if (is_array($set['fields']) && !empty($set['fields'])) {

			// если пришло поле с первичным ключём 
			if ($columns['id_row']) {

				// сделаем поиск в массиве (ф-ия php: array_search() если находит ключ в массиве, то возвращает его порядковый 
				// номер) В параметры передаём:(1- что ищем (здесь- ($columns['id_row']), 2- массив в котором ищем (здесь- $set['fields']))
				$key = array_search($columns['id_row'], $set['fields']);

				// если ключ строго не равен false
				if ($key !== false) {

					// то надо разрегистрировать (удалить) этот элемент массива, поданный в параметры ф-ии php: unset() т.е. 
					// $set['fields'][$key]
					unset($set['fields'][$key]);
				}
			}

			$fields = [];

			foreach ($set['fields'] as $field) {
				$fields[$field] = $columns[$field]['Default'];
			}

			$update = $this->createUpdate($fields, false, false);

			// формируем запрос на редактирование данных в БД
			$query = "UPDATE $table SET $update $where";
		} else {
			$join_arr = $this->createJoin($set, $table);
			$join = $join_arr['join'];
			$join_tables = $join_arr['tables'];

			// формируем запрос на удалление данных из БД
			$query = 'DELETE ' . $table . $join_tables . ' FROM ' . $table . ' ' . $join . ' ' . $where;
		}

		// отправим (вернём) запрос
		// вернём результат работы метода query(), которому в параметры передаём переменную $query, ключ: u (т.е.редактироать(обновить)),
		return $this->query($query, 'u');
	}

	/** 
	 * Метод модели для формирования UNION запросов к базе данных (Выпуск №111)
	 */
	public function buildUnion($table, $set)
	{
		if (array_key_exists('fields', $set) && $set['fields'] === null) {

			return $this;
		}

		if (!array_key_exists('fields', $set) || empty($set['fields'])) {

			$set['fields'] = [];

			$columns = $this->showColumns($table);

			// удалим служебные поля
			unset($columns['id_row'], $columns['multi_id_row']);

			// проходимся в цикле по всем колонкам 
			foreach ($columns as $row => $item) {

				// и заполняем массив
				// (кол-во полей, которые мы выбираем из БД во всех union должно быть одинаковым (соответствовать) То что не 
				// соответствут будем дополнять: null )
				$set['fields'][] = $row;
			}
		}

		// обращаемся к св-ву: union, создаём у него ячейку: table и в неё сохраняем массив из переменой $set
		$this->union[$table] = $set;

		$this->union[$table]['return_query'] = true;

		return $this; // возвращаем указатель на контекст (на текущий объект данного класса)
	}

	// (Выпуск №111)
	/* public function test()
	{
		$a = 1;
	} */


	/** 
	 * Метод для генерации UNION запросов и их выполнения (Выпуск №112- ORM builder UNION запросов ч.2)
	 */
	public function getUnion($set = [])
	{
		if (!$this->union) {

			return false;
		}

		$unionType = ' UNION ' . (!empty($set['type']) ? strtoupper($set['type']) . ' ' : '');

		// для подсчёта наибольшего кол-ва столбцов (полей) объявим переменную
		$maxCount = 0;
		// переменная для названия таблицы с самым большим кол-вом полей (поставим её на первое место в запросе)
		$maxTableCount = '';

		foreach ($this->union as $key => $item) {

			// сохраняем из каждого $union количество элементов в ячейке: $item['fields']
			$count = count($item['fields']);

			// объявим переменную, чтобы предусмотреть работу с join
			$joinFields = '';

			if (!empty($item['join'])) {

				foreach ($item['join'] as $table => $data) {

					// если ключ: fields в переменной: $data есть и в ячейку: $data['fields'] что то пришло
					if (array_key_exists('fields', $data) && $data['fields']) {

						$count += count($data['fields']);

						$joinFields = $table;

						// иначе если
					} elseif (!array_key_exists('fields', $data) || (!$joinFields['data'] || $data['fields'] === null)) {

						$columns = $this->showColumns($table);

						unset($columns['id_row'], $columns['multi_id_row']);

						$count += count($columns);

						foreach ($columns as $field => $value) {

							$this->union[$key]['join'][$table]['fields'][] = $field;
						}

						$joinFields = $table;
					}
				}

				// иначе если ($item['join'] - пусто)
			} else {

				$this->union[$key]['no_concat'] = true;
			}

			// поиск максимального значения: 

			if ($count > $maxCount || ($count === $maxCount && $joinFields)) {

				$maxCount = $count;
				$maxTableCount = $key;
			}

			// создадим 2-е ячейки в массиве и установим для них первоначальные значения
			$this->union[$key]['lastJoinTable'] = $joinFields;
			$this->union[$key]['countFields'] = $count;
		}

		// После того как выполнили цикл (весь union готов), выстроим необходимый запрос к БД:

		$query = '';

		if ($maxCount && $maxTableCount) {

			// вставим их в запрос первыми (в скобках)
			$query .= '(' . $this->get($maxTableCount, $this->union[$maxTableCount]) . ')';

			unset($this->union[$maxTableCount]);
		}

		foreach ($this->union as $key => $item) {

			if (isset($item['countFields']) && $item['countFields'] < $maxCount) {

				// то запрос неоюходимо дополнить соответствующим кол-вом: null
				for ($i = 0; $i < $maxCount - $item['countFields']; $i++) {

					if ($item['lastJoinTable']) {

						$item['join'][$item['lastJoinTable']]['fields'][] = null;
					} else {

						$item['fields'][] = null;
					}
				}
			}


			$query && $query .= $unionType;

			$query .= '(' . $this->get($key, $item) . ')';
		}

		// сформируем переменную
		$order = $this->createOrder($set);

		$limit = !empty($set['limit']) ? 'LIMIT ' . $set['limit'] : '';


		/* if (method_exists($this, 'createPagination')) {

			$this->createPagination($set, "($query)", $limit);
		} */

		$query .= " $order $limit";

		// для корректного начала в переменную: $union положим пустой массив
		$this->union = [];

		return $this->query(trim($query));
	}

	/** 
	 * Метод даёт информацию о колонках с полями БД (+Выпуск №75)
	 */
	final public function showColumns($table)
	{
		// если ячейка: tableRows[$table] не существует или пустая
		if (!isset($this->tableRows[$table]) || !$this->tableRows[$table]) {

			// +Выпуск №78
			$checkTable = $this->createTableAlias($table);

			// если что то пришло в ячейку (т.е. проверим: не существует ли в св-ве: $this->tableRows в соответствующей 
			// ячейке, такого элемента, который хранится в $checkTable (его ячейке: ['table'])): 
			if (!empty($this->tableRows[$checkTable['table']])) {

				// то в в массиве: tableRows, создадим ячейку массива с псевдонимом таблицы, которая будет равна ячейке 
				// массива с названием таблицы ( без псевдонима)
				return $this->tableRows[$checkTable['alias']] = $this->tableRows[$checkTable['table']];
			}

			// в переменную $query сохраним результат работы запроса
			// чтобы массив в запросе (внутри двойных кавычек) преобразовать в строку, мы должны заключить его в 
			// фигурные скобки
			$query = "SHOW COLUMNS FROM {$checkTable['table']}";

			// в переменную $res придёт результат работы метода query(), на вход ему передаём переменную $query
			$res = $this->query($query);

			// объявим результирующий массив (пока пустой)
			$this->tableRows[$checkTable['table']] = [];

			// если в переменную $res что то пришло
			if ($res) {

				foreach ($res as $row) {

					// примечание: название ячеек, которые приходят из базы данных начинаются с большой буквы: $row['Field'] 
					// выстраиваем ассоциативный массив и перемещаем в него $row
					$this->tableRows[$checkTable['table']][$row['Field']] = $row;

					// если ячейка Key массива $row строго равна значению PRI (является первичным ключём)
					if ($row['Key'] === 'PRI') {

						// ф-ия php: isset() — Определяет, была ли установлена переменная (поданная на вход) значением, отличным от null
						if (!isset($this->tableRows[$checkTable['table']]['id_row'])) {

							// в корень результирующего массива: $this->tableRows[$checkTable['table']] в его ячейку id_row, положим ту ячейку $row и её поле Field, которая является первичным ключём (т.е. Key=PRI), а именно название поля
							$this->tableRows[$checkTable['table']]['id_row'] = $row['Field'];
						} else {

							// если не существует соответствующей ячейки (составного ключа: multi_id_row, которая обычно 
							// появляется при реализации связи: многие ко многим через третью таблицу)
							if (!isset($this->tableRows[$checkTable['table']]['multi_id_row'])) {

								// добавим в массив в ячейке: $this->tableRows[$checkTable['table']]['multi_id_row'] содержимое ячейки: $this->tableRows[$checkTable['table']]['id_row']
								$this->tableRows[$checkTable['table']]['multi_id_row'][] = $this->tableRows[$checkTable['table']]['id_row'];

								// и затем туда же последовательно добавим содержимое ячейки: $row['Field']
								$this->tableRows[$checkTable['table']]['multi_id_row'][] = $row['Field'];
							}
						}
					}
				}
			}
		}

		// +Выпуск №78
		if (isset($checkTable) && $checkTable['table'] !== $checkTable['alias']) {

			// то в в массиве: tableRows, создадим ячейку массива с псевдонимом таблицы, которая будет равна (указывать) по ссылке на ячейку массива с названием таблицы ( без псевдонима)
			return $this->tableRows[$checkTable['alias']] = &$this->tableRows[$checkTable['table']];
		}

		// имеем: если в запросе (в join) задали псевдоним, то результат вернётся в соответствующую ячейку
		// остальные поля вернутся как обычно 
		return $this->tableRows[$table];
	}

	/** 
	 * Метод озвращает все таблицы из БД и проверяет существуют ли вспомогательные таблицы (Выпуск №54)
	 */
	final public function showTables()
	{
		// в переменной сохраним запрос
		$query = 'SHOW TABLES';

		$tables = $this->query($query);

		$table_arr = [];

		// создадим массив таблиц вида: нумерованный ключ => название таблицы

		if ($tables) {

			foreach ($tables as $table) {

				// reset() перематывает внутренний указатель на первый элемент и возвращает значение первого элемента массива
				// в массиве: $table_arr[] на каждой итерации цикла сохраняем значение первого элемента массива, поданного на вход: (в переменной: $table)
				$table_arr[] = reset($table);
			}
		}

		return $table_arr;
	}
}
