<?php

namespace core\base\model;

use core\base\controller\BaseMethods;
use core\base\controller\Singleton;
use core\base\exceptions\AuthException;

/** 
 * Класс пользовательской модели (Выпуск №114- система идентификации пользователей | часть 1)
 * Методы: public function getAdminTable(); public function getBlockedTable(); public function getLastError()
 *         public function setAdmin(); public function checkUser();
 *         private function set(); private function package(); private function unPackage(); private function validate()
 *         public function logout()
 */
class UserModel extends BaseModel
{
	use Singleton;
	use BaseMethods;

	// имя куки для пользовательской части
	private $cookieName = 'identifier';

	// имя куки для административной части
	private $cookieAdminName = 'WQEngineCache';

	// свойство для пользовательских данные
	private $userData = [];

	// свойство для сбора ошибок
	private $error;

	// таблица БД (данные пользователей сайта)
	private $userTable = 'visitors';

	// таблица БД (данные администрации сайта)
	private $adminTable = 'users';

	// таблица БД (отвечает за некорректные попытки входа пользователей)
	private $blockedTable = 'blocked_access';


	/** 
	 * Метод возвращает название таблицы из свойства: adminTable
	 */
	public function getAdminTable()
	{
		return $this->adminTable;
	}

	/** 
	 * Метод возвращает название таблицы из свойства: $blockedTable
	 */
	public function getBlockedTable()
	{
		return $this->blockedTable;
	}

	/** 
	 * Метод ловит ошибки
	 */
	public function getLastError()
	{
		return $this->error;
	}

	/** 
	 * Метод автоматического создания и заполнения таблицы из $userTable и создания таблицы из $blockedTable (если таких таблиц в БД не создано)
	 */
	public function setAdmin()
	{
		// переопределим свойства
		$this->cookieName = $this->cookieAdminName;
		$this->userTable = $this->adminTable;

		// Оишем создание таблиц, участвующих в системе идентификации пользователей:

		// если таблицы из $userTable в БД нет
		if (!in_array($this->userTable, $this->showTables())) {

			//делаем запрос вв БД на её создание
			$query = 'create table ' . $this->userTable . '
                (
                    id int auto_increment primary key,
                    name varchar(255) null,
                    login varchar(255) null,
                    password varchar(32) null,
                    credentials text null
                )
                charset = utf8 
            ';

			if (!$this->query($query, 'u')) {

				exit('Ошибка создания таблицы ' . $this->userTable);
			}

			// добавим запись в таблицу БД (из $userTable) для авторизации админа (по умолчанию)
			$this->add($this->userTable, [
				'fields' => [
					'name' => 'admin',
					'login' => 'adminStroitSait(ы)',
					'password' => md5(123123123)
				]
			]);
		}

		// если нет таблицы блокирующей пользователя (из $blockedTable)
		if (!in_array($this->blockedTable, $this->showTables())) {

			// сделаем запрос в БД для ей создания
			$query = 'create table ' . $this->blockedTable . '
                (
                    id int auto_increment primary key,
                    login varchar(255) null,
                    ip varchar(32) null,
                    trying tinyint(2) null,
                    time datetime null
                )
                charset = utf8 
            ';

			if (!$this->query($query, 'u')) {

				exit('Ошибка создания таблицы ' . $this->blockedTable);
			}
		}
	}

	/** 
	 * Метод проверки пользователя, который является точкой входа (всё распределяет) 
	 * На вход: 1- идентификатор пользователя, 2- флаг администратора
	 */
	public function checkUser($id = false, $admin = false)
	{

		// если что то пришло в $admin и метод: setAdmin() ещё не был вызван, вызовем его
		$admin && $this->userTable !== $this->adminTable && $this->setAdmin();

		// устанавливаем метод выполнения по умолчанию (будет разбирать куку)
		$method = 'unPackage';

		// если пришёл: $id
		if ($id) {

			// в ячейку: userData['id'] сохраним $id
			$this->userData['id'] = $id;

			// переопределим метод
			$method = 'set';
		}

		// описываем конструкцию: try-catch
		try {

			// вызовем метод из переменной: $method
			$this->$method();
		} catch (AuthException $e) {

			// если будут выброшены ошибки, сохраним результат работы метода: getMessage() в свойстве: $error;
			$this->error = $e->getMessage();

			// если в $e->getCode() не пусто, то будем логировать ошибку в файле: log_user.txt
			!empty($e->getCode()) && $this->writeLog($this->error, 'log_user.txt');

			return false;
		}

		// если в конструкцию: catch не попали, то вернём: 
		return $this->userData;
	}

