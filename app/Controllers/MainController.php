<?php

namespace Controllers;

/**
 * Главная страница
 */
class MainController extends Controller
{
	/**
	 * @var Slim $app
	 * @var Twig_Environment $twig
	 */
	protected $app;
	protected $twig;

	function __construct($app, $twig)
	{
		$this->app = $app;
		$this->twig = $twig;
	}

	/**
	 * @param string $token xsrf токен
	 */
	public function run($token) {
		$fileCookie = $this->app->getCookie("file"); 
		$args = array(
			"token" => $token,
			"fileCookie" => $fileCookie
		);
	    echo $this->twig->render('index.html', $args);
	}
}