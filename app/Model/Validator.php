<?php

namespace Model;
/**
 * Класс для проверки входящих данных на правильность
 * 
 * Коды ошибок:
 * file => 0 - Не выбран файл.
 * file => 1 - Слишком большой файл.
 * comment => 0 - Неправильный формат в поле comment
 * comment => 1 - Коммент не должен быть длинее 50 символов
 */
class Validator
{
	/** 
	 * @var array список найденных ошибок
	 */
	public $errors;

	function __construct()
	{
		$this->errors = array();
	}

    /**
     * Проверка загружаемого файла
     *
     * @return bool
     */
	public function validateFile() : bool
	{
		if(is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			echo MAX_FILE_SIZE;
			if($_FILES['userfile']["size"] > MAX_FILE_SIZE) {
				$this->errors = array_merge($this->errors, array("file" => 1));
				return 0;
			} else {
				return 1;
			}
			
		} else {
			$this->errors = array_merge($this->errors, array("file" => 0));
			return 0;
		}
	}

    /**
     * Проверка комментария
     *
     * @param string $comment комментарий
     * @return bool
     */
	public function validateComment(string $comment) : bool
	{
		if (!is_string($comment)) {
			$this->errors = array_merge($this->errors, array("comment" => 0));
			return 0;
		} elseif (mb_strlen($comment) > 50) {
			$this->errors = array_merge($this->errors, array("comment" => 1));
			return 0;
		} else {
			return 1;
		}
	}

	/**
     * Возвращает все найденные ошибки в файле и комментарии в виде массива
     * 
     * @param string $comment комментарий 
     * @return array
     */
	public function validateAll(string $comment) : array
	{
		$this->validateFile();
		$this->validateComment($comment);
		return $this->errors;
	}

}