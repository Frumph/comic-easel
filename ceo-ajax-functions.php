<? php
// if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
// add_action( 'wp_ajax_nopriv_ceo_comic_upload', 'myajax_submit' );
add_action( 'wp_ajax_comic_upload', 'ceo_comic_upload' );

function ceo_comic_upload() {
	// generate the response
	$response = '{success:true}';

	// response output
	echo $response;

	// IMPORTANT: don't forget to "exit"
	exit;
}

?>