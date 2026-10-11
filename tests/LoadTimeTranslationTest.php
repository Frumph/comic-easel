<?php

/**
 * Nothing translates, or reads the settings, while the plugin loads.
 *
 * WordPress 6.7 and later log a notice when a text domain is loaded before after_setup_theme,
 * which in practice means before init. ceo_pluginfo() builds the default settings on a fresh
 * install, and some of those defaults are translated, so reading a setting that early is as bad
 * as calling __() directly. The notice only appears on the first request after activation, or
 * after the settings are reset, which is exactly what a reviewer's clean install sees.
 *
 * comiceasel.php cannot be required in a unit test, so this sweeps the source instead: code at
 * file scope in every file the plugin loads, and the callbacks it hooks to actions that fire
 * before after_setup_theme.
 */
class LoadTimeTranslationTest extends CE_TestCase {

	/** Calls that load the text domain, directly or by building the default settings. */
	const EARLY_CALLS = array(
		'ceo_pluginfo', 'ceo_load_options',
		'__', '_e', '_x', '_ex', '_n', '_nx', 'translate', 'translate_nooped_plural',
		'esc_html__', 'esc_html_e', 'esc_html_x', 'esc_attr__', 'esc_attr_e', 'esc_attr_x',
	);

	/** Actions that fire before after_setup_theme. */
	const EARLY_HOOKS = array( 'muplugins_loaded', 'plugins_loaded', 'sanitize_comment_cookies', 'setup_theme' );

	/** Files required while the plugin loads (ceo-admin.php and admin-meta.php only in the admin). */
	private static function loadTimeFiles() {
		return array_merge(
			array( CE_PLUGIN_DIR . '/comiceasel.php', CE_PLUGIN_DIR . '/ceo-admin.php' ),
			glob( CE_PLUGIN_DIR . '/functions/*.php' ),
			glob( CE_PLUGIN_DIR . '/widgets/*.php' )
		);
	}

	/**
	 * Walk a file's tokens, reporting each call to one of EARLY_CALLS together with the function
	 * it sits in ('' at file scope; closures count as functions, since they run later).
	 *
	 * @return array<int,array{name:string,line:int,in:string}>
	 */
	private static function earlyCalls( $file ) {
		$tokens  = token_get_all( file_get_contents( $file ) );
		$calls   = array();
		$depth   = 0;
		$stack   = array(); // array( depth the body opened at, function name )
		$pending = null;     // name of a function whose body has not opened yet
		$count   = count( $tokens );
		for ( $i = 0; $i < $count; $i++ ) {
			$token = $tokens[ $i ];
			if ( is_array( $token ) ) {
				if ( T_FUNCTION === $token[0] ) {
					$next    = self::neighbour( $tokens, $i, 1 );
					$pending = is_array( $tokens[ $next ] ) ? $tokens[ $next ][1] : '{closure}';
				} elseif ( T_CURLY_OPEN === $token[0] || T_DOLLAR_OPEN_CURLY_BRACES === $token[0] ) {
					$depth++;
				} elseif ( T_STRING === $token[0] && in_array( strtolower( $token[1] ), self::EARLY_CALLS, true ) ) {
					$prev = $tokens[ self::neighbour( $tokens, $i, -1 ) ];
					$next = $tokens[ self::neighbour( $tokens, $i, 1 ) ];
					$is_declaration_or_method = is_array( $prev ) && in_array( $prev[0], array( T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR ), true );
					if ( '(' === $next && ! $is_declaration_or_method ) {
						$calls[] = array( 'name' => $token[1], 'line' => $token[2], 'in' => $stack ? end( $stack )[1] : '' );
					}
				}
			} elseif ( '{' === $token ) {
				$depth++;
				if ( null !== $pending ) {
					$stack[] = array( $depth, $pending );
					$pending = null;
				}
			} elseif ( '}' === $token ) {
				if ( $stack && end( $stack )[0] === $depth ) {
					array_pop( $stack );
				}
				$depth--;
			} elseif ( ';' === $token ) {
				$pending = null; // an abstract or interface method has no body
			}
		}
		return $calls;
	}

	/** Index of the nearest token in $direction that is not whitespace or a comment. */
	private static function neighbour( $tokens, $i, $direction ) {
		do {
			$i += $direction;
		} while ( is_array( $tokens[ $i ] ) && in_array( $tokens[ $i ][0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) );
		return $i;
	}

	public function testNothingAtFileScopeTranslatesOrReadsTheSettings() {
		$offenders = array();
		foreach ( self::loadTimeFiles() as $file ) {
			foreach ( self::earlyCalls( $file ) as $call ) {
				if ( '' === $call['in'] ) {
					$offenders[] = substr( $file, strlen( CE_PLUGIN_DIR ) + 1 ) . ':' . $call['line'] . ' ' . $call['name'] . '()';
				}
			}
		}
		$this->assertSame( array(), $offenders );
	}

	public function testNothingHookedBeforeAfterSetupThemeTranslatesOrReadsTheSettings() {
		$early_callbacks = array();
		$calls_by_function = array();
		foreach ( self::loadTimeFiles() as $file ) {
			$source = file_get_contents( $file );
			preg_match_all( '/add_(?:action|filter)\(\s*[\'"](' . implode( '|', self::EARLY_HOOKS ) . ')[\'"]\s*,\s*[\'"](\w+)[\'"]/', $source, $matches );
			$early_callbacks = array_merge( $early_callbacks, $matches[2] );
			foreach ( self::earlyCalls( $file ) as $call ) {
				$calls_by_function[ $call['in'] ][] = $call['name'] . '() on line ' . $call['line'];
			}
		}
		// Guard the sweep itself: if the pattern stops matching, the test would pass vacuously.
		$this->assertContains( 'ceo_init_injections', $early_callbacks );

		$offenders = array();
		foreach ( $early_callbacks as $callback ) {
			if ( isset( $calls_by_function[ $callback ] ) ) {
				$offenders[ $callback ] = $calls_by_function[ $callback ];
			}
		}
		$this->assertSame( array(), $offenders );
	}
}
