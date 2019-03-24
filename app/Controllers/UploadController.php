<?php

namespace Controllers;

use Model\Validator;
use Model\Helper;
use Model\File;

/**
 * Загрузка файла
 */
class UploadController extends Controller
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
	 * @param string $token xsrf токен
	 */
	public function run($token)
	{
		$valid = new Validator;
		$errors = $valid->validateAll($this->app->request->post("comment"));
		if(empty($errors)) {
		    $tmpName = $_FILES["userfile"]["tmp_name"];
		    $name = mb_substr($_FILES["userfile"]["name"], -50, 50);
		    $newName = mb_substr($name, 0, 16) . ".txt";
		    $date = date("Y-m-d G:i:s");
		    $comment = $this->app->request->post("comment");
		    $this->db->insert($newName, $date);
		    $id = $this->db->getLastInsertId(); 
		    $path = "../upload/".date("Ymd")."/".$id;

		    File::upload($path, $tmpName, $name);
		    $mediaInfo = File::analyze($path, $name);
		    $jsonMediaInfo = json_encode($mediaInfo);
		    if(array_key_exists("mime_type", $mediaInfo)) {
			    if(preg_match('/image/ui', $mediaInfo["mime_type"])) {
			    	File::createThumbnail($path, $name, $id);
			    }
			}
		    rename($path . "/" . $name, $path . "/" . $newName);
		    $this->db->update($id, $newName, $date, $jsonMediaInfo, $comment);
		    $this->app->setCookie(
		    	"file",
		    	"/files/" . $id . "/" . $name,
				'1 week',
				'/',
				null,
				false,
				true
		    );
		    header("Location: /files/$id/$name");
	    } else {
			$toView = Helper::prepareErrors($errors);
			$fileCookie = $this->app->getCookie("file");
			$args = array(
				"errors" => $toView, 
				"token" => $token, 
				"fileCookie" => $fileCookie
			);
			echo $this->twig->render("index.html", $args);
		}
	}
}