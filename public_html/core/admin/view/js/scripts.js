//console.log(ADMIN_MODE); Выпуск №68

// Обратимся к методам объекта: Promise, который вернётся в Ajax() после того как выполнится его ф-ия (reject() или resolve())
/* Ajax({ type: 'POST' })
	.then((res) => {

		console.log('успех - ' + res)
	})
	.catch((res) => {

		console.log('ошибка - ' + res)
	}); */

createFile()

/**
 * Метод создания файла (добавление картинки (по одной и галереи)) Выпуск №92,93,94,98
 */
function createFile() {
	// Выпуск №92
	// Добавление квадратиков в галерее админки Добавляем возможность удаления новых изображений галереи до сохранения

	// В св-ве: files будут находиться все файлы, которые мы добавили
	// в переменную: files сохраним результат работы метода: querySelectorAll, объекта: document и выберем сюда 
	// все: input[type=file] на странице форм
	// (Метод querySelectorAll() Document возвращает статический (не динамический) NodeList , содержащий все найденные
	// элементы документа, которые соответствуют указанному селектору Его свойство: length может быть равно нулю)
	let files = document.querySelectorAll('input[type=file]')

	let fileStore = [];

	// проверим еть ли что то в массиве: NodeListfiles
	if (files.length) {

		// запустим метод: forEach, который даёт нам доступ к переменной: item
		files.forEach(item => {

			// на каждый элемент: item повесим событие: onchange (через св-во)
			item.onchange = function () {

				// объявим флаг:
				let multiple = false

				// переменная будет родительским контейнером, в который будут добавляться изображения
				let parentContainer

				let container

				// проверим является ли искомый input[type=file] с множественным добавлением (т.е. установлен ли у него атрибут: multiple)
				// если атрибут (поданный на вход) есть, то метод: hasAttribute() вернёт: true
				if (item.hasAttribute('multiple')) {

					multiple = true

					// получим родительский контейнер (его элементы в котором лежат файлы (изображения)) При этом если файл в элементе есть, то это ссылка (a)
					parentContainer = this.closest('.gallery_container')

					if (!parentContainer) return false;

					// получим у родительского контейнера элементы в которох не лежат файлы (изображения) При этом если в элементах файлов нет, то это div
					// это св-во: container знает только те empty_container, которые есть на данный момент (до добавления)
					container = parentContainer.querySelectorAll('.empty_container')

					// в св-ве: files (для this- указателя на item) будут находиться все файлы, которые мы добавили в галерею
					if (container.length < this.files.length) {

						// то надо насоздавать ещё пустых квадратиков (container с классом: empty_container)
						for (let index = 0; index < this.files.length - container.length; index++) {

							// создадим элемент: div
							let el = document.createElement('div')

							// добавляем необходимые классы для квадратиков (пустых)
							el.classList.add('vg-dotted-square', 'vg-center', 'empty_container')

							// на каждой итерации цикла вставим в родительский контейнер с добавленными выше классами (для пустого элемента)
							parentContainer.append(el)

						}

						// после добавления квадратиков перезапишем св-во: container (т.е. сохраним в него изменения кол-ва пустых квадратиков)
						container = parentContainer.querySelectorAll('.empty_container')
					}
				}

				//console.log(this.files)

				// атрибут name (массив) для каждого input[type=file] сохраним в переменную
				let fileName = item.name

				// что бы удалять элементы (с изображениями) которые не были сохранены (т.е. ещё не попали в БД), повесим на
				// div c классом: empty_container атрибут (тоже что в fileName, но без [])
				let attributeName = fileName.replace(/[\[\]]/g, '');

				// если св-во: i является свойством массива: files, а не его прототипа (по цепочке наследования)
				for (let i in this.files) {

					// проверим является ли св-во: files, свойством данного элемента (объекта): (this)
					if (this.files.hasOwnProperty(i)) {

						// если добавление файлов (изображений) множественное (Выпуск №93)
						if (multiple) {

							// проверим есть ли ячейка массива: fileStore[fileName]
							// если нет
							if (typeof fileStore[fileName] === 'undefined') {

								// то создадим её
								fileStore[fileName] = [];
							}

							// В ячейку массива добавляем элементы (с помощью метода: push())

							// метод: push() после добавления возвращает новое количество выборки массива в который он добавил 
							// элементы (наш элемент меньше чем длина массива на единицу)
							// (так мы получим порядковый номер элемента, который добавился в массиве: fileStore[fileName])
							let elId = fileStore[fileName].push(this.files[i]) - 1;

							// Установим атрибут у контейнера (его i-того элемента) для того чтобы можно было удалять добавленные	 элементы 

							// метод: setAttribute() получает на вход: 1- название атрибута (с добавлением к названию атрибута 
							// того что лежит в переменной: ${attributeName}) в обратных кавычках, 2- значение атрибута: elId
							container[i].setAttribute(`data-deleteFileId-${attributeName}`, elId);

							// добавив 3-им параметром: колбэк ф-ию (Выпуск №103), получаем возможность сортировки картинок сразу после добавления
							showImage(this.files[i], container[i], function () {

								// на элементе: parentContainer вызовем сортировку (т.е. ф-ию: sortable())
								parentContainer.sortable({

									excludedElements: 'label .empty_container'
								});
							});

							// вызовем метод отвечающий за удаление новых файлов (картинок)
							// на вход: 1- значение атрибута, 2- элемент, который будем искать, 3-атрибут, 4- ячейку: container[i]
							deleteNewFiles(elId, fileName, attributeName, container[i]);

							// если нет атрибута множественного добавления (добавляем единичное изображение)
						} else {

							// в переменную положим результат работы метода поиска: closest Ищем класс родителя элемента:
							// img_container Далее у него вызываем метод: querySelector и выбираем класс: img_show
							// (в полученный контейнер: container и будем вставлять данные)
							container = this.closest('.img_container').querySelector('.img_show');

							// вызовем функцию (описана ниже), которая будет осуществлять показ при помощи объекта: FileReader 
							// (на вход: 1- конкретный элемент массива: this.files, 2- контейнер)
							showImage(this.files[i], container);

						}
					}
				}

				//console.log(fileStore)
			}

			// Выпуск №98- dragAndDrop добавление файлов в админке перетаскиванием:

			// Метод closest ищет ближайший родительский элемент, подходящий под указанный CSS селектор (здесь ищем контейнер: img_wrapper), при этом сам элемент тоже включается в поиск
			let area = item.closest('.img_wrapper');

			// если нашли
			if (area) {

				// то вызовем метод, для добавления файлов перетаскиванием (описана ниже) Здесь- item это input с type = file
				dragAndDrop(area, item);
			}

		})


		// Выпуск №94 (отправка данных на сервер)
		// на вход querySelector() передаём идентификатор формы (вернёт null или объект: Element)
		let form = document.querySelector('#main-form')

		if (form) {

			// Событие onsubmit возникает при отправке формы, это обычно происходит, когда пользователь нажимает специальную
			// кнопку: Submit
			//(на вход функции- объект: событие (e))
			form.onsubmit = function (e) {

				// Выпуск №103 | подготовка сортируемых данных для отправки на сервер
				createJsSortable(form);

				//e.preventDefault();
				//return false;

				// проверим не пуст ли массив при помощи нашей ф-ии: isEmpty()
				// Если массив не пуст
				if (!isEmpty(fileStore)) {

					//console.log('yes')

					e.preventDefault();

					// создадим объект FormData (элемент js-формы (эквивалентной форме HTML)) Получим форму в которой мы находимся: form (т.е. this) при этом JS заполнит её (нужные св-ва)
					let forData = new FormData(this);

					//console.log(forData.get('name'))

					for (let i in fileStore) {

						// если i- это его собственное свойство (которое мы туда добавляем)
						if (fileStore.hasOwnProperty(i)) {

							// очистим св-во в форме (что бы на сервер не прилетали не корректные данные)
							forData.delete(i);

							// получим чистое имя свойства (без квадратных скобок в конце)
							let rowName = i.replace(/[\[\]]/g, '');

							// пройдёмся в цикле по i-му элементу массива
							// (нам нужны: 1- переменная: item (сам элемент), 2-индекс этого элемента)
							fileStore[i].forEach((item, index) => {
								// заполним обхект: forData
								// обратимся к объекту и вызовем у него метод: append(), который добавляет в конец формы элементы
								// на вход: 1- ключ, который создастся (запишем в обратные кавычки, чтобы указывать переменные),
								// 2- значение, которое в него запишется
								forData.append(`${rowName}[${index}]`, item);
							})
						}
					}

					//console.log(forData.get('gallery_img[0]'));
					//console.log(forData.get('gallery_img[1]'));

					// добавим в объект ключ: ajax, со значением: editData
					forData.append('ajax', 'editData');

					// сформируем данные для вызова (Выпуск №95)
					// обращаемся к объекту: Ajax и передадим ему св-ва, которые нам нужны (настройка объекта)
					Ajax({
						url: this.getAttribute('action'), // есть в нашей форме (в action)
						type: 'post',
						data: forData, // сформировали переменную: data, в которую отправим объект: forData
						processData: false,
						contentType: false
					}).then(res => {

						// пришлём результат	
						try {

							res = JSON.parse(res);

							if (!res.success) {

								throw new Error();
							}

							// перезагрузка страницы
							location.reload();

						} catch (e) {

							alert('Произошла внутрення ошибка');
						}
					});
				}
			}
		}

		/**
		 * Метод отвечающий за удаление новых файлов (картинок) // Выпуск №93
		 * На вход: 1- значение атрибута, 2- элемент, который будем искать, 3-атрибут, 4- ячейку: container
		 */
		function deleteNewFiles(elId, fileName, attributeName, container) {

			//console.log(container)

			// на контейнер повесим событие: click
			container.addEventListener('click', function () {

				// метод: remove() удаляет элемент со всеми его обработчиками событий
				this.remove();

				// обращаемся к ячейке: fileStore[fileName][elId] и удаляем её с помощью инструкции: delete
				// (при этом элемента в массиве не будет, но длина массива не изменится)
				delete fileStore[fileName][elId];

				//console.log(fileStore);
			})
		}

		/**
		 * Метод, который будет осуществлять показ загруженных изображений, при помощи объкта: FileReader // Выпуск №92
		 *   (на вход: 1- конкретный элемент массива, 2- контейнер, 3- колбэк-функция (функция обратного вызова — функция, *   предназначенная для отложенного выполнения)Выпуск №103)
		 */
		function showImage(item, container, calcback) {

			// сохраним в переменную объект: FileReader
			let reader = new FileReader();

			// очистим контейнер (на случай если добавили изображение, а потом решили его поменять)
			// (т.е. почистим свойство: innerHTML контейнера)
			container.innerHTML = '';

			// у объекта: reader вызовем метод: readAsDataURL(), который прочитает файл, который пришёл в качестве base64-строки
			reader.readAsDataURL(item);

			// т.к. FileReader-асинхронный, обратимся к св-ву: onload, объекта: reader
			// т.к. указатель (this) на reader нам не нужен, поэтому это будет стрелочная функция (здесь будет объект e (событие, как произойдёт загрузка))
			reader.onload = e => {
				// когда FileReader прочитает наш элемент необходимо:

				// вызовем св-во: innerHTML для контейнера и заполним тегом: img 
				container.innerHTML = '<img class="img_item" src="">';

				// дозваниваемся до тега: img и атрибут: src поставим в то значение, которе вернётся по onload
				// метод: setAttribute() принимает на вход: 1- название атрибута, 2- значение (то что возвращает объект событие в его св-ве: target и в его св-ве: result)
				container.querySelector('img').setAttribute('src', e.target.result);

				// уберём класс у контейнера (обращаемся к объекту: classList, его методу: remove() На вход он принимает 
				// строку с названием класса)
				container.classList.remove('empty_container');

				// проверка: если в calcback что то пришло, то вызовем ф-ию: calcback()
				calcback && calcback();
			}
		}


		/**
		 * Метод, для добавления файлов в админке перетаскиванием (Выпуск №98)		 
		 */
		function dragAndDrop(area, input) {

			// опишем функционал: dragAndDrop (4-е события) в массиве:

			// 1- dragenter- событие, которое возникает когда перетаскиваем элемент (файл) и он попадает в нужную область (здесь- area)
			// 2- dragover- событие, которое возникает когда элемент (файл) двигается внутри этой области
			// 3- dragleave- событие, которое возникает когда элемент (файл) покидает выделенную область
			// 4- drop- событие, которое возникает когда элемент (файл) падает в выделенную область (отпускаем кнопку мыши)

			// Пройдёмся по этому массиву методом: forEach() Этот метод может дать три переменных (используем две):
			// 1- eventName (в каждый указанный момент времени сюда будет попадать определённое событие (элемент из массива)),
			// 2- индекс элемента массива
			['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName, index) => {

				// на каждой итерации на area будем вешать событие: eventName (т.е. одно из 4-х из массива)
				area.addEventListener(eventName, e => { // нам понадобится объект события (е)

					// на каждом событии блокируем действие по умолчанию:
					// т.е. метод preventDefault() объекта Event сообщает, что если событие не обрабатывается явно, его
					// действие по умолчанию не должно выполняться так, как обычно. 
					// Событие продолжает распространяться как обычно, до тех пор, пока один из его обработчиков не вызывает методы stopPropagation ()
					e.preventDefault();

					// на каждом событии блокируем всплытие этого события:
					// т.е. метод stopPropagation() объекта Event прекращает дальнейшую передачу текущего события 
					// (предотвращает всплытие этого события), иначе событие всплывёт до начала документа и браузер откроет данное изображение (файл)
					e.stopPropagation();

					// Далее взависимоости какое событие происходт (укажет индекс соответствующего массива) делаем определённые действия:

					// если индекс элемента (события) < 2 (т.е. dragenter- когда перетаскиваем элемент (файл) и он попадает в нужную область)
					if (index < 2) {

						// изменим св-во: background блока (цвет фона станет: lightblue)
						area.style.background = 'lightblue';

						// если мы покидаем блок (dragleave) или отпускаем элемент (drop)
					} else {

						// то фон блока возвращаем на исходный (белый)
						area.style.background = 'white';

						if (index === 3) {

							// в параметр: input (подан на вход), его св-во: files кладём то что пришло в объекте события (е), в его объекте (dataTransfer), в его объект (files)
							input.files = e.dataTransfer.files;

							// программно вызовем это собыие (onchange)
							// метод dispatchEvent()- отправляет событие в общую систему событий на вход: объект: new Event, на
							// вход которго подаём событие: change
							input.dispatchEvent(new Event('change'));
						}
					}
				});
			});
		}
	}
}


