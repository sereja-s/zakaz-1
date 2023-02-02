//console.log(PATH);

// опишем объект, который будет отвечать за отправку асинхронных запросов (Выпуск №68)
//  и объявим стрелочную ф-ию, в которую будет приходить объект настроек: set
// (в стрелочной ф-ии не доступен указатель на контекст(ключевое слово: this), т.е. указатель на объект (this будет искать ближайший контекст, который ему доступен (здесь- это объект: Window)))
/**
 * Объект, который будет отвечать за отправку асинхронных запросов 
 */
const Ajax = (set) => {

	//console.log(this);

	if (typeof set === 'undefined') set = {};

	if (typeof set.url === 'undefined' || !set.url) {

		set.url = typeof PATH !== 'undefined' ? PATH : '/';
	}

	// +Выпуск №95
	if (typeof set.ajax === 'undefined') {

		set.ajax = true;
	}

	if (typeof set.type === 'undefined' || !set.type) set.type = 'GET';

	set.type = set.type.toUpperCase();

	let body = '';

	if (typeof set.data !== 'undefined' && set.data) {

		// +Выпуск №95
		if (typeof set.processData !== 'undefined' && !set.processData) {

			body = set.data;

		} else {

			for (let i in set.data) {

				if (set.data.hasOwnProperty(i)) {

					body += '&' + i + '=' + set.data[i];
				}
			}

			body = body.substr(1);

			if (typeof ADMIN_MODE !== 'undefined') {

				body += body ? '&' : '';

				body += 'ADMIN_MODE=' + ADMIN_MODE;
			}
		}

	}

	if (set.type === 'GET') {

		set.url += '?' + body;
		body = null;
	}

	return new Promise((resolve, reject) => {

		let xhr = new XMLHttpRequest();

		// откроем соединение
		xhr.open(set.type, set.url, true);

		// сделаем базовые настройки соединения
		let contentType = false;

		if (typeof set.headers !== 'undefined' && set.headers) {

			for (let i in set.headers) {

				//+Выпуск №95
				if (set.headers.hasOwnProperty(i)) {

					// установим заголовки для объекта: XMLHttpRequest
					xhr.setRequestHeader(i, set.headers[i]);

					if (i.toLowerCase() === 'content-type') contentType = true;
				}

			}
		}

		// +выпуск №95
		if (!contentType && (typeof set.contentType === 'undefined' || set.contentType))
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

		// +выпуск №95
		if (set.ajax)
			// сформируем заголовок
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

		xhr.onload = function () {

			if (this.status >= 200 && this.status < 300) {

				if (/fatal\s+?error/ui.test(this.response)) {

					reject(this.response);
				}

				resolve(this.response);
			}

			reject(this.response);
		}

		xhr.onerror = function () {

			reject(this.response);
		}

		// отправим данные на сервер
		xhr.send(body);

	});

}


/** Метод проверит не пуст ли массив */
function isEmpty(arr) {

	// если цикл начнёт выполняться, значит массив не пуст
	for (let i in arr) {

		// то вернём:
		return false;
	}

	// иначе:
	return true;
}


/**
 *   Метод вывода сообщения об ошибке (Выпуск №96)
 */
function errorAlert() {

	alert('Произошла внутренняя ошибка');

	return false;
}


/**
 * Свойство: slideToggle в котором хранится функция для реализации аккордеона (Выпуск №97)
 *  (На вход: 1- время анимации, 2- параметр: callback (сообщение появляется при клике на элементе после срабатывания))
 */
Element.prototype.slideToggle = function (time, callback) {

	let _time = typeof time === 'number' ? time : 400;
	callback = typeof time === 'function' ? time : callback;

	// Функция getComputedStyle (у объекта: window) позволяет получить значение любого CSS свойства элемента, даже из CSS файла
	if (getComputedStyle(this)['display'] === 'none') {

		// то элемент надо открыть:

		// 1- его св-во: transition поставим в null
		this.style.transition = null;

		// 2- его св-во: overflow поставим в значение: hidden
		this.style.overflow = 'hidden';

		// 3- его св-во: maxHeight поставим в значение: ноль
		this.style.maxHeight = 0;

		// 4- его св-во: display поставим в значение: block (т.е. теперь можем показать элемент)
		this.style.display = 'block';

		//console.log(this);
		//console.dir(this);

		// аналогично установим значения (и конкатенируем к ним единицы измерения) следующих свойств:

		this.style.transition = _time + 'ms';

		this.style.maxHeight = this.scrollHeight + 'px';

		// вызовем функцию: setTimeout
		setTimeout(() => {

			callback && callback();
		}, _time); // укажем промежуток времени (на выполнение анимации)

		// иначе если элемент был открыт закроем его (свернём раскрывающийся список)
	} else {

		this.style.transition = _time + 'ms';

		this.style.maxHeight = 0;

		setTimeout(() => {

			this.style.transition = null;

			this.style.display = 'none'; // скроем элемент

			callback && callback();
		}, _time);
	}
}


/**
 * Опишем самовызывающуюся функцию сортировки: sortable (метод для сортировки данных использует технологию javascript drag and drop) Выпуск №102
 */
