<?php

namespace Controllers;

use Model\Helper;
use Model\File;

/**
 * Скачивание файла
 */
class DownloadController extends Controller
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
	public function run($id, $file)
	{
		if(Helper::checkPathCorrect($id, $file, $this->db)) {
	        $row = $this->db->getById($id)->fetch();
			$data = json_decode($row['data'], true);
			$file = $data["filepath"] ."/". $row["name"];
			$filename = $data["filename"];
			
			File::download($file, $filename);
		} else {
			$error = array("0" => "Такого файла не существует!");
			$fileCookie = $this->app->getCookie("file");
			$args = array(
				"error" => $error,
				"fileCookie" => $fileCookie
			);
			echo $this->twig->render('file.html', $args);
		}
	}
}