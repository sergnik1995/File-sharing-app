<?php

/**
 * Класс для работы с базой данных
 *
 * Для подключения к базе данных использует
 * файл "db.ini"
 */
class Database 
{
	/** 
	 * @var PDO соединение с базой данных 
	 */
	private $db;

	function __construct() 
	{
		$ini = parse_ini_file("db.ini");
		$host = $ini['host'];
		$database = $ini['database'];
		$user = $ini['user'];
		$pass = $ini['pass'];
		$toPDO = 'pgsql:host='.$host.';dbname='.$database.'';
		try {
			$this->db = new PDO($toPDO, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		} catch (PDOException $e) {
		    echo 'Соединение оборвалось: ' . $e->getMessage();
		    exit;
		}
	}

    /**
     * Вставка информации о файле в базу данных
     *
     * @param string $name имя файла
     * @param string $date дата добавления файла
     * @param string $data подробная информация о файле
     * @param $commment комментарий к файлу
     * @return void 
     */
	function insert(string $name, string $date, string $data = NULL, $comment = NULL) 
	{
		$stmt = $this->db->prepare("INSERT INTO files (name, data, date, comment) VALUES (:name, :data::json, :date, :comment)");
		$stmt->bindParam(":name", $name);
		$stmt->bindParam(":data", $data);
		$stmt->bindParam(":date", $date);
		$stmt->bindParam(":comment", $comment);
		$stmt->execute();
	}

    /**
     * Обновляет информацию о файле в базе данных
     *
     * @param $id id файла в базе данных
     * @param string $name имя файла в базе данных
     * @param string $date дата добавления файла в базу данных
     * @param string $data подробная информация о файле
     * @param $comment комментарий к файлу
     * @return void
     */
	function update($id, string $name, string $date, string $data, $comment = NULL)
	{
		$stmt = $this->db->prepare("UPDATE files SET name = :name, date = :date, data = :data, comment = :comment WHERE id = :id");
		$stmt->bindParam(":id", $id);
		$stmt->bindParam(":name", $name);
		$stmt->bindParam(":data", $data);
		$stmt->bindParam(":date", $date);
		$stmt->bindParam(":comment", $comment);
		$stmt->execute();
	}

    /**
     * Удаляет информацию о файле из базы данных
     *
     * @param $id id файла в базе данных
     * @return void
     */
	function delete($id) 
	{
		$stmt = $this->db->prepare("DELETE FROM files WHERE id = :id");
		$stmt->bindParam(":id", $id);
		$stmt->execute();
	}

    /**
     * Поиск информации о файле в базе данных по его id
     *
     * @param $id id файла в базе данных
     * @return bool
     */
	function searchById($id) : bool
	{
		$stmt = $this->db->prepare("SELECT id FROM files WHERE id = :id");
		$stmt->bindParam(":id", $id);
		$stmt->execute();
		if($stmt->fetch() != FALSE) {
			return 1;
		} else {
			return 0;
		}
	}
    
    /**
     * Получение информации о файле в базе данных по его id
     *
     * @param $id id файла в базе данных
     * @return PDOStatement
     */
	function getById($id) : PDOStatement
	{
		$stmt = $this->db->prepare("SELECT * FROM files WHERE id = :id");
		$stmt->bindParam(":id", $id);
		$stmt->execute();
		return $stmt;
	}

    /**
     * Получение id последней записи о файле
     *
     * @return int
     */
	function getLastInsertId() : int
	{
		return $id = $this->db->lastInsertId("files_id_seq");
	}

    /**
     * Получение информации о 100 последних файлах
     * добавленных в базу данных
     *
     * @return PDOStatement
     */
	function getLastUploads() : PDOStatement
	{
		$q = $this->db->query("SELECT * FROM files WHERE date <= now() ORDER BY date DESC LIMIT 100");
		$q->execute();
		return $q;
	}

}