<?php

include_once('easyphpthumbnail.class.php');
class ceo_UploadFileXhr {
	function save($path){
		$input = fopen("php://input", "r");
		$fp = fopen($path, "w");
		while ($data = fread($input, 1024)){
			fwrite($fp,$data);
		}
		fclose($fp);
		fclose($input);			
	}
	function getName(){
		return $_GET['qqfile'];
	}
	function getSize(){
		$headers = apache_request_headers();
		return (int)$headers['Content-Length'];
	}
}

class ceo_UploadFileForm {	
  function save($path){
		move_uploaded_file($_FILES['qqfile']['tmp_name'], $path);
	}
	function getName(){
		return $_FILES['qqfile']['name'];
	}
	function getSize(){
		return $_FILES['qqfile']['size'];
	}
}

function ceo_handleUpload(){

	$maxFileSize = 100 * 1024 * 1024;
		
	if (isset($_GET['qqfile'])){
		$file = new ceo_UploadFileXhr();
	} elseif (isset($_FILES['qqfile'])){
		$file = new ceo_UploadFileForm();
	} else {
		return array(success=>false);
	}	

	$size = $file->getSize();
	if ($size == 0){
		return array(success=>false, error=>"File is empty.");
	}				
	if ($size > $maxFileSize){
		return array(success=>false, error=>"File is too large.");
	}
		
	$pathinfo = pathinfo($file->getName());
	$filename = $pathinfo['filename'];			
	$ext = $pathinfo['extension'];
	
	// if you limit file extensions on the client side,
	// you should check file extension here too
			
	while (file_exists(ceo_pluginfo('comic_path') .'/'. $filename . '.' . $ext)){
		$filename .= rand(10, 99);			
	}	
		
	$file->save(ceo_pluginfo('comic_path') .'/'. $filename . '.' . $ext);
	
	if (preg_match("/jpg|jpeg|gif|png/i", $ext)){
		//generate medium comic image
		$thumb = new easyphpthumbnail;
		$thumb -> Thumbwidth            = ceo_pluginfo('medium_comic_width');
		$thumb -> Thumblocation         = ceo_pluginfo('thumbnail_medium_path') .'/';
		$thumb -> Thumbprefix           = '';
		$thumb -> Createthumb(ceo_pluginfo('comic_path') .'/'. $filename . '.' . $ext,'file');

		//generate small comic image
		$thumb -> Thumbwidth            = ceo_pluginfo('small_comic_width');
		$thumb -> Thumblocation         = ceo_pluginfo('thumbnail_small_path') .'/';
		$thumb -> Thumbprefix           = '';
		$thumb -> Createthumb(ceo_pluginfo('comic_path') .'/'. $filename . '.' . $ext,'file');
	}
	add_post_meta($_GET[post_id], 'comic', $filename . '.' . $ext, false);
	
	return array(success=>true);
}

