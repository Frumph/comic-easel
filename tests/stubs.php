<?php
/**
 * WordPress stubs for the Comic Easel unit tests.
 *
 * These tests deliberately do NOT boot WordPress. The functions under test depend only on
 * their arguments plus a small set of WordPress helpers, so stubbing those helpers gives
 * fast, dependency-free tests of the logic that matters.
 *
 * Three of these stubs have to be faithful or the tests they support are worthless:
 *
 *  - esc_html()/esc_attr() call _wp_specialchars() with $double_encode = FALSE, so they
 *    leave text that already looks like an entity alone. esc_textarea() double-encodes.
 *    Most of the escaping tests probe exactly that difference, so a naive
 *    htmlspecialchars() stub would give the wrong answer and the tests would lie.
 *  - wp_kses_post() is a RECORDER returning a sentinel, not a reimplementation. The
 *    assertions that matter are "was filtering applied", not "what did it strip".
 *  - apply_filters() passes through by default but is overridable, because the filters
 *    are the seams the PayPal tests hang on.
 *
 * State is held in CE_Test_State and must be reset between tests (the base test case in
 * tests/CE_TestCase.php does that automatically).
 */

class CE_Test_State {
	/** @var array<string,mixed> option name => value */
	public static $options = array();
	/** @var array<string,mixed> "postid:metakey" => value */
	public static $post_meta = array();
	/** @var array<int,object> post ID => lightweight WP_Post stand-in */
	public static $posts = array();
	/** @var array<string,array<int,object>> taxonomy => term objects */
	public static $terms = array();
	/** @var array<string,bool> "userid:cap" => bool */
	public static $user_caps = array();
	/** @var array<string,bool> capability => bool for current_user_can() */
	public static $current_user_caps = array();
	/** @var array<string,string> nonce action => accepted nonce */
	public static $valid_nonces = array();
	/** @var array<string,mixed> filter tag => value to return */
	public static $filters = array();
	/** @var array<string,string> msgid => translation */
	public static $translations = array();
	/** @var array<string,mixed> ceo_pluginfo key => value */
	public static $pluginfo = array();
	/** @var array<int,string> post ID => filtered post title */
	public static $titles = array();
	/** @var mixed value returned by wp_remote_post() */
	public static $http_response = array();
	/** @var array recorded wp_remote_post() calls */
	public static $http_requests = array();
	/** @var array recorded wp_kses_post() inputs */
	public static $kses_calls = array();
	/** @var array recorded wp_kses() calls, as array( input, allowlist ) */
	public static $kses_allowlist_calls = array();
	/** @var array recorded wp_mail() calls */
	public static $mail_calls = array();
	/** @var array recorded update_post_meta() calls */
	public static $meta_writes = array();
	/** @var array<string,array> handle => arguments of the wp_enqueue_script() call */
	public static $scripts = array();
	/** @var array<string,array> handle => arguments of the wp_enqueue_style() call */
	public static $styles = array();
	/** @var array<string,array> handle => arguments of the wp_register_style() call */
	public static $registered_styles = array();
	/** @var object|null what get_current_screen() returns */
	public static $screen = null;
	/** @var array recorded wp_add_inline_script() calls */
	public static $inline_scripts = array();
	/** @var array<int,object> posts that get_posts() and every WP_Query return, in order */
	public static $found_posts = array();
	/** @var array<string,bool> conditional tag without its is_ prefix => result, e.g. 'home' */
	public static $is = array();
	/** @var array<string,mixed> plugin navigation helper name => the permalink it returns */
	public static $nav_links = array();

	public static function reset() {
		self::$options = array( 'blog_charset' => 'UTF-8' );
		self::$post_meta = array();
		self::$posts = array();
		self::$terms = array();
		self::$user_caps = array();
		self::$current_user_caps = array();
		self::$valid_nonces = array();
		self::$filters = array();
		self::$translations = array();
		self::$pluginfo = array();
		self::$titles = array();
		self::$http_response = array();
		self::$http_requests = array();
		self::$kses_calls = array();
		self::$kses_allowlist_calls = array();
		self::$mail_calls = array();
		self::$meta_writes = array();
		self::$scripts = array();
		self::$styles = array();
		self::$registered_styles = array();
		self::$screen = null;
		self::$inline_scripts = array();
		self::$found_posts = array();
		self::$is = array();
		self::$nav_links = array();
	}
}

