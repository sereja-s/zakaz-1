<?php

namespace core\admin\model;

use core\base\controller\Singleton;
use core\base\exceptions\RouteException;
use core\base\model\BaseModel;
use core\base\settings\Settings;

/** 
 * Класс модели для административной части
 * 
 * Методы: public function showForeignKeys(); public function updateMenuPosition(); public function search();
 *         protected function createWhereOrder()
 */
class Model extends BaseModel
{
	use Singleton;

	/**	  
	 * Метод модели показывающий внешние ключи таблиц в БД (Выпуск №39)	  	 	 
	 */
	public function showForeignKeys($table, $key = false)
	{

		$db = DB_NAME;

		if ($key) {
			$where = "AND COLUMN_NAME = '$key' LIMIT 1";
		}

		// в переменной сохраним запрос к информационной БД (её таблице: KEY_COLUMN_USAGE) В условии: WHERE укажем назвааание БД 
		// и таблиц где искать , а также: CONSTRAINT_NAME <> 'PRIMARY' (т.е. не нужны первичные ключи)
		// ещё одно условие: REFERENCED_TABLE_NAME is not null $where" (т.е.таблица на которую мы ссылаемся должна быть не  пустая)

		// выбираем в информационной БД: information_schema следующие поля:
		// COLUMN_NAME (имя колонки (поле), которая ссылается на внешнюю таблицу)
		// REFERENCED_TABLE_NAME (имя таблицы на которую ссылаемся)
		// REFERENCED_COLUMN_NAME (имя колонки (поле), на которое ссылается)
		$query = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table' AND
                  CONSTRAINT_NAME <> 'PRIMARY' AND REFERENCED_TABLE_NAME is not null $where";

		return $this->query($query);
	}

