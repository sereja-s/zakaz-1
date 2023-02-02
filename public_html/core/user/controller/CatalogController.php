<?php

namespace core\user\controller;

use core\base\exceptions\RouteException;

/** 
 * Каталог-контроллер пользовательской части (Выпуск №129)
 * 
 * Методы: protected function createCatalogOrder(); protected function checkFilters(); protected function setFilters();
 *         protected function crossDiffArr();
 */
class CatalogController extends BaseUser
{
	protected function inputData()
	{
		parent::inputData();

		// (-Выпуск №131)
		/* $order = [
			'price' => 'цене',
			'name' => 'названию'
		]; */

		// +Выпуск №134
		// количество товаров для отображения на странице каталога
		$quantities = [3, 6, 12];

		// Сформируем название для страницы каталог, взависимости от того в какой категории находимся
		$data = [];

		if (!empty($this->parameters['alias'])) {

			$data = $this->model->get('catalog', [
				'where' => ['alias' => $this->parameters['alias'], 'visible' => 1],
				'limit' => 1
			]);

			if (!$data) {

				throw new RouteException('Не найдены записи в таблице catalog по ссылке ', $this->parameters['alias']);
			}

			$data = $data[0];
		}


		// сформируем инструкцию для товаров
		$where = ['visible' => 1];

		if ($data) {

			// Выпуск №144
			$where['parent_id'] = $data['id'];
		} else {

			$data['name'] = 'Каталог';
		}

		// +Выпуск №131
		$catalogFilters = $catalogPrices = $orderDb = null;

		// Выпуск №131
		$order = $this->createCatalogOrder($orderDb);

		// Выпуск №132
		$operand = $this->checkFilters($where);


		// Получим товары (с их фильтрами и ценами):

		$goods = $this->model->getGoods([
			'where' => $where,
			// Выпуск №132
			'operand' => $operand,
			// Выпуск №131
			'order' => $orderDb['order'],
			'order_direction' => $orderDb['order_direction'],
			// Выпуск №135
			'pagination' => [
				'qty' => $_SESSION['quantities'] ?? QTY,

				'page' => $this->clearNum($_GET['page'] ?? 1) ?: 1
			]
		], $catalogFilters, $catalogPrices);

		//$a = 1;

		// Выпуск №136
		$pages = $this->model->getPagination();

		return compact('data', 'catalogFilters', 'catalogPrices', 'goods', 'order', 'quantities', 'pages');
	}


	/** 
	 * Метод сортировки каталога товаров (В каталоге реализуем- сортировать по:) (Выпуск №131)
	 */
	protected function createCatalogOrder(&$orderDb)
	{

		$order = [

			'цене' => 'price_asc',

			'названию' => 'name_asc'

		];

		// изначально инициализируем массив
		$orderDb = ['order' => null, 'order_direction' => null];

		// если в сортировщик что то пришло
		if (!empty($_GET['order'])) {

			$orderArr = preg_split('/_+/', $_GET['order'], 0, PREG_SPLIT_NO_EMPTY);

			if (!empty($this->model->showColumns('goods')[$orderArr[0]])) {

				$orderDb['order'] = $orderArr[0];

				$orderDb['order_direction'] = $orderArr[1] ?? null;

				// здесь надо понять какая сортировка сейчас выбрана пользователем
				foreach ($order as $key => $item) {

					if (strpos($item, $orderDb['order']) === 0) {

						$direction = $orderDb['order_direction'] === 'asc' ? 'desc' : 'asc';

						$order[$key] = $orderDb['order'] . '_' . $direction;

						break;
					}
				}
			}
		}

		return $order;
	}


	/** 
	 * Метод фильтрации каталога (Выпуск №132)
	 */
	protected function checkFilters(&$where)
	{

		$dbWhere = [];

		$dbOperand = [];

		if (isset($_GET['min_price'])) {

			$dbWhere['price'] = $this->clearNum($_GET['min_price']);

			$dbOperand[] = '>=';
		}

		if (isset($_GET['max_price'])) {

			// здесь ячейка: [' price'] названа с побелом в начале, что бы не переопределилась
			$dbWhere[' price'] = $this->clearNum($_GET['max_price']);

			$dbOperand[] = '<=';
		}


		// Выпуск №133 Пользовательская часть | система перекрестных фильтров
		if (!empty($_GET['filters']) && is_array($_GET['filters'])) {


			$subFiltersQuery = $this->setFilters();

			if ($subFiltersQuery) {

				$dbWhere['id'] = $subFiltersQuery;

				$dbOperand[] = 'IN';
			}


			// -Выпуск №133
			/* $dbWhere['id'] = $this->model->get('goods_filters', [
				'fields' => ['goods_id'],
				'where' => ['filters_id' => implode(',', $_GET['filters'])],
				'operand' => ['IN'],
				'return_query' => true // что бы вернулся запрос
			]); */
		}

		$where = array_merge($dbWhere, $where);

		$dbOperand[] = '=';

		return $dbOperand;
	}