/* -------------------------------------------------------------------------- *
 * Escaping — must mirror WordPress's double_encode semantics. See header.
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8', false );
	}
}

if ( ! function_exists( 'esc_textarea' ) ) {
	function esc_textarea( $text ) {
		// Note the missing 4th argument: htmlspecialchars() defaults to double_encode = true,
		// which is what WordPress's esc_textarea() does too.
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_js' ) ) {
	function esc_js( $text ) {
		return addslashes( (string) $text );
	}
}

/**
 * Approximation of esc_url(). The behaviour the tests rely on is that a disallowed scheme
 * yields an empty string and that ampersands are entity-encoded.
 */
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url, $protocols = null ) {
		$url = (string) $url;
		if ( '' === $url ) {
			return '';
		}
		$allowed = null === $protocols
			? array( 'http', 'https', 'mailto', 'ftp', 'ftps', 'news', 'irc', 'tel' )
			: $protocols;
		if ( preg_match( '#^([a-z0-9+.-]+):#i', $url, $m ) && ! in_array( strtolower( $m[1] ), $allowed, true ) ) {
			return '';
		}
		$url = str_replace( array( '"', "'", '<', '>' ), '', $url );
		return str_replace( '&amp;', '&#038;', htmlspecialchars( $url, ENT_NOQUOTES, 'UTF-8', false ) );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url, $protocols = null ) {
		$url = (string) $url;
		if ( '' === $url ) {
			return '';
		}
		$allowed = null === $protocols
			? array( 'http', 'https', 'mailto', 'ftp', 'ftps', 'news', 'irc', 'tel' )
			: $protocols;
		if ( preg_match( '#^([a-z0-9+.-]+):#i', $url, $m ) && ! in_array( strtolower( $m[1] ), $allowed, true ) ) {
			return '';
		}
		return $url;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		// Like core, drop <script> and <style> elements whole, not just their tags.
		$str = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $str );
		$str = strip_tags( $str );
		$str = preg_replace( '/[\r\n\t ]+/', ' ', $str );
		return trim( str_replace( "\0", '', $str ) );
	}
}

/**
 * Core's algorithm without its filters: keep the characters an address may contain and drop
 * anything that cannot be one. The IPN tests depend on it returning '' for a non-address.
 */
if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		$email = (string) $email;
		if ( strlen( $email ) < 6 || false === strpos( $email, '@', 1 ) ) {
			return '';
		}
		list( $local, $domain ) = explode( '@', $email, 2 );
		$local = preg_replace( '/[^a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]/', '', $local );
		$domain = trim( preg_replace( '/\.{2,}/', '', $domain ), " \t\n\r\0\x0B." );
		if ( '' === $local || '' === $domain ) {
			return '';
		}
		$subs = array();
		foreach ( explode( '.', $domain ) as $sub ) {
			$sub = preg_replace( '/[^a-z0-9-]+/i', '', trim( $sub, " \t\n\r\0\x0B-" ) );
			if ( '' !== $sub ) {
				$subs[] = $sub;
			}
		}
		return count( $subs ) < 2 ? '' : $local . '@' . implode( '.', $subs );
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class, $fallback = '' ) {
		$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
		return '' === $sanitized ? $fallback : $sanitized;
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

/**
 * Recorder, not a reimplementation. Returns a sentinel so a test can assert that filtering
 * was applied without depending on what kses would actually have stripped.
 */
define( 'CE_KSES_SENTINEL', 'KSES:' );

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		CE_Test_State::$kses_calls[] = $data;
		return CE_KSES_SENTINEL . $data;
	}
}

/**
 * Same idea for wp_kses() with an explicit allowlist: records the input together with the
 * allowlist it was held to, so a test can assert which tags would survive.
 */
if ( ! function_exists( 'wp_kses' ) ) {
	function wp_kses( $data, $allowed_html, $allowed_protocols = array() ) {
		CE_Test_State::$kses_allowlist_calls[] = array( $data, $allowed_html );
		return CE_KSES_SENTINEL . $data;
	}
}

/**
 * A representative slice of core's 'post' context, global attributes included. Enough for
 * code that extends the post allowlist rather than replacing it.
 */