	/** 
	 * Метод модели для сортировки очередности вывода записей из базы данных (Выпуск №87)
	 * На вход: 1- $table (таблица с которой работаем), 2- $row (по умолчанию: menu_position), 3- $where (инструкция 
	 * будет формироваться, если id пришёл на вход в одноимённый метод в BaseAdmin и значит будем работать отностельно 
	 * этого id), 4- $end_pos (конечная позиция, на которую у нас встанет текущая запись), 5- $update_rows (массив для 
	 * сортировки (например относительно parent_id) )  
	 */
	public function updateMenuPosition($table, $row, $where, $end_pos, $update_rows = [])
	{
		if ($update_rows && isset($update_rows['where'])) {

			// выясняем пришёл ли операнд, если не пришёл, то по умочанию операндом будет знак: = (т.е. в переменную 
			// положим массив со знаком равенства)
			$update_rows['operand'] = isset($update_rows['operand']) ? $update_rows['operand'] : ['='];

			// если пришла инструкция в переменную: $where
			if ($where) {

				// то получим старые данные из таблицы				
				$old_data = $this->get($table, [
					// нам нужны поля: то что лежит в ячейке: $update_rows['where']- например parent_id и в переменой: $row- 
					// по умолчанию: menu_position
					'fields' => [$update_rows['where'], $row],
					// в инструкцию положим массив
					'where' => $where
				])[0]; // в переменную вернём нулевой элемент

				// в стартовую позицию положим то что лежит в ячейке: $old_data[$row] (т.е. какая позиция в 
				// menu_position уже есть у элемента в БД, такая и придёт)
				$start_pos = $old_data[$row];


				// если ПОСТ который пришёл отличается от того который был (например сменилась родительская категория и соответственно parent_id) 
				if ($old_data[$update_rows['where']] !== $_POST[$update_rows['where']]) {

					// в переменную получим из таблицы, количество элементов (в родительской категории, котора была)

					// т.е. если сменился родитель посчитаем все элементы в таблице и получим то количество элементов которое 
					// есть c родителем (parent_id) из $update_rows['where'], причём который был у нашего элемента: в 
					// $old_data[$update_rows['where']], что бы и те данные перестроить относительно старого родителя: в 
					// $old_data[$update_rows['where']] и потом проUPDATE таблицу относительно нового родителя в $_POST[$update_rows['where']]
					$pos = $this->get($table, [
						'fields' => ['COUNT(*) as count'],
						'where' => [$update_rows['where'] => $old_data[$update_rows['where']]],
						'no_concat' => true
					])[0]['count']; // в переменную вернём нулевой элемент, то что хранится в ячейке: ['count']

					// проверим не является ли старая позиция элемента последним элементом (если да, то не надо 
					// модифицировать старые данные)

					// если не равно (т.е. позиция элемента не последняя)
					if ($start_pos != $pos) {

						// в переменную сохраним сформированную строку запроса для инструкций WHERE к БД
						$update_where = $this->createWhere([
							'where' => [$update_rows['where'] => $old_data[$update_rows['where']]],
							'operand' => $update_rows['operand']
						]);

						// выполним запрос, который изменит последовательность (menu_position) у всей таблицы 
						// относительно: parent_id
						$query = "UPDATE $table SET $row = $row - 1 $update_where AND $row <= $pos AND $row > $start_pos";

						// вызовем метод
						$this->query($query, 'u');
					}

					// получим другие (обновлённые) стартовые позиции (относително нового parent_id)
					$start_pos = $this->get($table, [
						'fields' => ['COUNT(*) as count'],
						'where' => [$update_rows['where'] => $_POST[$update_rows['where']]],
						'no_concat' => true
					])[0]['count'] + 1;
				}
				// если не пришла инструкция в переменную: $where
			} else {

				// Сначала получим стартовую позицию
				$start_pos = $this->get($table, [
					'fields' => ['COUNT(*) as count'],
					'where' => [$update_rows['where'] => $_POST[$update_rows['where']]],
					'no_concat' => true
				])[0]['count'] + 1;
			}

			// Далее сформируем корректирующий запрос

			if (array_key_exists($update_rows['where'], $_POST)) {

				$where_equal = $_POST[$update_rows['where']];
			} elseif (isset($old_data[$update_rows['where']])) {

				$where_equal = $old_data[$update_rows['where']];
			} else {

				$where_equal = NULL;
			}

			// и затем в переменную сохраним сформированную строку запроса для инструкций WHERE к БД
			$db_where = $this->createWhere([
				'where' => [$update_rows['where'] => $where_equal],
				'operand' => $update_rows['operand']
			]);

			// иначе (если в $update_rows ничего не пришло и ячейка: $update_rows['where'] = null)
		} else {

			// если пришла инструкция в переменную: $where, значит происходит редактирование таблицы
			if ($where) {

				// то получим первичное значение (место где элемент был раньше)
				// в переменную: $start_pos вернём нулевой элемент (то что лежит в ячейке: [$row])
				$start_pos = $this->get($table, [
					'fields' => [$row], // (по умолчанию: menu_position)
					'where' => $where
				])[0][$row]; // вернём нулевой элемент (то что лежит в ячейке: [$row])

				// иначе (если инструкция не пришла в переменную: $where (значит что мы добавляем данные))
			} else {

				// то чтобы получить стартовую позицию: посчитаем поля, активируем флаг: no_concat
				// в переменную: $start_pos вернём нулевой элемент (то что лежит в ячейке: [$count] увеличеной на единицу)
				$start_pos = $this->get($table, [
					'fields' => ['COUNT(*) as count'], // посчитаем поля
					'no_concat' => true // активируем флаг: no_concat
				])[0]['count'] + 1; // вернём нулевой элемент (то что лежит в ячейке: [$count] увеличеной на единицу)
			}
		}

		// если переменная: $db_where сформирована и отлична от null, то сохраним её в переменной: $db_where и 
		// конкатенируем к ней строку: пробел AND, иначе в переменную: $db_where положим строку: WHERE
		$db_where = isset($db_where) ? $db_where . ' AND' : 'WHERE';


		// сделаем запросы учитывая положение элемента в таблице БД
		if ($start_pos < $end_pos) {

			// запрос к БД (если номер позиции элемена в таблице стал больше)
			$query = "UPDATE $table SET $row = $row - 1 $db_where $row <= $end_pos AND $row > $start_pos";
		} elseif ($start_pos > $end_pos) {

			// запрос к БД (если номер позиции элемена в таблице стал меньше)
			$query = "UPDATE $table SET $row = $row + 1 $db_where $row >= $end_pos AND $row < $start_pos";

			// иначе (если позиции равны)
		} else {

			// ничего не изменится
			return;
		}
		// вернём результат
		return $this->query($query, 'u');
	}