changeMenuPosition();

/**
 * Метод асинхронного пересчета позиций вывода данных при смене родительской категории (Выпуск №96)
 */
function changeMenuPosition() {

	// находим нашу форму (относительно объекта: document) по указаному в параметрах идентификатору
	let form = document.querySelector('#main-form');

	if (form) {

		// отосительно формы: form находим: select[name=parent_id]
		let selectParent = form.querySelector('select[name=parent_id]');

		// аналогично находим: select[name=menu_position]
		let selectPosition = form.querySelector('select[name=menu_position]');

		// если имеются parent_id и menu_position
		if (selectParent && selectPosition) {

			// то получим дефолтные (по умолчанию) значение переменных: selectParent и selectPosition:

			let defaultParent = selectParent.value;
			// символ: +  означает приведение значения к числу
			let defaultPosition = +selectPosition.value;

			// слушаем событие: change
			selectParent.addEventListener('change', function () {

				// объявим переменную: выбор по умолчанию и установим ей первоначальное значение
				let defaultChoose = false;

				if (this.value === defaultParent) {

					defaultChoose = true; // т.е. мы из цепочки изменений вернулись в значение по умолчанию
				}

				// После того как получили все базовые значения, необходимо отправлять данные на сервер и с сервера данные получать

				// вызываем метод: Ajax() На входе опишем объект (его св-ва)
				Ajax({
					// опишем св-ва(поля) объекта: data
					data: {
						// и далее св-ва, которые необходимо передать (вида- св-во: значение)

						table: form.querySelector('input[name=table]').value,

						'parent_id': this.value, // св-во указано в кавычках, т.к. в его названии есть символ_  Дальше идёт значение, которое сейчас пришло

						ajax: 'change_parent', // св-во, исходя из которого подключается необходимый метод в AjaxController

						// проверим есть ли в форме идентификатор tableId (что бы знать редактируем ли мы запись)

						// если нет, то отправим на сервер в качестве значения iteration единицу, иначе отправим значение 
						// обратное от defaultChoose (приведённое к числу(симввол: + впереди))
						// (т.е. если let defaultChoose = false, то итерировать нужно, иначе - нет (т.к. это выбор по умолчанию))
						iteration: !form.querySelector('#tableId') ? 1 : +!defaultChoose
					}
				}).then(res => {
					//console.log(res);

					// Далее опишем необходимые действия

					// приведём переменную к числу (а не строке)
					res = +res;

					if (!res) {

						// вернём сообщение об ошибке
						return errorAlert();
					}

					// в переменной создадим элемент с тегом: select
					let newSelect = document.createElement('select');

					// установим ему атрибут с именем: menu_position
					newSelect.setAttribute('name', 'menu_position');

					// для корректного отображения, зададим ему те классы, которые у select есть в форме (в вёрстке)
					newSelect.classList.add('vg-input', 'vg-text', 'vg-full', 'vg-firm-color1');

					for (let i = 1; i <= res; i++) {

						// если какое то значение было выбрано и оно лежит в defaultPosition, то при формировании нового select это надо учесть
						let selected = defaultChoose && i === defaultPosition ? 'selected' : '';

						// сделаем вставку option в HTML (в обратных кавычках, что бы была возможность использовать переменные в фигурных скобках)
						newSelect.insertAdjacentHTML('beforeend', `<option ${selected} value="${i}">${i}</option>`);
					}

					// вставим newSelect перед selectPosition
					selectPosition.before(newSelect);

					// теперь можно удалить selectPosition (необходимо)
					selectPosition.remove();

					// что бы отрабатывали все проверки, в переменную: selectPosition надо сохранить новую переменную: newSelect
					selectPosition = newSelect;
				})
			})
		}
	}
}