/**
 * Core's is addslashes( wp_kses( stripslashes( $data ), 'strip' ) ): every tag removed, and the
 * result slashed again, which the Config page stores as it is.
 */
if ( ! function_exists( 'wp_filter_nohtml_kses' ) ) {
	function wp_filter_nohtml_kses( $data ) {
		return addslashes( strip_tags( stripslashes( (string) $data ) ) );
	}
}

if ( ! function_exists( 'wp_kses_allowed_html' ) ) {
	function wp_kses_allowed_html( $context = '' ) {
		$global = array( 'class' => true, 'id' => true, 'style' => true, 'title' => true, 'role' => true );
		return array(
			'a'      => array_merge( $global, array( 'href' => true, 'rel' => true, 'target' => true ) ),
			'div'    => $global,
			'span'   => $global,
			'em'     => $global,
			'strong' => $global,
			'img'    => array_merge( $global, array( 'alt' => true, 'height' => true, 'loading' => true, 'src' => true, 'width' => true ) ),
		);
	}
}

/* -------------------------------------------------------------------------- *
 * Options, meta, capabilities, filters — all test-driven.
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		return array_key_exists( $name, CE_Test_State::$options ) ? CE_Test_State::$options[ $name ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $name, $value, $autoload = null ) {
		CE_Test_State::$options[ $name ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $name ) {
		unset( CE_Test_State::$options[ $name ] );
		return true;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $post_id, $key = '', $single = false ) {
		$k = $post_id . ':' . $key;
		if ( ! array_key_exists( $k, CE_Test_State::$post_meta ) ) {
			return $single ? '' : array();
		}
		return CE_Test_State::$post_meta[ $k ];
	}
}

if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( $post_id, $key, $value, $prev = '' ) {
		CE_Test_State::$post_meta[ $post_id . ':' . $key ] = $value;
		CE_Test_State::$meta_writes[] = array( $post_id, $key, $value );
		return true;
	}
}

if ( ! function_exists( 'add_post_meta' ) ) {
	function add_post_meta( $post_id, $key, $value, $unique = false ) {
		// WordPress refuses a unique add when any row already holds the key, including a row
		// whose value is the empty string. Modelling the refusal is what lets a test tell an
		// absent row apart from an empty one, since get_post_meta() reports '' for both.
		if ( $unique && array_key_exists( $post_id . ':' . $key, CE_Test_State::$post_meta ) ) {
			return false;
		}
		return update_post_meta( $post_id, $key, $value );
	}
}

if ( ! function_exists( 'delete_post_meta' ) ) {
	function delete_post_meta( $post_id, $key, $value = '' ) {
		unset( CE_Test_State::$post_meta[ $post_id . ':' . $key ] );
		return true;
	}
}

if ( ! function_exists( 'user_can' ) ) {
	function user_can( $user, $capability, ...$args ) {
		$id = is_object( $user ) ? $user->ID : (int) $user;
		return ! empty( CE_Test_State::$user_caps[ $id . ':' . $capability ] );
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability, ...$args ) {
		return ! empty( CE_Test_State::$current_user_caps[ $capability ] );
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action = -1 ) {
		return isset( CE_Test_State::$valid_nonces[ $action ] )
			&& hash_equals( CE_Test_State::$valid_nonces[ $action ], (string) $nonce );
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value = null, ...$args ) {
		return array_key_exists( $tag, CE_Test_State::$filters ) ? CE_Test_State::$filters[ $tag ] : $value;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return array_key_exists( $text, CE_Test_State::$translations ) ? CE_Test_State::$translations[ $text ] : $text;
	}
}

if ( ! function_exists( '_e' ) ) {
	function _e( $text, $domain = 'default' ) {
		echo __( $text, $domain );
	}
}

if ( ! function_exists( '_x' ) ) {
	function _x( $text, $context, $domain = 'default' ) {
		return __( $text, $domain );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return esc_html( __( $text, $domain ) );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		return esc_attr( __( $text, $domain ) );
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e( $text, $domain = 'default' ) {
		echo esc_html__( $text, $domain );
	}
}

if ( ! function_exists( 'esc_attr_e' ) ) {
	function esc_attr_e( $text, $domain = 'default' ) {
		echo esc_attr__( $text, $domain );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $text, $remove_breaks = false ) {
		$text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $text );
		$text = strip_tags( $text );
		if ( $remove_breaks ) {
			$text = preg_replace( '/[\r\n\t ]+/', ' ', $text );
		}
		return trim( $text );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.test' . $path;
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( ...$args ) {
		if ( is_array( $args[0] ) ) {
			$params = $args[0];
			$url    = isset( $args[1] ) ? $args[1] : '';
		} else {
			$params = array( $args[0] => $args[1] );
			$url    = isset( $args[2] ) ? $args[2] : '';
		}
		$sep = false === strpos( $url, '?' ) ? '?' : '&';
		return $url . $sep . http_build_query( $params );
	}
}

/* -------------------------------------------------------------------------- *
 * HTTP — wp_remote_post() is test-driven and records what it was sent, which is how the
 * IPN tests assert the notification is echoed back verbatim.
 * -------------------------------------------------------------------------- */

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $message;
		public function __construct( $code = '', $message = '' ) {
			$this->message = $message;
		}
		public function get_error_message() {
			return $this->message;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'wp_remote_post' ) ) {
	function wp_remote_post( $url, $args = array() ) {
		CE_Test_State::$http_requests[] = array( 'url' => $url, 'args' => $args );
		return CE_Test_State::$http_response;
	}
}