	/** 
	 * Метод работы с поиском (3-ий параметр- кол-во показываемых подсказок (ссылок)) Выпуск №105
	 */
	public function search($data, $currentTable = false, $qty = false)
	{
		// получим все таблицы из БД
		$dbTables = $this->showTables();

		// экранируем слешами (для корректного поиска)
		$data = addslashes($data);

		// разбираем поисковую строку и строим поисковый массив (систему уточнений)
		// (т.е. сначала ищем всю строку (длину), потом ищем уменьшенную на один элемент и т.д)

		$arr = preg_split('/(,|\.)?\s+/', $data, 0, PREG_SPLIT_NO_EMPTY);

		// Сформируем поисковый массив

		$searchArr = [];

		$order = [];

		//  запустим цикл без условий
		for (;;) {

			if (!$arr) {

				break;
			}

			// implode()- Возвращает строку, содержащую строковое представление всех элементов массива в одном порядке со 
			// строкой-разделителем (необязательный параметр. По умолчанию используется пустая строка) между каждым элементом
			$searchArr[] = implode(' ', $arr);

			// удаляем последний элемент
			unset($arr[count($arr) - 1]);
		}

		// определим переменную (флаг) и установим ей значение по умолчанию (Выпуск №108)
		// (понадобится при выводе подсказок поиска с приоритетом той таблицы (категории) из которой осуществляется поиск)
		$correctCurrentTable = false;

		// получим свойство с таблицами проекта, в которых будет проходить поиск (связующие и т.д. исключаются) Св-во
		// применяется для проверки: существует ли указанная в нём таблица в БД 
		$projectTables = Settings::get('projectTables');

		if (!$projectTables) {
			throw new RouteException('Ошибка поиска: нет разделов в админ панели');
		}

		foreach ($projectTables as $table => $item) {

			// проверка на существование таблицы в БД
			if (!in_array($table, $dbTables)) {
				continue;
			}

			$searchRows = [];

			// массив по которому будем сортировать (кол-во полей для сортировки можно менять)
			$orderRows = ['name'];

			// массив полей по которорым будем искать
			$fields = [];

			// поля, которые есть в БД
			$columns = $this->showColumns($table);

			// поля, которые понадобятся для поиска (поле с первичным ключом)
			$fields[] = $columns['id_row'] . ' as id';

			// +Выпуск №113
			// сформируем переменую с названием поля для выпадающего меню с результатом поиска:
			// если существует ячейка: $columns['name'], то будем исползовать конструкцию: CASE и через WHEN и THEN 
			// заполнять поле: name из таблицы по указанным условиям (здесь если имя не равно пустой строке, то в 
			// переменную: $fieldName сохраним строку с именем (и названием таблицы впереди) иначе - пустую строку )
			$fieldName = isset($columns['name']) ? "CASE WHEN {$table}.name <> '' THEN {$table}.name " : '';

			foreach ($columns as $col => $value) {

				if ($col !== 'name' && stripos($col, 'name') !== false) {

					if (!$fieldName) {

						$fieldName = 'CASE ';
					}

					// +Выпуск №113
					$fieldName .= "WHEN {$table}.$col <> '' THEN {$table}.$col ";
				}

				// формируем поля в которых будем искать (здесь- по текстовому признаку (по вхождению в поле слов: char или text))
				if (
					isset($value['Type']) &&
					(stripos($value['Type'], 'char') !== false ||
						stripos($value['Type'], 'text') !== false)
				) {

					$searchRows[] = $col;
				}
			}

			if ($fieldName) {

				// сохраним в массиве, то что пришло в переменную и закроем конструкцию: CASE (описана выше) конструкцией: END и далее укажем: как псевдоним имени
				$fields[] = $fieldName . 'END as name';

				// иначе (если в $fieldName ничего не пришло)
			} else {

				// сохраним в массиве идентификатор как псевдоним имени
				$fields[] = $columns['id_row'] . ' as name';
			}

			// чтобы понимать из какой таблицы получены данные (исходя из этого значения будем фоормировать алиас)
			// добавим в массив ещё поле (с названием таблицы)
			$fields[] = "('$table') AS table_name";

			$res = $this->createWhereOrder($searchRows, $searchArr, $orderRows, $table);

			$where = $res['where'];

			// если $order ещё не заполнялось
			!$order && $order = $res['order'];


			if ($table === $currentTable) {

				$correctCurrentTable = true;

				$fields[] = "('$currentTable') AS current_table";
			}


			if ($where) {

				// обратимся к методу модели для формирования UNION запросов к базе данных (Выпуск №111)
				$this->buildUnion($table, [
					'fields' => $fields,
					'where' => $where,
					'no_concat' => true
				]);
			}
		}

		//$this->test();

		$orderDirection = null;

		// сформируем: $order
		if ($order) {

			// если correctCurrentTable имеет значение: true, зачит мы используем выбранную таблицу (применяется поиск по 
			// админке с приоритетом таблицы)
			$order = ($correctCurrentTable ? 'current_table DESC, ' : '') . '(' . implode('+', $order) . ')';

			$orderDirection = 'DESC';
		}

		// Выпуск №112- ORM builder UNION запросов ч.2
		$result = $this->getUnion([
			//'type' => 'all',
			//'pagination' => [],
			//'limit' => 3,
			'order' => $order,
			'order_direction' => $orderDirection
		]);

		//$a = 1;

		// произведём вывод поиска (подсказки (ссылки)) (+Выпуск №113)

		if ($result) {

			foreach ($result as $index => $item) {

				// корректно сформируем алиасы и name
				$result[$index]['name'] .= '(' .
					(isset($projectTables[$item['table_name']]['name'])
						? $projectTables[$item['table_name']]['name']
						: $item['table_name']) . ')';

				// сформируем готовый алиас на редактирование
				$result[$index]['alias'] = PATH .
					Settings::get('routes')['admin']['alias'] . '/edit/' . $item['table_name'] . '/' . $item['id'];
			}
		}

		return $result ?: [];
	}

