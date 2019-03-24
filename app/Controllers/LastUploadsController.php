<?php

namespace Controllers;

use Model\Helper;

/**
 * Страница последних 100 загрузок на сервер
 */
class LastUploadsController extends Controller
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

	public function run()
	{
		$table = $this->db->getLastUploads();
		$table = Helper::prepareTable($table);
		$fileCookie = $this->app->getCookie("file");
		$args = array(
			"fileCookie" => $fileCookie,
			"table" => $table
		);
		echo $this->twig->render('last.html', $args);
	}
}