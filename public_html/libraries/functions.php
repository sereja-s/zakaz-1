<?php

// функция распечатывающая массив
function print_arr($arr)
{
	echo '<pre>';
	print_r($arr); // стандартную функцию PHP (для распечатки) заключили в тег <pre> что бы показывались не печатаемые символы
	echo '</pre>';
}

// если не существует стандартной функции php (поданной на вход) 
if (!function_exists('mb_str_replace')) {
	/** 
	 * Метод для замены символов в строке (мультибайтовый) Описан в libraries/functions.php 
	 */
	function mb_str_replace($needle, $text_replace, $haystack)
	{
		return implode($text_replace, explode($needle, $haystack));
	}
}