if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
	function wp_remote_retrieve_response_code( $response ) {
		return isset( $response['response']['code'] ) ? $response['response']['code'] : '';
	}
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
	function wp_remote_retrieve_body( $response ) {
		return isset( $response['body'] ) ? $response['body'] : '';
	}
}

if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		CE_Test_State::$mail_calls[] = compact( 'to', 'subject', 'message' );
		return true;
	}
}

/* -------------------------------------------------------------------------- *
 * Admin pages. wp_die() never returns in WordPress; here it throws, so a test (or
 * tests/isolated-runner.php) can see that a page stopped, and with what message.
 * -------------------------------------------------------------------------- */

class CE_Test_Die extends Exception {}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '', $title = '', $args = array() ) {
		throw new CE_Test_Die( (string) $message );
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $display = true ) {
		$field = '<input type="hidden" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="nonce:' . esc_attr( $action ) . '" />';
		if ( $display ) {
			echo $field;
		}
		return $field;
	}
}

/* -------------------------------------------------------------------------- *
 * Hook registration — no-ops, so plugin files can simply be require'd.
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'add_action' ) ) {
	function add_action( ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'remove_action' ) ) {
	function remove_action( ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'remove_filter' ) ) {
	function remove_filter( ...$args ) {
		return true;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( ...$args ) {
		return true;
	}
}

/* -------------------------------------------------------------------------- *
 * Script and style queues — recorders, so a test can assert what was enqueued and what
 * was attached to it without a WP_Scripts instance.
 * -------------------------------------------------------------------------- */

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $args = array() ) {
		CE_Test_State::$scripts[ $handle ] = compact( 'src', 'deps', 'ver', 'args' );
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		CE_Test_State::$styles[ $handle ] = compact( 'src', 'deps', 'ver', 'media' );
	}
}

if ( ! function_exists( 'wp_register_style' ) ) {
	function wp_register_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
		CE_Test_State::$registered_styles[ $handle ] = compact( 'src', 'deps', 'ver', 'media' );
		return true;
	}
}

if ( ! function_exists( 'get_current_screen' ) ) {
	function get_current_screen() {
		return CE_Test_State::$screen;
	}
}

if ( ! function_exists( 'wp_add_inline_script' ) ) {
	function wp_add_inline_script( $handle, $data, $position = 'after' ) {
		CE_Test_State::$inline_scripts[] = compact( 'handle', 'data', 'position' );
		return true;
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'add_shortcode' ) ) {
	function add_shortcode( $tag, $callback ) {
		return true;
	}
}

if ( ! function_exists( 'shortcode_atts' ) ) {
	function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
		$atts = (array) $atts;
		$out  = array();
		foreach ( $pairs as $name => $default ) {
			$out[ $name ] = array_key_exists( $name, $atts ) ? $atts[ $name ] : $default;
		}
		return $out;
	}
}

