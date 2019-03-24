<?php

namespace Controllers;

use Model\Helper;

/**
 * Страница скачивания файла 
 */
class DownloadPageController extends Controller
{
	/**
	 * @var Slim $app
	 * @var Twig_Environment $twig
	 * @var PDO $db
	 */
	protected $app;
	protected $twig;
	protected $db;

	function __construct($app, $twig, $db)
	{
		$this->app = $app;
		$this->twig = $twig;
		$this->db = $db;
	}

	/**
 	 * @param int $id айди файла в базе данных
 	 * @param string $file имя файла в базе данных
 	 */
	public function run($id, $file) {
		$fileCookie = $this->app->getCookie("file");

		if (Helper::checkPathCorrect($id, $file, $this->db)) {
			$row = $this->db->getById($id)->fetch();
			$data = json_decode($row["data"], true);
			$name = $data["filename"];
			$size = $data["filesize"];
			$time = $row["date"];
			$comment = $row["comment"];
			$mime = array_key_exists("mime_type", $data) ? $data["mime_type"] : null;
			$preview = preg_match("/image/ui", $mime) ? "/preview/".$id."/".$name : null;
			$args = array(
				"name" => $name,
				"size" => $size,
				"time" => $time,
			    "comment" => $comment,
			    "id" => $id,
			    "mime" => $mime,
			    "preview" => $preview,
			    "fileCookie" => $fileCookie
			);
		} else {
			$error = array("0" => "Такого файла не существует!");
			$args = array(
				"error" => $error,
				"fileCookie" => $fileCookie
			);
		}
		echo $this->twig->render('file.html', $args);
	}
}