	/** 
	 * Метод, устанавливающий куку (Выпуск №115)
	 */
	private function set()
	{
		// в переменную: $cookieString сохраним результат работв метода: package()
		$cookieString = $this->package();

		// если что то пришло в переменную: $cookieString
		if ($cookieString) {

			// то устанавливаем куку (на вход: 1- имя куки, 2- значение куки, 3- время на которое устанавливаем куку 
			// (здесь- 10 лет)), 4- то на что кука будет распространяться
			setcookie($this->cookieName, $cookieString, time() + 60 * 60 * 24 * 365 * 10, PATH);

			return true;
		}

		// иначе выбросим исключение
		throw new AuthException('Ошибка формирования cookie', 1);
	}

	/** 
	 * Метод собирающий куку
	 */
	private function package()
	{
		// проверим что ячейка: userData['id'] не пустая
		if (!empty($this->userData['id'])) {

			$data['id'] = $this->userData['id'];

			$data['version'] = COOKIE_VERSION;

			// дата постановки куки, что бы сбрасывать её при необходимости
			$data['cookieTime'] = date('Y-m-d H:i:s');

			// вернём результат работы шифрровки
			return Crypt::instance()->encrypt(json_encode($data));
		}

		throw new AuthException('Не корректный идентификатор пользователя ' . $this->userData['id'], 1);
	}

	/** 
	 * Метод разбирающий куку
	 */
	private function unPackage()
	{
		if (empty($_COOKIE[$this->cookieName])) {

			throw new AuthException('Отсутствует cookie пользователя');
		}

		// декодируем имя куки
		$data = json_decode(Crypt::instance()->decrypt($_COOKIE[$this->cookieName]), true);

		if (empty($data['id']) || empty($data['version']) || empty($data['cookieTime'])) {

			$this->logout();

			throw new AuthException('Не корректные данные в cookie пользователя', 1);
		}

		// вызовем метод валидации
		$this->validate($data);

		// получим данные пользователя из БД
		$this->userData = $this->get($this->userTable, [
			// т.е. всё по id пользователя
			'where' => ['id' => $data['id']]
		]);

		if (!$this->userData) {

			$this->logout();

			throw new AuthException('Не найжены данные в таблице ' . $this->userTable . ' по идентификатору ' . $data['id'], 1);
		}

		// получим данные пользователя (что бы они сразу лежали массивом) +Выпуск №116
		$this->userData = $this->userData[0];

		return true;
	}

	/** 
	 * Метод валидации (проверяет две составляющих: версия куки и время куки)
	 */
	private function validate($data)
	{
		// если константа: COOKIE_VERSION (версия куки) установлена (не пусто)
		if (!empty(COOKIE_VERSION)) {

			// если дата в ячейке: $data['version'] не равна константе: COOKIE_VERSION
			if ($data['version'] !== COOKIE_VERSION) {

				$this->logout();

				// выбросим исключение
				throw new AuthException('Не корректная версия cookie');
			}
		}

		// если константа: COOKIE_TIME (время жизни куки) установлена (не пусто) 
		if (!empty(COOKIE_TIME)) {

			// проверка: если объект класса DateTime (из текущей метки времени) больше объекта класса DateTime (метки времени из ячейки: $data['cookieTime']), при этом модифицируем его в ф-ии: modify()
			if ((new \DateTime()) > (new \DateTime($data['cookieTime']))->modify(COOKIE_TIME . ' minutes')) {

				throw new AuthException('Превышено время бездействия пользователя');
			}
		}
	}

	/** 
	 * Метод который будет выкидывать куку пользователя
	 */
	public function logout()
	{
		setcookie($this->cookieName, '', 1, PATH);
	}
}