if ( ! function_exists( 'get_posts' ) ) {
	function get_posts( $args = array() ) {
		// As in core, a WP_Query does the work, so WP_Query::$last_query records the arguments.
		$query = new WP_Query();
		return $query->query( $args );
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post = null ) {
		$post_id = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : (int) $post;
		return array_key_exists( $post_id, CE_Test_State::$posts ) ? CE_Test_State::$posts[ $post_id ] : null;
	}
}

/**
 * Every taxonomy gets core's default capabilities, which is what the plugin registers its own
 * with (it passes none).
 */
if ( ! function_exists( 'get_taxonomy' ) ) {
	function get_taxonomy( $taxonomy ) {
		return (object) array(
			'name' => $taxonomy,
			'cap'  => (object) array(
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_posts',
			),
		);
	}
}

if ( ! function_exists( 'get_post_type_object' ) ) {
	function get_post_type_object( $post_type ) {
		return (object) array(
			'cap' => (object) array( 'edit_post' => 'edit_post' ),
		);
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $post = 0 ) {
		return 'https://example.test/?p=' . ( is_object( $post ) ? $post->ID : (int) $post );
	}
}

if ( ! function_exists( 'get_term_link' ) ) {
	function get_term_link( $term, $taxonomy = '' ) {
		return 'https://example.test/' . $taxonomy . '/' . $term;
	}
}

if ( ! function_exists( 'get_term_by' ) ) {
	function get_term_by( $field, $value, $taxonomy = '', $output = 'OBJECT', $filter = 'raw' ) {
		return false;
	}
}

if ( ! function_exists( 'wp_get_post_terms' ) ) {
	function wp_get_post_terms( $post_id, $taxonomy, $args = array() ) {
		return array_key_exists( $taxonomy, CE_Test_State::$terms ) ? CE_Test_State::$terms[ $taxonomy ] : array();
	}
}

if ( ! function_exists( 'wptexturize' ) ) {
	function wptexturize( $text ) {
		return $text;
	}
}

if ( ! function_exists( 'is_feed' ) ) {
	function is_feed() {
		return false;
	}
}

if ( ! function_exists( 'is_archive' ) ) {
	function is_archive() {
		return false;
	}
}

if ( ! function_exists( 'is_search' ) ) {
	function is_search() {
		return false;
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	function get_the_title( $post = 0 ) {
		$post_id = is_object( $post ) && isset( $post->ID ) ? (int) $post->ID : (int) $post;
		return array_key_exists( $post_id, CE_Test_State::$titles ) ? CE_Test_State::$titles[ $post_id ] : 'Title';
	}
}

if ( ! function_exists( 'get_the_time' ) ) {
	function get_the_time( $format = '', $post = null ) {
		return '2020-01-01';
	}
}

if ( ! function_exists( 'get_post_time' ) ) {
	function get_post_time( $format = 'U', $gmt = false, $post = null, $translate = false ) {
		return '2020';
	}
}

if ( ! function_exists( 'wp_cache_get' ) ) {
	function wp_cache_get( $key, $group = '' ) {
		return false;
	}
}

if ( ! function_exists( 'wp_cache_set' ) ) {
	function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
		return true;
	}
}

if ( ! function_exists( 'wp_cache_delete' ) ) {
	function wp_cache_delete( $key, $group = '' ) {
		return true;
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type, $gmt = 0 ) {
		if ( 'timestamp' === $type || 'U' === $type ) {
			return 1579089600;
		}
		return '2020-01-15 12:00:00';
	}
}

if ( ! function_exists( 'zeroise' ) ) {
	function zeroise( $number, $threshold ) {
		return sprintf( '%0' . $threshold . 's', $number );
	}
}

if ( ! function_exists( 'calendar_week_mod' ) ) {
	function calendar_week_mod( $number ) {
		return ( $number % 7 + 7 ) % 7;
	}
}

if ( ! function_exists( 'get_month_link' ) ) {
	function get_month_link( $year, $month ) {
		return 'https://example.test/' . (int) $year . '/' . (int) $month . '/';
	}
}

if ( ! function_exists( 'get_day_link' ) ) {
	function get_day_link( $year, $month, $day ) {
		return 'https://example.test/' . (int) $year . '/' . (int) $month . '/' . (int) $day . '/';
	}
}

