<?php

/**
 * 
 */
class File
{
	
	function __construct(argument)
	{
	}

	public static function upload(string $filename) 
	{
		$tmpName = $_FILES[$filename]["tmp_name"];
	    $name = mb_substr($_FILES[$filename]["name"], -50, 50);
	    $newName = mb_substr($name, 0, 16) . ".txt";
	    $path = "../upload/".date("Ymd")."/".$id;
	    if(!is_dir($path)) {
	        mkdir($path,0777,true);
	    }
	    move_uploaded_file($tmpName, $path . "/" . $name);
	    return $newName;
	}

	public static function analyze(string $path)
	{
		$mediaInfo = $getId3->analyze($path . "/" . $name);
	    if(array_key_exists("error", $mediaInfo)) {
	    	$app->redirect("/");
	    }
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
	}

}