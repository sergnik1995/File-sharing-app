<?php

error_reporting(-1);
mb_internal_encoding('utf8');

require '../vendor/autoload.php';
require '../app/autoload.php';

//Максимальный размер файла 
define("MAX_FILE_SIZE", 100*1024*1024);

//Местонахождение шаблонов для использования в twig
$loader = new Twig_Loader_Filesystem('templates');
//Инициализация twig
$twig = new Twig_Environment($loader, array(
    'cache' => 'compilation_cache',         //местонахождение кэша twig
    'auto_reload' => true                   //автоматическая пересоздание кеша
));
//Инициализация базы данных
$db = new Database;
//Инициализация slim
$app = new \Slim\Slim();

//Создание токена для защиты от xsrf и добавление его в куки
if(!array_key_exists('token', $_COOKIE)) {
	$token = uniqid('', true);
}
else {
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

//Главная страница
$app->get('/', function () use ($app, $twig, $token) {
	//Добавление куки с токеном в шаблон
	$fileCookie = $app->getCookie("file"); 
	$args = array(
		"token" => $token,
		"fileCookie" => $fileCookie
	);
    echo $twig->render('index.html', $args);
});

//Страница для скачивания файла
$app->get('/files/:id/:file', function($id, $file) use ($app, $twig, $db) {
	$fileCookie = $app->getCookie("file");
	//Проверка правильности введеного пути к файлу
	if (Helper::isRouterPathCorrect($id, $file, $db)) {
		$row = $db->getById($id)->fetch();
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
	echo $twig->render('file.html', $args);
});

//Страница для просмотра видео или прослушивания аудио
$app->get("/watch/:id/:name", function($id, $name) use ($app, $db) {
	if(Helper::isRouterPathCorrect($id, $name, $db)) {
		$row = $db->getById($id)->fetch();
		$data = json_decode($row["data"], true);
		$mime = $data["mime_type"];
		$size = $data["filesize"];
		$file = $data["filepath"] ."/". $row["name"];
		$fp = fopen($file, 'rb');
		$length = $size;
		$start  = 0;
		$end    = $size - 1;
		header('Content-type: '. $mime);
		header("Accept-Ranges: 0-$length");
		if (ob_get_level()) {
			      ob_end_clean();
			    }
		if (isset($_SERVER['HTTP_RANGE'])) {

		    $c_start = $start;
		    $c_end   = $end;

		    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
		    if (strpos($range, ',') !== false) {
		        header('HTTP/1.1 416 Requested Range Not Satisfiable');
		        header("Content-Range: bytes $start-$end/$size");
		        exit;
		    }
		    if ($range == '-') {
		        $c_start = $size - substr($range, 1);
		    }else{
		        $range  = explode('-', $range);
		        $c_start = $range[0];
		        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
		    }
		    $c_end = ($c_end > $end) ? $end : $c_end;
		    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
		        header('HTTP/1.1 416 Requested Range Not Satisfiable');
		        header("Content-Range: bytes $start-$end/$size");
		        exit;
		    }
		    $start  = $c_start;
		    $end    = $c_end;
		    $length = $end - $start + 1;
		    fseek($fp, $start);
		    header('HTTP/1.1 206 Partial Content');
		}
		header("Content-Range: bytes $start-$end/$size");
		header("Content-Length: ".$length);

		$buffer = 1024 * 8;
		while(!feof($fp) && ($p = ftell($fp)) <= $end) {

		    if ($p + $buffer > $end) {
		        $buffer = $end - $p + 1;
		    }
		    set_time_limit(0);
		    echo fread($fp, $buffer);
		    flush();
		}

		fclose($fp);
		exit();
	}
});

//Страница с последними 100 загруженными файлами
$app->get("/last", function() use ($app, $twig, $db) {
	$table = $db->getLastUploads();
	$table = Helper::prepareTable($table);
	$fileCookie = $app->getCookie("file");
	$args = array(
		"fileCookie" => $fileCookie,
		"table" => $table
	);
	echo $twig->render('last.html', $args);
});

//Скачивание файлов с сервера
$app->post('/download/:id/:file', function($id, $file) use ($app, $twig, $db) {
	if(Helper::isRouterPathCorrect($id, $file, $db)) {
        $row = $db->getById($id)->fetch();
		$data = json_decode($row['data'], true);
		$file = $data["filepath"] ."/". $row["name"];
		if (file_exists($file)) {
		    if (ob_get_level()) {
		      ob_end_clean();
		    }
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename=' . $data["filename"]);
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($file));
		    readfile($file);
		    exit;
	    }
	} else {
		$error = array("0" => "Такого файла не существует!");
		$fileCookie = $app->getCookie("file");
		$args = array(
			"error" => $error,
			"fileCookie" => $fileCookie
		);
		echo $twig->render('file.html', $args);
	}
});

//Загрузка файлов на сервер
$app->post('/', function() use ($app, $twig, $db, $token) {
	$valid = new Validator;
	$errors = $valid->validateAll($app->request->post("comment"));
	//Проверка на xsrf
	if(!array_key_exists('token', $_POST) OR $app->request->post("token") == '' OR $app->request->post("token") != $app->getCookie("token")) {
		$xsrf = array("xsrf" => "Произошла непредвиденная ошибка, пожалуйста, переотправьте файл заново!");
	} else { 
		if(empty($errors)) {
		    $tmpName = $_FILES["userfile"]["tmp_name"];
		    $name = mb_substr($_FILES["userfile"]["name"], -50, 50);
		    $newName = mb_substr($name, 0, 16) . ".txt";
		    $date = date("Y-m-d G:i:s");
		    $comment = $app->request->post("comment");
		    $db->insert($newName, $date);
		    $id = $db->getLastInsertId(); 
		    $path = "../upload/".date("Ymd")."/".$id;
		    if(!is_dir($path)) {
		        mkdir($path,0777,true);
		    }
		    move_uploaded_file($tmpName, $path . "/" . $name);
		    //Получение подробно информации о файле
		    $getId3 = new getID3;
		    $getId3->encoding = 'UTF-8';
		    $mediaInfo = $getId3->analyze($path . "/" . $name);
		    array_walk_recursive($mediaInfo, function(&$value) {
		    	if (!mb_check_encoding($value, "UTF-8")) {
		    		mb_convert_encoding($value, "UTF-8");
		    		if (!mb_check_encoding($value, "UTF-8")) {
		    			$value = "unreadable";
		    		}
		    	}
		    });
		    $jsonMediaInfo = json_encode($mediaInfo);
		    if(array_key_exists("mime_type", $mediaInfo)) {
			    if(preg_match('/image/ui', $mediaInfo["mime_type"])) {
			    	mkdir("preview/".$id);
			    	copy($path."/".$name, "preview/".$id."/".$name);
			    }
			}
		    rename($path . "/" . $name, $path . "/" . $newName);
		    $db->update($id, $newName, $date, $jsonMediaInfo, $comment);
		    $app->setCookie(
		    	"file",
		    	"/files/" . $id . "/" . $name,
				'1 week',
				'/',
				null,
				false,
				true
		    );
		    $app->redirect("/files/" . $id . "/" . $name);
	    }
	}
	$toView = isset($xsrf) ? Helper::prepareErrors($errors) + $xsrf : Helper::prepareErrors($errors);
	$fileCookie = $app->getCookie("file");
	$args = array(
		"errors" => $toView, 
		"token" => $token, 
		"fileCookie" => $fileCookie
	);
	echo $twig->render("index.html", $args);
});
$app->run();