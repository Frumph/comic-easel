<?php
/**
 * Runs one plugin entry point in a fresh PHP process. See CE_TestCase::runIsolated().
 *
 * Some plugin code cannot run inside the PHPUnit process: admin pages that do their work, and
 * declare their helper functions, as they are included (ceo-import.php), and the PayPal IPN
 * handler, every path out of which ends in exit(). This loads the suite's stubs, applies the
 * spec it reads as JSON on standard input, runs the entry point, and prints a JSON report from
 * a shutdown function, which PHP still runs after exit().
 *
 * Spec keys: state   CE_Test_State properties to set ('posts' entries become objects)
 *            post, get, request   superglobal contents ($_REQUEST defaults to $_GET + $_POST)
 *            load    plugin files to require first
 *            include plugin file to include, or call  function to call
 */

require dirname( __DIR__ ) . '/vendor/autoload.php';
require __DIR__ . '/bootstrap.php';

$ce_spec = json_decode( (string) stream_get_contents( STDIN ), true );

CE_Test_State::reset();
foreach ( isset( $ce_spec['state'] ) ? $ce_spec['state'] : array() as $ce_property => $ce_value ) {
	if ( 'posts' === $ce_property ) {
		$ce_value = array_map(
			function ( $post ) {
				return (object) $post;
			},
			$ce_value
		);
	}
	CE_Test_State::${$ce_property} = $ce_value;
}
$_POST    = isset( $ce_spec['post'] ) ? $ce_spec['post'] : array();
$_GET     = isset( $ce_spec['get'] ) ? $ce_spec['get'] : array();
$_REQUEST = isset( $ce_spec['request'] ) ? $ce_spec['request'] : array_merge( $_GET, $_POST );

$ce_report = array(
	'exited' => true,
	'died'   => null,
);
ob_start();
register_shutdown_function(
	function () use ( &$ce_report ) {
		$ce_report['output'] = '';
		while ( ob_get_level() ) {
			$ce_report['output'] = ob_get_clean() . $ce_report['output'];
		}
		$ce_report['options']     = CE_Test_State::$options;
		$ce_report['post_meta']   = CE_Test_State::$post_meta;
		$ce_report['meta_writes'] = CE_Test_State::$meta_writes;
		$ce_report['mail_calls']  = CE_Test_State::$mail_calls;
		echo json_encode( $ce_report );
	}
);

foreach ( isset( $ce_spec['load'] ) ? $ce_spec['load'] : array() as $ce_file ) {
	require_once CE_PLUGIN_DIR . '/' . $ce_file;
}
try {
	if ( isset( $ce_spec['include'] ) ) {
		include CE_PLUGIN_DIR . '/' . $ce_spec['include'];
	}
	if ( isset( $ce_spec['call'] ) ) {
		call_user_func( $ce_spec['call'] );
	}
	$ce_report['exited'] = false;
} catch ( CE_Test_Die $ce_die ) {
	$ce_report['exited'] = false;
	$ce_report['died']   = $ce_die->getMessage();
}
