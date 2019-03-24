<?php

namespace Model;

use getID3;

/**
 * Работа с файлами
 */
class File
{
	/**
	 * Перемещает загруженный файл 
	 *
	 * @param string $path директория куда надо переместить загруженный файл
	 * @param string $tmpName временное имя загруженного файла
	 * @param string $name новое имя файла
	 */
	public static function upload($path, $tmpName, $name) 
	{
	    if(!is_dir($path)) {
	        mkdir($path,0777,true);
	    }
	    move_uploaded_file($tmpName, $path . "/" . $name);
	}

	/**
	 * Получает подробную информацию о файле
	 * и удаляет нечитаемые данные
	 *
	 * @param string $path путь к файлу 
	 * @param string $name имя файла
	 *
	 * @return array
	 */
	public static function analyze($path, $name)
	{
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
	    return $mediaInfo;
	}

	/**
	 * Создание превью для изображений
	 *
	 * @param string $path путь к изобрадению
	 * @param string $name имя изображения
	 * @param int $id айди изображения в базе данных
	 */
	public static function createThumbnail($path, $name, $id) 
	{
		mkdir("preview/".$id);
		copy($path."/".$name, "preview/".$id."/".$name);
	}

	/**
	 * Воспроизведение аудио\видео
	 *
	 * @param string $file полный путь к файлу
	 * @param string $mime mime тип файла
	 * @param int $size размер файла
	 */
	public static function watch($file, $mime, $size) 
	{
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

	/**
	 * Скачивание файла
	 *
	 * @param string $path полный путь к файлу
	 * @param string $filename новое имя файла
	 */
	public static function download($path, $filename)
	{
		if (file_exists($path)) {
		    if (ob_get_level()) {
		      ob_end_clean();
		    }
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename=' . $filename);
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($path));
		    readfile($path);
		    exit;
	    }
	}

}