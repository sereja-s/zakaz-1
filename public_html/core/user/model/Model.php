<?php

namespace core\user\model;

use core\base\controller\Singleton;

/** 
 * Пользовательская модель (Выпуск №120)
 * Методы: public function getGoods(); public function applyDiscount();
 */
class Model extends \core\base\model\BaseModel
{

	use Singleton;

	/** 
	 * Метод модели для получения каталога товаров (Выпуск №125)
	 * 1-ый параметр (настройки): $set = [] (принимает), 
	 * 2-ой (фильтры каталога) и 3-ий (цены каталога): &$catalogFilters = null и &$catalogPrices = null (возвращает по ссылке)
	 */
	public function getGoods($set = [], &$catalogFilters = null, &$catalogPrices = null)
	{
		// получим товары с id (для этого используется метод: protected function joinStructure() из BaseModelMethods, 
		// который запускается если есть ячейка: ['join_structure'] при этом вернётся массив вида: id товара => данные 
		// товара) Поэтому делаем следующую проверку:
		if (empty($set['join_structure'])) {

			$set['join_structure'] = true;
		}

		// в $set['where'] должен быть массив
		if (empty($set['where'])) {

			$set['where'] = [];
		}

		// соберём сортировку по умолчанию
		if (empty($set['order'])) {

			$set['order'] = [];

			// если не пусто в таблице: goods в ячейке: parent_id
			if (!empty($this->showColumns('goods')['parent_id'])) {

				// то в начале будем сортировать по ней
				$set['order'][] = 'parent_id';
			}

			// аналогично делаем для ячейки: price
			if (!empty($this->showColumns('goods')['price'])) {

				$set['order'][] = 'price';
			}
		}

		// получим товары получить при этом подаём уже обработанный $set
		$goods = $this->get('goods', $set);

		//$a = 1;

		// все дальнейшие действия выполняем если пришли товары
		if ($goods) {

			if (!empty($this->showColumns('goods')['discount'])) {

				foreach ($goods as $key => $item) {

					$this->applyDiscount($goods[$key], $item['discount']);
				}
			}

			unset($set['join'], $set['join_structure'], $set['pagination']);


			// Получим цены:

			if ($catalogPrices !== false && !empty($this->showColumns('goods')['price'])) {

				// MIN() и MAX()- функции SQL
				$set['fields'] = ['MIN(price) as min_price', 'MAX(price) as max_price'];

				// получим в переменную: массив с min_price(мин.цена) и max_price(макс.цена) товара из таблицы БД: goods
				$catalogPrices = $this->get('goods', $set);

				//$a = 1;

				if (!empty($catalogPrices[0])) {

					$catalogPrices = $catalogPrices[0];
				}
			}


			// Получим фильтры:

			if ($catalogFilters !== false && in_array('filters', $this->showTables())) {

				$parentFiltersFields = [];

				$filtersWhere = [];

				$filtersOrder = [];

				foreach ($this->showColumns('filters') as $name => $item) {

					if (!empty($item) && is_array($item)) {

						$parentFiltersFields[] = $name . ' as f_' . $name; // что бы отличать родителя от значения
					}
				}


				if (!empty($this->showColumns('filters')['visible'])) {

					$filtersWhere['visible'] = 1;
				}

				if (!empty($this->showColumns('filters')['menu_position'])) {

					$filtersOrder[] = 'menu_position';
				}


				// получаем фильтры
				$filters = $this->get('filters', [
					'where' => $filtersWhere,
					'join' => [
						// соединяем таблицу с самой собой
						'filters f_name' => [
							'type' => 'INNER',  // т.к. нам не нужно чтобы приходило значение если нет родителя
							'fields' => $parentFiltersFields,
							'where' => $filtersWhere,
							// укажем признак (из предыдущей таблицы- поле: parent_id смотрит на текущую- поле: id)
							'on' => ['parent_id', 'id']
						],
						// нам нужен джоин (связь) с таблицей связей
						'goods_filters' => [
							// применим расширенный режим (с указанием ключа: 'on') т.к. смотрим не на предыдущую таблицу (здесь- filters f_name), а на другую
							'on' => [
								'table' => 'filters',
								// поле из предыдущей таблицы (id) должно смотреть на поле текущей (filters_id)
								'fields' => ['id', 'filters_id']
							],
							'where' => [
								// строим подзапрос (вложенный запрос), так блок с фильтрами нужно получить для всех товаров в разделе
								'goods_id' => $this->get('goods', [
									'fields' => [$this->showColumns('goods')['id_row']],
									'where' => $set['where'] ?? null,
									// Выпуск №132
									'operand' => $set['operand'] ?? null,
									'return_query' => true
								])
							],

							'operand' => ['IN'],
						]
					],

					// 'return_query' => true
				]);

				//$a = 1;

				// Этот код перенесли выше (-Выпуск №141)
				/* if (!empty($this->showColumns('goods')['discount'])) {
					foreach ($goods as $key => $item) {
						$this->applyDiscount($goods[$key], $item['discount']);
					}
				} */

				// Сделаем подсчёт количества товаров в конкретном фильтре (относительно категории в которой находимся) отдельным запросом:

				if ($filters) {

					// implode() — объединение элементов массива со строкой
					// (Возвращает строку, содержащую строковое представление всех элементов массива в одном порядке со 
					// строкой-разделителем (здесь- запятая) между каждым элементом)
					// array_column() — возвращает значения из одного столбца во входном массиве

					// Получим все уникальные id для фильтров и товаров из массива в переменной: $filters

					$filtersIds = implode(',', array_unique(array_column($filters, 'id')));

					$goodsIds = implode(',', array_unique(array_column($filters, 'goods_id')));

					$query = "SELECT `filters_id` as id, COUNT(goods_id) as count FROM goods_filters WHERE filters_id IN ($filtersIds) AND goods_id IN ($goodsIds) GROUP BY filters_id";

					// количество товаров в конкретных фильтрах (относительно категории в которой находимся) отдельным запросом (придёт: id для каждого фильтра и кол-во товаров, для которых он применён):
					$goodsCountDb = $this->query($query);

					// $a = 1;

					$goodsCount = [];

					if ($goodsCountDb) {

						foreach ($goodsCountDb as $item) {

							// в ячейку с ключём: id (для каждого фильтра) положим значение (массив): его id и кол-во товаров, для которых он применён
							$goodsCount[$item['id']] = $item;
						}
					}

					// формируем фильтр каталога
					$catalogFilters = [];

					foreach ($filters as $item) {

						$parent = [];

						$child = [];

						foreach ($item as $row => $rowValue) {

							// определим родительскую категорию (в массиве: её данные с префиксом: f_): фильтр
							if (strpos($row, 'f_') === 0) {

								$name = preg_replace('/^f_/', '', $row);

								// в ячейку с именем родителя положим его значение
								$parent[$name] = $rowValue;

								// иначе это данные дочерней категории: значения фильтра
							} else {

								// в ячейку с именем дочерней категории положим соответственно её значение
								$child[$row] = $rowValue;
							}
						}


						if (isset($goodsCount[$child['id']]['count'])) {

							$child['count'] = $goodsCount[$child['id']]['count'];
						}

						if (empty($catalogFilters[$parent['id']])) {

							$catalogFilters[$parent['id']] = $parent;

							// создадим элемент для сбора значений фильтров
							$catalogFilters[$parent['id']]['values'] = [];
						}

						// сформируем фильтры
						$catalogFilters[$parent['id']]['values'][$child['id']] = $child;

						if (isset($goods[$item['goods_id']])) {

							if (empty($goods[$item['goods_id']]['filters'][$parent['id']])) {

								$goods[$item['goods_id']]['filters'][$parent['id']] = $parent;
								$goods[$item['goods_id']]['filters'][$parent['id']]['values'] = [];
							}

							$goods[$item['goods_id']]['filters'][$parent['id']]['values'][$item['id']] = $child;
						}
					}
				}
			}
		}

		return $goods ?? null;
	}

	/** 
	 * Метод применения скидок (Выпуск №126)
	 */
	public function applyDiscount(&$data, $discount)
	{

		if ($discount) {

			$data['old_price'] = $data['price'];

			$data['discount'] = $discount;

			$data['price'] = $data['old_price'] - ($data['old_price'] / 100 * $discount);
		}
	}
}
