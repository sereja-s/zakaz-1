<?php

namespace core\user\controller;

use core\base\exceptions\RouteException;

/** 
 * Контроллер карточки товара (Выпуск №137)
 */
class ProductController extends BaseUser
{

	protected function inputData()
	{
		parent::inputData();

		if (empty($this->parameters['alias'])) {

			throw new RouteException('Отсутствует ссылка на товар', 3);
		}

		$data = $this->model->getGoods([
			'where' => ['alias' => $this->parameters['alias'], 'visible' => 1]
		]);

		if (!$data) {

			throw new RouteException('Отсутствует товар по ссылке ' . $this->parameters['alias']);
		}

		$data = array_shift($data);

		// Выпуск №138 (пункт меню и таб в карточке товара: доставка и оплата)
		$deliveryInfo = $this->model->get('information', [
			'where' => ['visible' => 1, 'name' => 'доставка', ' name' => 'оплата'],
			'operand' => ['=', '%LIKE%'],
			'condition' => ['AND', 'OR'],
			'limit' => 1
		]);

		//$a = 1;

		$deliveryInfo && $deliveryInfo = $deliveryInfo[0];

		return compact('data', 'deliveryInfo');
	}
}
