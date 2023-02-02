<?php

namespace core\user\controller;

use core\admin\model\Model;
use core\base\controller\BaseController;

/** 
 * Индексный контроллер пользовательской части (для тестов)
 */
class IndexTestController extends BaseUser
{

	protected $name;

	// Выпуск №12
	//use trait1;
	//use trait2;
	//use trait1, trait2;
	//use trait1, trait2 {
	// если трейты имеют одноимённые методы, необходимо указать метод какого трейта является приоритетным
	// например можем записать-  методом: who() 1-го трейта заменить одноимённый метод 2-го трейта
	//trait1::who insteadof trait2;
	// или наоборот
	//trait2::who insteadof trait1;
	//}

	//use trait1, trait2 {
	// что бы пользоваться методами обоих трейдов, необходимо методу трейта, который заменили дать псевдоним
	// теперь этот метод можно вызыввать по псевдониму
	//trait1::who insteadof trait2;
	// (псевдоним можно объявить только для метода, который уже переопределён (замещён) иным метоодом !!!)
	//trait2::who as who2;
	//}


	/* protected function hello()
	{
		$template = $this->render(false, ['name' => 'Marina Sergeevna']);
		exit($template);
	} */

	protected function inputData()
	{
		// Выпуск №120
		parent::inputData();

		// Выпуск №125
		$goods = $this->model->getGoods();

		// Выпуск №124
		/* $years = $this->wordsForCounter(50384);
		$a = 1; */

		// Выпуск №121
		//$alias = '';
		//$res = $this->alias('catalog//auto', '?page=2');
		//$res = $this->alias(['catalog' => 'auto', 'girl' => 'Maria'], ['page' => 1, 'order' => 'desc']);
		//$a = 1;

		// Выпуск №121
		/* $res = $this->img(false, true);
		$a = 1; */

		echo $this->getController();
		exit;

		// Выпуск №119
		/* $this->init();

		$header = $this->render(TEMPLATE . 'header');
		$content = $this->render();
		$footer = $this->render(TEMPLATE . 'footer');

		return $this->render(TEMPLATE . 'templater', compact('header', 'content', 'footer')); */

		// Выпуск №10
		//$template = $this->render(false, ['name' => 'Masha']);
		//exit($template);


		// Выпуск №11
		//$name = 'Masha';		
		//$this->name = 'Ivan';	
		// в функцию: compact() на вход передаём переменные (их название в строковом виде) и она формирует массив, который вернём
		//return compact('name', 'surname');

		/* $name = 'Marina';
		$content = $this->render('', compact('name'));
		$header = $this->render(TEMPLATE . 'header');
		$footer = $this->render(TEMPLATE . 'footer');
		return compact('header', 'content', 'footer'); */


		// Выпуск №12
		/* $this->who();
		$this->who2();
		exit();

		$num = '1';
		$num = $this->clearNum($num);
		exit();

		$post = $this->isPost(); */

		// Выпуск №71
		//строка, которую необходимо зашифровать и затем расшифровать
		/* $str = '12ф';

		$en_str = \core\base\model\Crypt::instance()->encrypt($str);
		$dec_str = \core\base\model\Crypt::instance()->decrypt($en_str);

		exit(); */

		// Выпуск №74 Mysql связи многие ко многим
		//$model = Model::instance();

		// Получим всех учителей и узнаем всех их учеников (у кого они ведут)
		//$res = $model->get('teachers', [
		// укажем условие для выборки
		//'where' => ['id' => '51,54'],
		//'operand' => ['IN'],
		//'join' => [
		// в начале свяжем по признаку: id идентификаторы предыдущей таблицы: teachers с соответствующими 
		// идентификаторами в связывающей (текущей) таблице: stud_teach
		//'stud_teach' => ['on' => ['id', 'teachers']],
		// можем также получить студентов (связываемся с таблицей: students) и т.д.
		//'students' => [
		// назначим для необходимых в этой таблице полей псевдонимы (что бы они не переопределились)
		//'fields' => ['id as student_id', 'name as student_name'],
		//'fields' => ['name as student_name', 'content'],
		//'fields' => ['name'],
		// укажем по какому принципу (признаку) вязать: предыдущее поле (из таблицы: stud_teach) и текущее (из таблицы: students)
		//'on' => ['students', 'id']
		//]
		//],
		// добавляем флаг (чтобы выборку структурировать если это необходимо)
		//'join_structure' => true
		//]);

		// Выпуск №77 
		//$res2 = $model->get('goods', [
		// укажем условие для выборки
		//where' => ['id' => '15,16,17'],
		//operand' => ['IN'],
		//join' => [
		// в начале свяжем по признаку: идентификаторы предыдущей таблицы: 'goods'- id: (будет получено- goods_id) с 
		// соответствующими идентификаторами в связывающей (текущей) таблице: 'goods_filters'- goods_id (будет получено- filters_id)
		// (т.е. в ячейке: goods_filters получим все значения id фильтров (filters_id) из таблицы: goods_filters 
		// соответствующие id каждого конкретного товара (goods_id), связанного с полем: id в таблице: goods)
		//'goods_filters' => [
		// укажен что поля таблицы получать не надо
		//'fields' => null,
		//'on' => ['id', 'goods_id']
		//],
		// можем также получить фильтры (связываемся с таблицей: filters) и т.д.
		//'filters f' => [
		// назначим для необходимых в этой таблице полей псевдонимы (что бы они не переопределились)					
		//'fields' => ['name as filter_name', 'content'],
		// укажем по какому принципу (признаку) вязать: поле из предыдущей таблицы (goods_filters) и поле из текущей таблицы (filters f)
		//'on' => ['filters_id', 'id']
		//],
		// чтобы группировать категории фильтров, нужно получить привязку к значениям фильтров (у нас они в одной 
		// таблице: filters, при этом поле: parent_id значений фильтров, ссылаются на поле: id категорий фильтров)
		// Создадим нумерованный массив
		//[
		// укажем таблицу с которой связываемся (т.к. нельзя в запросе дважды обратиться к одной таблице, 
		// необходимо использование алиаса (псевдонима)) У нас для этого применяется метод: protected function createTableAlias()
		//'table' => 'filters',
		// по признаку (из предыдущей таблицы: (здесь- filters) поле: parent_id будет связываться с текущей таблицей (здесь- также filters)) с полем: id
		//'on' => ['parent_id', 'id']
		//]

		//],
		// добавляем флаг (чтобы выборку структурировать если это необходимо)
		//'join_structure' => true,
		//'order' => ['id'],
		//'order_direction' => ['DESC']
		//]);
		//exit;
		//}

		//protected function outputData()
		//{
		// Выпуск №11
		// получаем нулевой элемент массива (в $data), поданного на вход методу outputData() в качестве аргумента (в метод: request() в BaseController)
		//$vars = func_get_arg(0);

		// и передаём в качестве 2-го(необязателльного) параметра методу: render()
		//exit($this->render('', $vars));

		//return $vars;

		//return $this->render(TEMPLATE . 'templater', $vars);

		//$this->page = $this->render(TEMPLATE . 'templater', $vars);
	}
}