if ( ! function_exists( 'register_activation_hook' ) ) {
	function register_activation_hook( $file, $callback ) {
		return true;
	}
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
	function register_deactivation_hook( $file, $callback ) {
		return true;
	}
}

/* -------------------------------------------------------------------------- *
 * Widgets
 * -------------------------------------------------------------------------- */

if ( ! class_exists( 'WP_Widget' ) ) {
	class WP_Widget {
		public $id_base;
		public $name;
		public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {
			$this->id_base = $id_base;
			$this->name    = $name;
		}
		public function get_field_id( $field ) {
			return $this->id_base . '-' . $field;
		}
		public function get_field_name( $field ) {
			return $this->id_base . '[' . $field . ']';
		}
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$parsed = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$parsed = $args;
		} else {
			parse_str( (string) $args, $parsed );
		}
		return is_array( $defaults ) ? array_merge( $defaults, $parsed ) : $parsed;
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	/**
	 * Records the arguments it was built with and loops over CE_Test_State::$found_posts,
	 * which is empty unless a test fills it, so by default it finds nothing. the_post() sets
	 * the global $post as the real loop does.
	 */
	class WP_Query {
		/** @var mixed the arguments the most recent instance was built with */
		public static $last_query = null;
		public $query;
		private $current = 0;
		public function __construct( $query = '' ) {
			$this->query      = $query;
			self::$last_query = $query;
		}
		public function query( $query ) {
			$this->__construct( $query );
			return CE_Test_State::$found_posts;
		}
		public function have_posts() {
			return $this->current < count( CE_Test_State::$found_posts );
		}
		public function the_post() {
			$GLOBALS['post'] = CE_Test_State::$found_posts[ $this->current++ ];
		}
	}
}

if ( ! function_exists( 'is_404' ) ) {
	function is_404() {
		return false;
	}
}

if ( ! function_exists( 'get_the_date' ) ) {
	function get_the_date( $format = '', $post = null ) {
		return gmdate( '' === $format ? 'F j, Y' : $format );
	}
}

/*
 * Conditional tags, driven by CE_Test_State::$is so a test can put a widget on the front page
 * or a single comic. is_404(), is_archive() and is_search() above predate this and stay false.
 */
if ( ! function_exists( 'is_home' ) ) {
	function is_home() {
		return ! empty( CE_Test_State::$is['home'] );
	}
}

if ( ! function_exists( 'is_front_page' ) ) {
	function is_front_page() {
		return ! empty( CE_Test_State::$is['front_page'] );
	}
}

if ( ! function_exists( 'is_paged' ) ) {
	function is_paged() {
		return ! empty( CE_Test_State::$is['paged'] );
	}
}

if ( ! function_exists( 'is_single' ) ) {
	function is_single() {
		return ! empty( CE_Test_State::$is['single'] );
	}
}

if ( ! function_exists( 'is_singular' ) ) {
	function is_singular() {
		return ! empty( CE_Test_State::$is['singular'] );
	}
}

if ( ! function_exists( 'is_page' ) ) {
	function is_page() {
		return ! empty( CE_Test_State::$is['page'] );
	}
}

if ( ! function_exists( 'is_active_widget' ) ) {
	function is_active_widget( ...$args ) {
		return false;
	}
}

if ( ! function_exists( 'setup_postdata' ) ) {
	function setup_postdata( $post ) {
		return true;
	}
}

if ( ! function_exists( 'the_content' ) ) {
	function the_content() {
		echo isset( $GLOBALS['post']->post_content ) ? $GLOBALS['post']->post_content : '';
	}
}