	/** 
	 * Метод для формирования инструкций WHERE и ORDER для системы поиска (Выпуск №109)
	 */
	protected function createWhereOrder($searchRows, $searchArr, $orderRows, $table)
	{
		$where = '';
		$order = [];

		if ($searchRows && $searchArr) {

			$columns = $this->showColumns($table);

			if ($columns) {

				// определи первую скобку в инструкции:
				$where = '(';

				foreach ($searchRows as $row) {

					// на каждой итерации добавляем ещё одну скобку (будут группы запросов)
					$where .= '(';

					foreach ($searchArr as $item) {

						if (in_array($row, $orderRows)) {

							// символ: %- означает искать и до и после
							$str = "($row LIKE '%$item%')";

							if (!in_array($str, $order)) {

								$order[] = $str;
							}
						}


						// +Выпуск №113
						if (isset($columns[$row])) {

							$where .= "{$table}.$row LIKE '%$item%' OR ";
						}
					}


					// preg_replace() — поиск и замена регулярных выражений
					// на вход: 1- шаблон (регулярное выражение) для поиска, 2- строка (или массив со строками) для замены
					// 3- строка или массив со строками для поиска и замены (где ищем)
					$where = preg_replace('/\)?\s*or\s*\(?$/i', '', $where) . ') OR ';
				}

				// обработаем переменную ещё раз (обрежем лишний OR с пробелом в конце и добавим закрыващую скобку в конце запроса)
				$where && $where = preg_replace('/\s*or\s*$/i', '', $where) . ')';
			}
		}

		return compact('where', 'order');
	}
}
