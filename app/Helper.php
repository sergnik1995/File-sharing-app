<?php

/**
 * Класс для подготовки данных к выводу
 */
class Helper
{
	/**
     * Возвращает подготовленные к выводу сообщения об ошибках
     * в виде массива
     *
     * @param array $errors массив с информацией об ошибках
     * @return array
     */
	public static function prepareErrors(array $errors) : array
	{
		$toView = array();
		$count = 0;
		foreach ($errors as $type => $code) {
			if($type == "file") {
				if($code == 0) {
					$toView += array($count => "Пожалуйста, выберите файл менее 100 мб который хотите загрузить");
					$count++;
				} elseif ($code == 1){
					$toView += array($count => "Вы пытаетесь загрузить файл более 100мб");
					$count++;
				}
			} elseif($type == "comment") {
				if($code == 0) {
					$toView += array($count => "Пожалуйста введите текст комментария");
					$count++;
				} elseif ($code == 1) {
					$toView += array($count => "Текст комментария не может быть длиннее 50 символов");
					$count++;
				}
			}
		}
		return $toView;
	}

    /**
     * Проверка правильности пути к файлу
     * 
     * @param $id айди файла
     * @param $name имя файла
     * @param Database $db база данных
     * @return bool
     */
	public static function isRouterPathCorrect($id, $name, Database $db) : bool
	{
		if ($db->searchById($id)) {
			$row = $db->getById($id)->fetch();
			$data = json_decode($row["data"], true);
			if ($name == $data["filename"]) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	/**
     * Возвращает подготовленные данные из базы данных
     * к выводу таблицей в виде массива
     *
     * @return array
     */
	public static function prepareTable($db) : array
	{
		$table = array();
		$count = 0;
	    while ($row = $db->fetch()) {
	    	$data = json_decode($row["data"], true);
	    	$table[$count]["date"] = $row["date"];
	    	$table[$count]["link"] = "/files/" . $row["id"] . "/" . $data["filename"];
	        $table[$count]["name"] = $data["filename"];
	        $table[$count]["size"] = $data["filesize"];
	        $count++;
	    }
	    return $table;
	}

}