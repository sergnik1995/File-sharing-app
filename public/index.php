<?php

error_reporting(-1);
mb_internal_encoding('utf8');

require '../vendor/autoload.php';
require '../app/autoload.php';

define("MAX_FILE_SIZE", 100*1024*1024);

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    'cache' => 'compilation_cache',
    'auto_reload' => true
));
$db = new \Model\Database();
$app = new \Slim\Slim();

if(!array_key_exists('token', $_COOKIE)) {
	$token = uniqid('', true);
} else {
	$token = $_COOKIE['token'];
}
$app->setCookie(
	'token',
	$token,
	'1 year',
	'/',
	null,
	false,
	true
);

$app->get('/', function () use ($app, $twig, $token) {
	$controller = new Controllers\MainController($app, $twig);

    $controller->run($token);
});

$app->get('/files/:id/:file', function($id, $file) use ($app, $twig, $db) {
	$controller = new Controllers\DownloadPageController($app, $twig, $db);

	$controller->run($id, $file);
});

$app->get("/watch/:id/:name", function($id, $name) use ($app, $db) {
	$controller = new Controllers\WatchController($app, $db);

	$controller->run($id, $name);
});

$app->get("/last", function() use ($app, $twig, $db) {
	$controller = new Controllers\LastUploadsController($app, $twig, $db);

	$controller->run();
});

$app->post('/download/:id/:file', function($id, $file) use ($app, $twig, $db) {
	$controller = new Controllers\DownloadController($app, $twig, $db);

	$controller->run($id, $file);
});

$app->post('/', 'checkXsrf', function() use ($app, $twig, $db, $token) {
	$controller = new Controllers\UploadController($app, $twig, $db);
    
	$controller->run($token);
});
$app->run();

function checkXsrf() {
	$app = \Slim\Slim::getInstance();

	if(!array_key_exists('token', $_POST) OR $app->request->post("token") == '' OR 
	                    $app->request->post("token") != $app->getCookie("token")) {
		$xsrf = array("xsrf" => "Произошла непредвиденная ошибка, пожалуйста, переотправьте файл заново!");
	    $toView = $xsrf;
	    $fileCookie = $app->getCookie("file");
	    $args = array(
	    	"errors" => $toView,
	    	"token" => $token,
	    	"fileCookie" => $fileCookie
	    );
	    echo $twig->render("index.html", $args);
	    $app->run();
	}
}