<?php
// ajax stuff
// if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
// add_action( 'wp_ajax_nopriv_ceo_comic_upload', 'myajax_submit' );
add_action( 'wp_ajax_ceo_uploader', 'ceo_comic_upload' );

// Ajax function to upload and attach files. uploader.php handles uploads, thumb generation, and attaching to post
// This ajax function will only work itthe user has edit privlages on the site and is currentl;y logged in.
function ceo_comic_upload() {
	require_once('functions/uploader.php');
	$result = ceo_handleUpload();
	// to pass data through iframe you will need to encode all html tags
	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
	exit;
}

add_action( 'wp_ajax_ceo_thumb_update', 'ceo_comic_thumb_update' );

// Ajax function to update thumb list on comic edit page when new files are attached to the post by the uploader or the selectbox attacher.
//This ajax function will only work itthe user has edit privlages on the site and is currentl;y logged in.
function ceo_comic_thumb_update() {
	$post = get_post( $_GET['post_id']);
	echo ceo_display_comic_thumbnail_editor('small', $post , false, 198); 
	exit;
}

add_action( 'wp_ajax_ceo_comic_add', 'ceo_comic_add' );
// Ajax function to attach files submited in select box. This ajax function will only work itthe user has edit privlages on the site and is currentl;y logged in.
function ceo_comic_add() {
	$comicfiles = $_GET['comicfile'];
	if (!($comicfiles == '')){
		$pieces = explode(",", $comicfiles);
		foreach ($pieces as $comicfile){
			add_post_meta($_GET['post_id'], 'comic', $comicfile, false);
		}
	}
	return 'success';
	exit;
}
add_action( 'wp_ajax_ceo_comic_remove', 'ceo_comic_remove' );
// Ajax function to remove files when Remove button is clicked. This ajax function will only work itthe user has edit privlages on the site and is currentl;y logged in.
function ceo_comic_remove() {
	delete_post_meta($_GET['post_id'], 'comic', $_GET['comicfile']);
	return 'success';
	exit;
}
?>