blockParameters();

/**
 * Метод реализующий аккордеон в блоках админки и работу кнопки: Выделить всё (Выпуск №97)
 */
function blockParameters() {

	// получим в переменную все контейнеры (для раскрывающихся списков)
	let wraps = document.querySelectorAll('.select_wrap');

	// проверяем на длину данного массива
	if (wraps.length) {

		// в переменную сохраним пустой массив (будет заполняться индексами соответствующих элементов при нажатии на кнопку: Выделить всё)
		let selectAllIndexes = [];

		// пройдёмся в цикле по всем найденным контейнерам и для элемента: item будем выполнять действия
		wraps.forEach(item => {

			// в переменную сохраним то, что лежит в св-ве: nextElementSibling (хранит первого следующего за ним 
			// дочернего элемента, который является элементом, и null в противном случае) для нашего элемента
			let next = item.nextElementSibling;

			// если переменная заполнена и содержит, нужный нам класс: option_wrap (раскрывающийся список)
			if (next && next.classList.contains('option_wrap')) {

				// слушаем событие: click
				item.addEventListener('click', e => {

					// если объект, на который распространяется событие не содержит класса: select_all (т.е. кнопка: Выделить всё)
					if (!e.target.classList.contains('select_all')) {

						// для детального просмотра объекта
						//console.dir(next);

						// то будем реализовывать аккордеон для блока (вызовем соответствующую функцию):
						next.slideToggle();
						//next.slideToggle(1000, function () { alert('аккардеон работает') })						

						// иначе реализуем функционал кнопки: Выделить всё
					} else {

						// получим индекс объекта, на который распространяется событие (e.target) относительно всей выборки: select_all и сохраним в переменной:

						// [...] означает деструктивное присваивание (преобразование в массив) Здесь оно необходимо т.к. 
						// индекс элемента в массиве ищет функция: indexOf(), на вход которой подаём: e.target (наш элемент в 
						// виде массива), а document.querySelectorAll() возвращает статический список нод (NodeList), в 
						// который входят все найденные в документе элементы, соответствующие указанным селекторам (не массив)
						let index = [...document.querySelectorAll('.select_all')].indexOf(e.target);

						// при нажатии кнопки: Выделить всё, каждого элемента(фильтра) на странице посмотрим индекс
						//console.log(index);

						// если условие выполнится (т.е. мы нажимаем по элементу первый раз)
						if (typeof selectAllIndexes[index] === 'undefined') {

							// активируем ячейку и ставим в значение: false
							selectAllIndexes[index] = false;
						}

						// переставим значение в обратное
						selectAllIndexes[index] = !selectAllIndexes[index];

						// у элемента: next обратимся к методу: querySelectorAll, выберем все: input с type=checkbox
						// вызываем метод: forEach В нём будет некий элемент, у этого элемента есть св-во: checked, которое
						// отвечает за заполненность чек-бокса (стоит ли галочка или др.символ), поставим его в значение из
						// ячейки: selectAllIndexes[index]
						next.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = selectAllIndexes[index]);
					}
				})
			}
		})
	}
}