if ( ! function_exists( 'the_permalink' ) ) {
	function the_permalink() {
		echo esc_url( get_permalink( $GLOBALS['post'] ) );
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type( $post = null ) {
		return isset( $GLOBALS['post']->post_type ) ? $GLOBALS['post']->post_type : false;
	}
}

/** Prints its three labels the way core does: as given, without escaping them. */
if ( ! function_exists( 'comments_popup_link' ) ) {
	function comments_popup_link( $zero = false, $one = false, $more = false ) {
		echo '<a href="' . esc_url( get_permalink( $GLOBALS['post'] ) ) . '#respond">' . $zero . '|' . $one . '|' . $more . '</a>';
	}
}

if ( ! function_exists( 'get_comments_number' ) ) {
	function get_comments_number( $post = 0 ) {
		return 3;
	}
}

if ( ! function_exists( 'has_post_thumbnail' ) ) {
	function has_post_thumbnail( $post = null ) {
		return true;
	}
}

if ( ! function_exists( 'get_the_post_thumbnail' ) ) {
	function get_the_post_thumbnail( $post = null, $size = 'post-thumbnail' ) {
		$post_id = is_object( $post ) ? $post->ID : (int) $post;
		return '<img src="https://example.test/thumb-' . $post_id . '.png" class="attachment-' . $size . ' size-' . $size . ' wp-post-image" alt="" />';
	}
}

if ( ! function_exists( 'get_post_thumbnail_id' ) ) {
	function get_post_thumbnail_id( $post = null ) {
		return 11;
	}
}

if ( ! function_exists( 'wp_get_attachment_image_src' ) ) {
	function wp_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = false ) {
		return array( 'https://example.test/full-' . (int) $attachment_id . '.png', 900, 600, false );
	}
}

if ( ! function_exists( 'get_intermediate_image_sizes' ) ) {
	function get_intermediate_image_sizes() {
		return array( 'thumbnail', 'medium', 'large' );
	}
}

/** The [cast-page] shortcode is what the cast hovercards expand; a marker is enough here. */
if ( ! function_exists( 'do_shortcode' ) ) {
	function do_shortcode( $content ) {
		return '<p class="cast-description">' . $content . '</p>';
	}
}

/* Terms come from CE_Test_State::$terms, keyed by taxonomy, as for wp_get_post_terms(). */
if ( ! function_exists( 'get_the_terms' ) ) {
	function get_the_terms( $post, $taxonomy ) {
		return empty( CE_Test_State::$terms[ $taxonomy ] ) ? false : CE_Test_State::$terms[ $taxonomy ];
	}
}

if ( ! function_exists( 'wp_get_object_terms' ) ) {
	function wp_get_object_terms( $object_ids, $taxonomies, $args = array() ) {
		return array_key_exists( $taxonomies, CE_Test_State::$terms ) ? CE_Test_State::$terms[ $taxonomies ] : array();
	}
}

if ( ! function_exists( 'get_terms' ) ) {
	function get_terms( $args = array(), $deprecated = '' ) {
		$taxonomy = is_array( $args ) ? $args['taxonomy'] : $args;
		return array_key_exists( $taxonomy, CE_Test_State::$terms ) ? CE_Test_State::$terms[ $taxonomy ] : array();
	}
}

/** Same markup as core: an escaped link per term, the term name as stored. */
if ( ! function_exists( 'get_the_term_list' ) ) {
	function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '' ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( ! $terms ) {
			return false;
		}
		$links = array();
		foreach ( $terms as $term ) {
			$links[] = '<a href="' . esc_url( get_term_link( $term->slug, $taxonomy ) ) . '" rel="tag">' . $term->name . '</a>';
		}
		return $before . implode( $sep, $links ) . $after;
	}
}

if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current = true, $display = true ) {
		$result = (string) $selected === (string) $current ? " selected='selected'" : '';
		if ( $display ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'checked' ) ) {
	function checked( $checked, $current = true, $display = true ) {
		$result = (string) $checked === (string) $current ? " checked='checked'" : '';
		if ( $display ) {
			echo $result;
		}
		return $result;
	}
}

/*
 * Category walkers and the two category template tags the chapter widget drives them with.
 * The template tags reproduce core's wrapper markup byte for byte (wp-includes/
 * category-template.php), including the quoting and spacing of the <select> tag, and walk a
 * flat list of CE_Test_State::$terms['chapters'] through the walker they are given.
 */
if ( ! class_exists( 'Walker_Category' ) ) {
	class Walker_Category {
		public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {}
		public function end_el( &$output, $data_object, $depth = 0, $args = array() ) {
			$output .= "</li>\n";
		}
	}
}

if ( ! class_exists( 'Walker_CategoryDropdown' ) ) {
	class Walker_CategoryDropdown {
		public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {}
	}
}

