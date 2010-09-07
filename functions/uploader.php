<?php

require_once( "../../../../wp-load.php" );
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
	
	//generate post for comic
	//need to add more data for the post from the uploader form here.
	$post = array(
		'post_content' => $_REQUEST['content'], //The full text of the post.
		'post_title' => $_REQUEST['title'], //The title of your post.
		'post_type' => 'comic',
		'post_author' => $_REQUEST['userid'],
		'post_status' => 'publish',
		'post_date' => $_REQUEST['date'] .' '. $_REQUEST['time'],
		'tags_input' => $_REQUEST['tags']
	);
	
	$newpost = wp_insert_post( $post );
	$chapter = get_term_by('id', $_REQUEST['chapter'], 'chapters');
	wp_set_object_terms( $newpost, $chapter->slug , 'chapters',  false);
	add_post_meta($newpost, 'comic', $filename . '.' . $ext);
	
	return array(success=>true);
}


$result = ceo_handleUpload();

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

