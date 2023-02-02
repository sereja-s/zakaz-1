<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Страница авторизации</title>

	<style>
		html,
		body {
			width: 100%;
			height: 100%;
			margin: 0;
			padding: 0;
		}

		body {
			display: flex;
			justify-content: center;
			align-items: center;
		}

		div {
			flex-basis: 500px;
			padding: 15px;
		}

		form {
			display: block;
		}

		label,
		input {
			display: block;
			margin: auto;
		}

		label,
		h1 {
			font-family: Impact, sans-serif;
			font-weight: 500;
			color: #ff6d00;
			text-align: center;
			padding: 5px;
		}

		input {
			margin-bottom: 20px;
			padding: 10px 20px;
			border-radius: 4px;
			border: 2px solid #9d4edd;
		}

		input:active,
		input:focus {
			/* border: 5px solid #ff6d00; */
			background: #fdffb6;
		}

		input[type=submit] {
			padding: 10px 20px;
			border-radius: 4px;
			cursor: pointer;
			border: 1px solid #9d4edd;
			color: #fff;
			background: #ff9e00;
			transition: all 0.2s ease;
		}

		input[type=submit]:hover {
			color: #0077aa;
			background: gold;
		}
	</style>
</head>

<body>
	<div>

		<!-- Выпуск №117 -->
		<?php if (!empty($_SESSION['res']['answer'])) {

			// выведем на экран содержимое ячейки: $_SESSION['res']['answer']
			echo '<p style="color: red; text-align: center">' . $_SESSION['res']['answer'] . '</p>';

			// затем удалим то что хранится в ячейке: $_SESSION['res']
			unset($_SESSION['res']);
		}
		?>

		<h1>Авторизация</h1>
		<form action="<?= PATH . $adminPath ?>/login" method="post">
			<label for="login">Логин</label>
			<input type="text" name="login" id="login">
			<label for="password">Пароль</label>
			<input type="password" name="password" id="password">
			<input type="submit" value="Войти">
		</form>
	</div>

	<!-- Выпуск №116 -->
	<script src="<?= PATH . ADMIN_TEMPLATE ?>js/frameworkfunctions.js"></script>
	<script>
		let form = document.querySelector('form');
		if (form) {
			form.addEventListener('submit', e => {

				// проверим: это событие сгенерировано пользователем (true) или программным кодом
				if (e.isTrusted) {
					e.preventDefault();
					Ajax({
						data: {
							ajax: 'token'
						}
					}).then(res => {

						if (res) {
							form.insertAdjacentHTML('beforeend', `<input type="hidden" name="token" value="${res}">`)
						}

						form.submit()
					});
				}
			});
		}
	</script>
</body>

</html>