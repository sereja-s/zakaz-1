<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;
use core\base\model\BaseModel;
use core\base\model\UserModel;
use core\base\settings\Settings;
use core\user\controller\BaseUser;

class IndexController extends BaseController
{
	protected function inputData()
	{
		$db = Model::instance();

		//$query = "SELECT * FROM articles";

		// Выпуск №17
		// вложенный запрос:
		//$query = "SELECT id, name FROM product WHERE parent_id = (SELECT id FROM category WHERE name = 'Apple')";

		// Связь: ОДИН КО МНОГИМ
		// получим продукты по id категории применив инструкцию LEFT JOIN
		//$query = "SELECT product.id, product.name FROM product LEFT JOIN category ON product.parent_id = category.id WHERE category.id = 1";

		// получим и продукты и категории
		//$query = "SELECT category.id, category.name, product.id as p_id, product.name as p_name FROM product LEFT JOIN category ON product.parent_id = category.id";

		// Связь: МНОГИЕ КО МНОГИМ
		//$query = "SELECT teachers.id, teachers.name, students.id as s_id, students.name as s_name FROM teachers LEFT JOIN stud_teach ON teachers.id = stud_teach.teachers LEFT JOIN students ON stud_teach.students = students.id";

		// испольуем метод библиотеки mysqli: query(), которому на вход подаём: $query
		//$res = $db->query($query);


		// Выпуск №18
		//$table = 'teachers';

		// Выпуск №29(удаления данных из БД)
		// Заполним (для тестов) таблицы в БД в цикле:
		//for ($i = 0; $i < 8; $i++) {

		//	$s_id = $db->add('students', [

		//		'fields' => ['name' => 'student-' . $i, 'content' => 'content-' . $i],
		//		'return_id' => true
		//	]);

		//	$db->add('teachers', [

		//		'fields' => ['name' => 'teacher-' . $i, 'content' => 'content-' . $i, 'student_id' => $s_id],
		//		'return_id' => true
		//	]);
		//}

		//$res = $db->delete($table, [

		//'fields' => ['name', 'content'],
		//'where' => ['id' => 38],
		/* 'join' => [
				'students' => [
					'on' => ['student_id', 'id']
				]
			] */
		//'join' => [

		//[
		//'table' => 'students',
		//'on' => ['student_id', 'id']
		//]
		//]
		//]);



		//Выпуск №25
		// Добавление данных в БД
		//$files['gallery_img'] = [""];
		//$files['img'] = '';

		//$_POST['id'] = 19;
		//$_POST['name'] = 'Zaia';
		//$_POST['content'] = "car";

		//$res = $db->edit($table);


		// Выпуск №27 (редактирование)
		//$res = $db->edit($table, [

		//'fields' => ['id' => 2, 'name' => 'Sveta']
		//'files' => $files,
		//'where' => ['id' => 1]
		//]);


		// Выпуск №26
		// добавляем данные в БД непосредственно через суперглобальный массив: $_POST
		//$_POST['name'] = 'Masha';

		//$res = $db->add($table, [

		//'fields' => ['id' => 3, 'name' => 'Olga', 'content' => 'Hello'],
		//'fields' => ['name' => 'Katya1', 'content' => 'Hello1'],
		//'except' => ['content'],
		//'files' => $files
		//]);

		//exit($res);

		// Выпуск №23
		/* $res = $db->get($table, [

			'fields' => ['id', 'name'],
			'where' => ['name' => "O'Raily"],
			'limit' => 1
		])[0];*/

		//exit('id = ' . $res['id'] . ' Name = ' . $res['name']);

		// Выпуск №21
		// (пример использования оператора: LIKE)

		// $query = "SELECT * FROM teachers WHERE name LIKE '%Марья%'";

		// если name LIKE 'Марья' это означает тоже что и name = 'Марья'
		// -знак: % в начале искомой строки (здесь- Марья) означает, что впереди могут идти любые символы 
		// -знак: % в конце искомой строки (здесь- Марья) означает, что в конце могут идти любые символы 
		// -знак: % в начале и в конце искомой строки (здесь- Марья) означает, что в начале и в конце могут идти любые 
		// символы (т.е. ищем вхождение строки в подстроку)

		// Выпуск №22
		// пример выборки со связыванием(левое объединение) таблиц (LEFT JOIN) И объединением запросов (UNION):

		// $query = "(SELECT t1.name, t2.fio FROM t1 LEFT JOIN t2 ON t1.parent_id = t2.id WHERE t1.parent_id = 1)
		//  UNION
		//  (SELECT t1.name, t2.fio FROM t1 LEFT JOIN t2 ON t1.parent_id = t2.id WHERE t2.id = 1)
		//  здесь сортировку можно сделать только по порядковому номеру поля, которое мы выбираем (здесь сортируем по 1-му полю ( t1.name)
		//  ORDER BY 1 ASC";

		//$color = ['red', 'blue', 'black'];

		// Выпуск №25 (Пример вывода массива в виде json-строки) Так массивы сохраняются в БД
		//$c = json_encode($color);

		//echo $c . '<br>';

		// на выходе json-строку преобразуем обратно в массив:
		//exit(print_arr(json_decode($c)));

		// вызовем метод get (read)- получить (прочитать)
		//$res = $db->get($table, [

		// то что нам необходимо выбрать
		//'fields' => ['id', 'name', 'content'],

		// условие (инструкция WHERE)
		//'where' => ['name' => 'Иванова Марья Ивановна, Пётр Петрович, Марина', 'content' => "до Контент"],
		//'where' => ['name' => 'Masha', 'surname' => 'Sergeevna', 'fio' => 'Andrey', 'car' => 'Porsche', 'color' => $color],
		//'where' => ['name' => 'Марина', 'surname' => 'SELECT name FROM students WHERE id = 1'],
		//'where' => ['name' => "O'Raily"],

		// укажем какой операнд использовать в условии (в инструкции WHERE)
		// (может быть или не быть (по умолчанию имеет значение: = (равно)))
		// для каждого элемента в условии можно указать свой операнд
		// (если указать элементов больше чем указано операндов, то для лишних элементов в условии (по умолчанию) будет 
		// браться последний операнд)
		//'operand' => ['IN', 'LIKE%', '<>', '=', 'NOT IN', '<>'],

		// укажем варианты условий (AND или OR) по которому будем объединять элементы записанные в инструкции WHERE:
		// (принципы их применения такие же как для операндов)
		// (может быть или не быть (по умолчанию имеет значение: AND (и)))
		//'condition' => ['AND', 'OR'],

		// передадим сортировку (указание по каким полям сортировать)
		//'order' => ['name', 'content'],

		// направление сортировки (по возрастанию, по убыванию)
		// (принципы применения такие же как для операндов)
		//'order_direction' => ['ASC', 'DESC'],

		// настройка LIMIT (количество элементов в выборке)
		//'limit' => '3'

		// делаем объединение таблиц (1-ый вариант реализации массива 'join' когда в нём два одинаковых элемента)
		// т.е. когда таблица стыкуется сама с собой через третью таблицу (связь: многие ко многим) или когда приходит элемент в массив с цифровым ключём:
		//'join' => [

		// таблица с полями:
		// заполняем нулевой элемент массива т.е. делаем запись вида: 0 => [] или: 
		//[

		// 1-ое поле (необязательное): к какой таблице присоединять
		// (если это поле не придёт, то по умолчанию будем присоединяться к предыдущей таблице т.е. здесь присоединимся к таблице в $table)
		//'table' => 'join_table1',

		// 2- поля, которые необходимо добавить к полям в выборки из 1-ой таблицы (для одноимённых используем псевдонимы)
		//'fields' => ['id as j_id', 'name as j_name'],

		// 3- указывается тип присоединения (необязательное (по умолчанию используем: left)):
		//  INNER JOIN– внутреннее соединение Этот вид джойна выведет только те строки, если условие соединения 
		// выполняется (является истинным, т.е. TRUE). В запросах необязательно прописывать INNER – если написать 
		// только JOIN, то СУБД по умолчанию выполнить именно внутреннее соединение

		// LEFT JOIN и RIGHT JOIN- Левое и правое соединения еще называют внешними. Главное их отличие от 
		// внутреннего соединения в том, что строка из левой (для LEFT JOIN) или из правой таблицы (для RIGHT 
		// JOIN) попадет в результаты в любом случае Левая таблица та, которая идет перед написанием ключевых 
		// слов [LEFT | RIGHT| INNER] JOIN, правая таблица – после них
		//'type' => 'left',

		// 4- инструкция WHERE (будет дополнять соответствующую инструкцию для 1-ой таблицы):
		//'where' => ['name' => 'Sasha'],

		// 5- операнд:
		//'operand' => ['='],

		// 6- укажем варианты условий (AND или OR):
		//'condition' => ['OR'],

		// 7- укажем по какому признаку будем присоединять:
		//'on' => [
		// явно укажем к какой таблице присоединять (по умолчанию: предыдущая (здесь- teachers))
		//'table' => 'teachers',
		// массив с количеством полей: 2
		//'fields' => ['id', 'parent_id']
		//]

		// то же запишем короче (т.к. идёт циклическое объединение с предыдущей таблицей)
		// (здесь- id-поле из предыдущей таблицы, parent_id-поле из текущей таблицы)
		//'on' => ['id', 'parent_id'],

		// предоставим возможность указывать групповое объединение (можно не указывать и по умолчанию будет установлено: AND)
		//'group_condition' => 'AND'

		//	],

		/* 'join_table2' => [

					'table' => 'join_table2',
					'fields' => ['id as j2_id', 'name as j2_name'],
					'type' => 'left',
					'where' => ['name' => 'Sasha2'],
					'operand' => ['<>'],
					'condition' => ['AND'],
					'on' => [
						//'table' => 'teachers',
						'fields' => ['id', 'parent_id']
					]
				] */
		//	]

		// (2-ый вариант реализации массива 'join' когда в нём разные элемента стыкуются разные таблицы):

		/* 'join' => [

				'join_table1' => [

					'table' => 'join_table1',
					'fields' => ['id as j1_id', 'name as j1_name'],
					'type' => 'left',
					'where' => ['name' => 'Sasha'],
					'operand' => ['='],
					'condition' => ['OR'],
					'on' => [
						'table' => 'teachers',
						'fields' => ['id', 'parent_id']
					]
				],

				'join_table2' => [

					'table' => 'join_table2',
					'fields' => ['id as j2_id', 'name as j2_name'],
					'type' => 'left',
					'where' => ['name' => 'Sasha2'],
					'operand' => ['='],
					'condition' => ['AND'],
					'on' => [
						'table' => 'teachers',
						'fields' => ['id', 'parent_id']
					]
				]
			] */

		//]);

		//exit('I am admin panel');
		//exit(print_arr($res));
		//=====================================================================================================================



		// пропишем путь куда мы будем перенапралять на контроллер (будем считать по умолчанию) и сохраним его в переменной: $redirect-
		// константа PATH (корень сайта), далее обратимся к настройкам: Settings, там к методу get() и получить массив: routes 
		// (нужны только: его ячейка ['admin'] и далее ячейка ['alias'] и затем конкатенируем название контроллера после слеша: /show)
		$redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

		// обратимся к методу redirect(), который перенаправляет пользователя (на вход передаём: путь (переменную $redirect))
		$this->redirect($redirect);
	}
}

// Используемые методы (CRUD): 

// add (create)- добавить (создать)
// edit (update)- редактировать (обновить)
// get (read)- получить (прочитать)
// delete- удалить