showHideMenuSearch();

/**
 * Метод для показа меню и строки поиска при нажатии на соответствующие кнопки (Выпуск №99)
 */
function showHideMenuSearch() {

	// Для кнопки меню в админке:
	document.querySelector('#hideButton').addEventListener('click', () => {

		// находим главный блок с классом: vg-carcass и у его объекта: classList вызваем метод: toggle (добавляет и
		// убирает класс поданный на вход при каждом клике)
		document.querySelector('.vg-carcass').classList.toggle('vg-hide');
	});


	// Для кнопки поиска:
	let searchBtn = document.querySelector('#searchButton');

	let searchInput = searchBtn.querySelector('input[type=text]');

	// на searchBtn вешаем событие: click
	searchBtn.addEventListener('click', () => {

		// что бы блок поиска появился, добавим класс: vg-search-reverse при клике
		searchBtn.classList.add('vg-search-reverse');

		// поставим курсор на поле ввода
		searchInput.focus();
	});

	// организуем закрытие поиска при потере фокуса (щелчке на другом месте, переключении вкладок): вешаем событие: blur
	searchInput.addEventListener('blur', e => {

		// организуем в поиске переход по подсказке (ссылке) при нажатии на неё (Выпуск №113)
		if (e.relatedTarget && e.relatedTarget.tagName === 'A') {

			return
		}

		// удалим класс: vg-search-reverse (поле поиска закроется)
		searchBtn.classList.remove('vg-search-reverse');
	});
}