	/** 	
	 * Метод получает значения фильтров (их сочетания) Выпуск №133- Пользовательская часть | система перекрестных фильтров
	 */
	protected function setFilters()
	{

		foreach ($_GET['filters'] as $key => $item) {

			$_GET['filters'][$key] = $this->clearNum($item);

			if (!$_GET['filters'][$key]) {

				unset($_GET['filters'][$key]);

				continue;
			}

			// поищем дубликаты (что бы снять нагрузку с БД)
			$other = array_search($_GET['filters'][$key], $_GET['filters']);

			if ($other !== false && $other !== $key)
				unset($_GET['filters'][$key]);
		}

		// получим фильтры с привязкой к родителям
		$res = $this->model->get('filters', [
			'where' => ['id' => 'SELECT DISTINCT parent_id FROM filters WHERE id IN(' . implode(',', $_GET['filters']) . ')'],
			'operand' => ['IN'],
			'join' => [
				// соединяемся этой же таблицей (здесь- filters), но применим псевдоним: f_val (здесь нужны значения)
				'filters f_val' => [
					'where' => ['id' => implode(',', $_GET['filters'])],
					'operand' => ['IN'],
					'fields' => ['id'],
					// укажем по какому критерию: id из предыдущей таблицы (здесь- filters) смотрит на parent_id из текущей 
					// (здесь таже ,но с псевдонимом: filters f_val)
					'on' => ['id', 'parent_id']
				]
			],

			'join_structure' => true
			//'return_query' => true

		]);

		// По условию соберём массив массивов (реализуем систему перекрестных фильтров): 

		if ($res) {

			$arr = [];

			$c = 0;

			foreach ($res as $item) {

				if (isset($item['join']['f_val'])) {

					// получим массивы в которых будут разложены id значений, которые были отмечены пользователем (для 
					// каждого фильтра, содеращего выбранный значения)
					$arr[$c] = array_column($item['join']['f_val'], 'id');

					$c++;
				}
			}

			// найдём перекрёсные сочетания значений от разных фильтров в полученном массиве
			$resArr = $this->crossDiffArr($arr);

			// разберём полученный массив
			if ($resArr) {

				$queryStr = '';

				// нужна переменная, т.к. будет применяться инструкция группировки: GROUP с инструкцией: HAVING
				// (изначально инициализируем её нулём)
				$filtersCount = 0;

				foreach ($resArr as $key => $item) {

					!$filtersCount && $filtersCount = count($item);

					$queryStr .= ' filters_id IN(' . implode(',', $item) . ')' . (isset($resArr[$key + 1]) ? ' OR ' : '');
				}

				return 'SELECT goods_id FROM goods_filters WHERE ' . $queryStr . ' GROUP BY goods_id HAVING COUNT(goods_id) >= '  . $filtersCount;
			}
		}

		return '';
	}

	/** 
	 * Метод получает перекрёсные сочетания значений от разных фильтров, которые отметил и применил пользователь в каталоге (Выпуск №133)
	 * На вход: 1-массив, 2-счётчик, исходя из которого будем забираться вверх
	 */
	protected function crossDiffArr($arr, $counter = 0)
	{
		// если пришёл в массиве только один элемент (массив)
		if (count($arr) === 1) {

			// разделим этот массив, по-элементно и вернём эти элементы
			return array_chunk(array_shift($arr), 1);
		}

		// проверим добрались ли мы до самого первого элемента (шли с конца массива)
		if ($counter === count($arr) - 1)
			return $arr[$counter];

		// Вызовем рекурсивно этот же метод (он будет проходить и буферизовать все данные, и от последнего к первому их возвращать)
		$buffer = $this->crossDiffArr($arr, $counter + 1);

		$res = [];

		foreach ($arr[$counter] as $a) {

			foreach ($buffer as $b) {

				$res[] = is_array($b) ? array_merge([$a], $b) : [$a, $b];
			}
		}

		return $res;
	}
}