Element.prototype.sortable = (function () {

	// инициализируем переменные для элемента, который перемещается и элемента, который стоит за ним по умолчанию
	let dragEl, nextEl;

	/**  
	 * Метод будет по условию ставить потомкам и всем их вложенным потомкам свойство: draggable = false
	 */
	function _unDraggable(elements) {

		if (elements && elements.length) {

			for (let i = 0; i < elements.length; i++) {

				// если текущий элемент не имеет атрибут: draggable 
				if (!elements[i].hasAttribute('draggable')) {

					// то установим ему свойство: draggable = false
					elements[i].draggable = false;

					// рекурсивно запускаем функцию
					_unDraggable(elements[i].children);
				}
			}
		}
	}


	function _onDragStart(e) {

		// блокируем всплытие событий
		e.stopPropagation();

		this.tempTarget = null;

		// в переменную: dragEl положим элемент который начинаем тащить (e.target)
		dragEl = e.target;

		//в переменную положим св-во: nextSibling (lkz dragEl)
		// (Доступное только для чтения свойство nextSibling интерфейса Node возвращает узел, следующий сразу за указанным 
		// в childNodes их родителя, или возвращает значение null, если указанный узел является последним дочерним 
		// элементом в родительском элементе)
		nextEl = dragEl.nextSibling;

		// установим св-во в значение: перемещать
		e.dataTransfer.dropEffect = 'move';

		// добавим два слушателя событий
		// т.к. мы работаем на прототипе элемента (Element.prototype), то сам элемент, к которому будем обращаться, находится в св-ве: this
		this.addEventListener('dragover', _onDragOver, false);
		this.addEventListener('dragend', _onDragEnd, false);
	}


	function _onDragOver(e) {

		// скинем действия по умолчанию 
		e.preventDefault();

		// блокируем всплытие событий
		e.stopPropagation();

		// установим св-во в значение: перемещать
		e.dataTransfer.dropEffect = 'move';

		let target;

		if (e.target !== this.tempTarget) {

			// здесь в e.target приходит элемент над которым мы тащим злемент, который хранится в переменной: dragEl
			this.tempTarget = e.target;

			// в переменую сохраним: e.target с атрибутом: draggable = true
			target = e.target.closest('[draggable=true]');
		}



		if (target && target !== dragEl && target.parentElement === this) {

			let rect = target.getBoundingClientRect();

			// в переменную положим результат расчёта координат
			// здесь- clientY- координата по оси Y
			//        rect.top- то, что мы получили из target (верхней его координаты)
			//			 rect.bottom- то, что мы получили из target (нижней его координаты)
			// (если результат выражения > 0,5, то в let next придёт true, иначе false)
			let next = (e.clientY - rect.top) / (rect.bottom - rect.top) > .5;

			// обращаемся к this, вызываем у него метод: insertBefore()
			// на вход: 1- указыаваем что вставляем: здесь- dragEl, 2- куда вствляем (по условию: если next (true), то 
			// вставим после target.nextSibling иначе вставим после target)
			this.insertBefore(dragEl, next && target.nextSibling || target);
		}
	}

	function _onDragEnd(e) {

		// отменим действие по умолчанию
		e.preventDefault();

		// скинем два слушателя событий
		this.removeEventListener('dragover', _onDragOver, false);
		this.removeEventListener('dragend', _onDragEnd, false);

		if (nextEl !== dragEl.nextSibling) {

			// св-во this.onUpdate определяется ниже
			this.onUpdate && this.onUpdate(dragEl);
		}
	}

	// реализуем замыкание
	return function (options) {

		// в переменную положим: options (если туда что то пришло иначе- пустой объект)
		options = options || {};

		// определим свойство: this.onUpdate
		this.onUpdate = options.stop || null;

		// в переменную сохраним элементы, которые необходмо исключить из процесса: сортировки (перетаскивания)
		// Метод split() разбивает объект String на массив строк, путём разделения строки указанной подстрокой
		// (Если в options.excludedElements что то пришло, то в переменную положим options.excludedElements (строку с CSS-селекторами, которую разделим через несколько пробелов или запятая-пробел Иначе запишем в переменную: null))
		let excludedElements = options.excludedElements && options.excludedElements.split(/,*\s+/) || null;

		[...this.children].forEach(item => {

			let draggable = 'true';

			if (excludedElements) {

				for (let i in excludedElements) {

					// метод: hasOwnProperty() проверяет, является ли св-во поданное на вход, собственным свойством элемента
					// метод: matches() проверяет элемент на соответствие заданному селектору 
					if (excludedElements.hasOwnProperty(i) && item.matches(excludedElements[i])) {

						draggable = false;

						// зщавершение работы цикла
						break;
					}
				}
			}

			item.draggable = draggable;

			// вызываем метод, что бы всем элементам внутри нашего сортируемого поставить значение: draggable = false (для
			// сортировки только тех элементов, которые нужны)
			_unDraggable(item.children);
		});

		// сбосим слушатель события: dragstart, 2-ым параметром передаётся метод: _onDragStart, 3-ий параметр: false (т.е с теми же option)
		this.removeEventListener('dragstart', _onDragStart, false);

		this.addEventListener('dragstart', _onDragStart, false);
	}
})();