// (Выпуск №99- РАБОТА С ПОДСКАЗКАМИ ПРИ ВВОДЕ СТРОКИ В ПОЛЕ ПОИСКА)

// в переменную сохраним самовызывающуюся функцию, внутри которой будет реализовано замыкание (для работы с появляющимися подсказками при вводе строки в поле поиска)
// (эта функция будет возвращать другую функцию, которую мы будем вызывать по обращению к имени: searchResultHover)
let searchResultHover = (() => {

	// Инициализируем ряд переменных Эти переменные инициализируются один раз (при первом обращении к ф-ии в переменной:
	// searchResultHover) и затем будут замкнуты в участке кода до: return () => {} т.е. вызова самовызывающейся функции 
	// Каждый раз, когда мы будем повторно вызывать: searchResultHover(), они останутся нетронутыми, а будет выполняться 
	// участок кода описанный после: return () => {}:

	// найдём и сохраним класс внутри которого будет выпадающее меню с ссылками-подсказками для поиска
	let searchRes = document.querySelector('.search_res')

	// аналогично найдём input с type = text в блоке поиска (с id="searchButton")
	let searchInput = document.querySelector('#searchButton input[type=text]')

	// объявим переменную- дефолтное значение для Input поиска
	let defaultInputValue = null

	/**
	 * Метод, который будет обрабатывать нажатие стрелочек (вниз-вверх) в подсказках при поиске 
	 * (на вход: e- объект события)
	 */
	function searchKeyDown(e) {

		// если элемент с id = searchButton не содержит класса: vg-search-reverse (т.е. не активен) или не нажата кнопка:
		// стрелка-вверх и не кнопка: стрелка-вниз (в объекте: е- событие, есть свойство: key, которое и показывает какую кнопку нажали)
		if (!(document.querySelector('#searchButton').classList.contains('vg-search-reverse')) ||
			(e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) {

			// завершаем работу скрипта
			return;
		}

		// сделаем деструктивное присваивание (приведём к массиву) для содержимого из searchRes.children
		let children = [...searchRes.children];

		if (children.length) {

			// скинем действия по умолчанию 
			e.preventDefault();

			// получим активный элемент (выделенная ссылка в выпадающем меню подсказок при поиске)
			// если querySelector() ничего не найдёт, то по умолчанию вернёт: null 
			let activeItem = searchRes.querySelector('.search_act')

			// сформируем переменную по условию и получим индекс элемента, который лежит в activeItem, иначе: -1
			let activeIndex = activeItem ? children.indexOf(activeItem) : -1

			// если нажата кнопка: стрелка-вниз
			if (e.key === 'ArrowUp') {

				// сформируем переменную по условию
				// здесь (children.length - 1) означает последний элемент массива
				activeIndex = activeIndex <= 0 ? children.length - 1 : --activeIndex

				// если не нажата
			} else {

				// сформируем переменную по другому условию
				activeIndex = activeIndex === children.length - 1 ? 0 : ++activeIndex
			}

			// у всех элементов: children необходимо убрать класс: search_act (если он есть)
			children.forEach(item => item.classList.remove('search_act'))

			// обратимся к массиву в переменной: children (его ячейке: [activeIndex])  и добавим класс: search_act
			children[activeIndex].classList.add('search_act')


			// +Выпуск №113
			// в элемент: searchInput (в его значение: value) занесём значение: innerText из children[activeIndex]
			searchInput.value = children[activeIndex].innerText.replace(/\(.+?\)\s*$/, '');
		}
	}

	/**
	 * Метод установки значения по умолчанию (в строке поиска)
	 */
	function setDefaultValue() {

		// в переменную: searchInput (в его переменную: value) положим значение по умолчанию (из переменной: defaultInputValue)
		searchInput.value = defaultInputValue
	}

	// Опишем 2-а слушателя событий:
	// (На вход: 1- событие, 2- функция, должна быть вызвана только тогда, когда на элементе сработает обработчик событий (для этого передаём её в качестве параметра без круглых скобок))

	// Событие: mouseleave срабатывает, когда курсор манипулятора (обычно мыши) перемещается за границы элемента
	searchRes.addEventListener('mouseleave', setDefaultValue)

	// Событие: keydown срабатывает, когда клавиша была нажата
	window.addEventListener('keydown', searchKeyDown)

	// вернется самовызывающая функция (будет вызываться в качестве результата при каждом обращении к 
	// переменной: searchResultHover)
	return () => {

		//setTimeout(() => {

		defaultInputValue = searchInput.value;

		// если подсказки (ссылки) существуют в переменной: searchRes (его св-ве: children, его св-ве: length)
		if (searchRes.children.length) {

			// используем деструктивное присваивание (преобразуем значение из searchRes.children в массив) и сохраним в переменной: children
			let children = [...searchRes.children]

			children.forEach(item => {

				// вешаем обработчик события на событие: mouseover (наведение указателя мыши)
				item.addEventListener('mouseover', () => {

					// уберём класс который подсвечивает подсказки (ссылки)
					children.forEach(el => el.classList.remove('search_act'))

					// для элемента: item добавим класс
					item.classList.add('search_act')

					// то что лежит в innerText (для элемента: item) положим в элемент: searchInput, в его св-во: value
					searchInput.value = item.innerText
				})
			})
		}

		//}, 5000)

	}

})()

searchResultHover()


/**
 * Метод работы поиска в админке (вывод подсказок(ссылок)) Выпуск №105
 */
function search() {

	let searchInput = document.querySelector('input[name=search]');

	//console.log(searchInput);

	if (searchInput) {

		searchInput.oninput = () => {

			// сделаем ограничение (подсказки (ссылки) появятся при вводе более одного символа в поисковой строке)
			if (searchInput.value.length > 1) {

				Ajax(
					{
						// в Ajax нам нужен объект: data
						data: {
							// в котором будет три поля (свойства)
							data: searchInput.value, // в поле: data отправляем: searchInput.value
							table: document.querySelector('input[name="search_table"]').value, // ищем с приоритетом по таблицам (получим соответствующее поле)
							ajax: 'search' // управляющий флаг (для Ajax-контроллера)
						}
					}
				).then(res => {
					console.log(res);

					// Выпуск №113
					try {

						res = JSON.parse(res);
						console.log(res);
						//console.log('success');

						let resBlok = document.querySelector('.search_res');

						let counter = res.length > 20 ? 20 : res.length;

						if (resBlok) {

							resBlok.innerHTML = '';

							for (let i = 0; i < counter; i++) {

								// на вход: 1- параметр: вставляем в конец, 2-ой: что вставляем
								resBlok.insertAdjacentHTML('beforeend', `<a href="${res[i]['alias']}">${res[i]['name']}</a>`);
							}

							searchResultHover();
						}
					} catch (e) {

						console.log(e);
						alert('Ошибка в системе поиска в админ панели');
					}
				})
			} else {
				//console.log(123)
			}

		}
	}
}

search();


// Выпуск №102
// вызываем метод: sortable() для сортировки галереи в админке
let galleries = document.querySelectorAll('.gallery_container')

if (galleries.length) {

	galleries.forEach(item => {

		// вызываем метод:
		item.sortable({

			// добавим в исключения (запретим перетаскивать): ячейку с крестиком и пустые ячейки
			excludedElements: 'label .empty_container',

			stop: function (dragEl) {

				console.log(this)
				console.log(dragEl)
			}
		})
	})
}

// вызываем метод: sortable() для сортировки блоков админки (результат не сохраняется без дополнительного функционала фреймворка) 
//document.querySelector('.vg-rows > div').sortable()

/**
 * Выпуск №103 | Метод подготовки сортируемых данных для отправки на сервер
 * @param {*} form 
 */
function createJsSortable(form) {

	if (form) {

		// получим все блоки, которые надо сортирвоать (т.е. input с [type=file] и с атрибутом: multiple)
		let sortable = form.querySelectorAll('input[type=file][multiple]');

		if (sortable.length) {

			sortable.forEach(item => {

				// получим контейнер для item
				let container = item.closest('.gallery_container');

				// получим атрибут: name для item
				let name = item.getAttribute('name');

				if (name && container) {

					// удалим все скобки (здесь- квадратные) из name (меняем их на пустую строку)
					name = name.replace(/\[\]/g, '');

					// далее в форму будем вставлять input, который будет называться: js-sorting и следующая его ячйка будет называться: name (тем самым на сервер придёт массив полей отсортированных элементов)
					let inputSorting = form.querySelector(`input[name="js-sorting[${name}]"]`);

					if (!inputSorting) {

						// создадим элемент
						inputSorting = document.createElement('input');

						// установим его атрибут
						inputSorting.name = `js-sorting[${name}]`;

						// закинем созданный элемент в форму
						form.append(inputSorting);
					}

					// создадим массив, который будем в него (элемент из inputSorting) помещать
					let res = [];

					for (let i in container.children) {

						if (container.children.hasOwnProperty(i)) {

							// проверим на наличие элементов которые в сортировке не учавствуют: label и empty_container
							if (!container.children[i].matches('label') && !container.children[i].matches('.empty_container')) {

								// если равно А (ссылка- большая буква (так тег лежит в свойстве: tagName)): здесь- новодобавленный элемент
								if (container.children[i].tagName === 'A') {

									// формируем записи в массиве: в res
									res.push(container.children[i].querySelector('img').getAttribute('src'));

									// иначе это div с атрибутом: data-deletefileid (т.е. был добавлен ранее)
								} else {

									// и res формируем по другому
									res.push(container.children[i].getAttribute(`data-deletefileid-${name}`));
								}
							}
						}
					}
					console.log(res);

					// stringify()- из массива или объекта сделает строку
					inputSorting.value = JSON.stringify(res);
				}
			})
		}
	}
}

// реализуем функционал закрытия всплывающих информационных сообщений в админке (Выпуск №118)
document.addEventListener('DOMContentLoaded', () => {

	function hideMessages() {

		document.querySelectorAll('.success, .error').forEach(item => item.remove());

		//console.log(111333);

		document.removeEventListener('click', hideMessages)

	}

	document.addEventListener('click', hideMessages)
});








