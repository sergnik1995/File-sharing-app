<?php

namespace Controllers;

use Model\Helper;
use Model\File;

/**
 * Воспроизведение аудио/видео
 */
class WatchController extends Controller
{
	/**
	 * @var Slim $app
	 * @var PDO $db
	 */
	protected $app;
	protected $db;

	function __construct($app, $db)
	{
		$this->app = $app;
		$this->db = $db;
	}

	/**
	 * @param int $id айди файла в базе данных
	 * @param string $name имя файла в базе данных
	 */
	public function run($id, $name) {
		if(Helper::checkPathCorrect($id, $name, $this->db)) {
			$row = $this->db->getById($id)->fetch();
			$data = json_decode($row["data"], true);
			$mime = $data["mime_type"];
			$size = $data["filesize"];
			$file = $data["filepath"] ."/". $row["name"];

			File::watch($file, $mime, $size);
		}
	}
}