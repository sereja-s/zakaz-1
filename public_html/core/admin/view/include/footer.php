			<!--.vg-main.vg-right-->
			</div>
			<!--.vg-carcass-->
			</div>

			<!-- вывод информационных сообщений в админке -->
			<div class="vg_modal vg-center">
				<?php

				// если существует ячейка: $_SESSION['res']['answer'] 
				// (сюда приходит из методов (например валидатора) описанных в BaseAdmin.php)
				if (isset($_SESSION['res']['answer'])) {

					// покажем то, что в ней есть
					echo $_SESSION['res']['answer'];

					// и затем разрегистрировать ячейку: $_SESSION['res']
					// (если пользователь валидацию не прощёл, его данные сохранятся Если при этом перезагрузил страницу, то 
					// придётся заполнять форму снова)
					unset($_SESSION['res']);
				}

				?>

			</div>

			<!--  Подключим скрипты (Выпуск №67)-->
			<script>
				const PATH = '<?= PATH ?>';
				const ADMIN_MODE = 1; /* объявили админ. режим */

				// Выпуск №106- javascript подключение визуального редактора tinymce 5
				const tinyMceDefaultAreas = '<?= implode(',', $this->blocks['vg-content']) ?>'
			</script>

			<?php $this->getScripts(); ?>

			</body>

			</html>