if ( ! function_exists( 'wp_list_categories' ) ) {
	function wp_list_categories( $args = array() ) {
		$args   = wp_parse_args( $args, array( 'show_count' => 0, 'selected' => 0 ) );
		$output = '';
		foreach ( get_terms( $args['taxonomy'] ) as $term ) {
			$args['walker']->start_el( $output, $term, 0, $args );
			$args['walker']->end_el( $output, $term, 0, $args );
		}
		return $output;
	}
}

if ( ! function_exists( 'wp_dropdown_categories' ) ) {
	function wp_dropdown_categories( $args = array() ) {
		$args  = wp_parse_args( $args, array( 'show_option_all' => '', 'show_count' => 0, 'selected' => 0, 'hide_if_empty' => false ) );
		$terms = get_terms( $args['taxonomy'] );
		if ( empty( $terms ) && $args['hide_if_empty'] ) {
			return '';
		}
		$name   = esc_attr( $args['name'] );
		$class  = esc_attr( isset( $args['class'] ) ? $args['class'] : 'postform' );
		$output = "<select  name='$name' id='$name' class='$class'>\n";
		if ( $args['show_option_all'] ) {
			$selected = ( '0' === (string) $args['selected'] ) ? " selected='selected'" : '';
			$output  .= "\t<option value='0'$selected>{$args['show_option_all']}</option>\n";
		}
		foreach ( $terms as $term ) {
			$args['walker']->start_el( $output, $term, 0, $args );
		}
		return $output . "</select>\n";
	}
}

/* -------------------------------------------------------------------------- *
 * $wpdb spy — records the SQL it is handed so tests can assert on the query text
 * without a database. This is what stands in for MySQL when checking that untrusted
 * shortcode attributes never reach the SQL string.
 * -------------------------------------------------------------------------- */

class CE_WPDB_Spy {
	public $posts              = 'wp_posts';
	public $terms              = 'wp_terms';
	public $term_relationships = 'wp_term_relationships';
	public $term_taxonomy      = 'wp_term_taxonomy';
	public $prefix             = 'wp_';

	/** @var string[] every SQL string handed to prepare() or a get_*() method */
	public $queries = array();
	/** @var mixed value the next get_col()/get_results()/get_var()/get_row() returns */
	public $result = array();
	/** @var array[] every update() call, as array( table, data, where ) */
	public $updates = array();

	public function prepare( $query, ...$args ) {
		// Close enough to $wpdb::prepare for assertion purposes: %d becomes an integer,
		// %s becomes a single-quoted escaped string.
		$i = 0;
		$out = preg_replace_callback(
			'/%[dsf]/',
			function ( $m ) use ( &$i, $args ) {
				$arg = array_key_exists( $i, $args ) ? $args[ $i ] : '';
				$i++;
				if ( '%d' === $m[0] ) {
					return (string) (int) $arg;
				}
				if ( '%f' === $m[0] ) {
					return (string) (float) $arg;
				}
				return "'" . addslashes( (string) $arg ) . "'";
			},
			$query
		);
		$this->queries[] = $out;
		return $out;
	}

	public function get_col( $query = null, $x = 0 ) {
		if ( null !== $query ) {
			$this->queries[] = $query;
		}
		return $this->result;
	}

	public function get_results( $query = null, $output = null ) {
		if ( null !== $query ) {
			$this->queries[] = $query;
		}
		return $this->result;
	}

	public function get_var( $query = null, $x = 0, $y = 0 ) {
		if ( null !== $query ) {
			$this->queries[] = $query;
		}
		return is_array( $this->result ) ? null : $this->result;
	}

	public function get_row( $query = null, $output = null, $y = 0 ) {
		if ( null !== $query ) {
			$this->queries[] = $query;
		}
		return null;
	}

	public function query( $query ) {
		$this->queries[] = $query;
		return true;
	}

	public function update( $table, $data, $where, ...$rest ) {
		$this->updates[] = array( $table, $data, $where );
		return 1;
	}

	/** The SQL from the most recent call. */
	public function last() {
		return end( $this->queries );
	}

	/** True when any recorded query contains $needle. */
	public function sawSql( $needle ) {
		foreach ( $this->queries as $q ) {
			if ( false !== strpos( $q, $needle ) ) {
				return true;
			}
		}
		return false;
	}
}
