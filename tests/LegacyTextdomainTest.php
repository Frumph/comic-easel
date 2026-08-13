<?php

/**
 * ceo_legacy_textdomain_mofile() — functions/filters.php
 *
 * The text domain used to be 'comiceasel'. A site that compiled its own comiceasel-{locale}.mo
 * was loading it, and the rename to 'comic-easel' would have dropped that translation without a
 * word. The filter picks the old filename up when nothing answers to the new one, so the cases
 * below are about which path comes back.
 *
 * Note that the path WordPress asks about is not necessarily the directory the file is in: on
 * WordPress 6.7 and later the question arrives with the plugin's own lang/ directory even when
 * the translation lives in wp-content/languages/plugins. Both are covered here.
 */
class LegacyTextdomainTest extends CE_TestCase {

	/** @var string stands in for the directory WordPress asks about */
	private $dir;

	/** @var string wp-content/languages/plugins, where a site keeps its own translations */
	private $site_dir;

	protected function setUp(): void {
		parent::setUp();
		self::loadPluginFile( 'functions/filters.php' );

		$this->dir      = sys_get_temp_dir() . '/ce-mofile-' . uniqid();
		$this->site_dir = WP_LANG_DIR . '/plugins';
		mkdir( $this->dir );
		mkdir( $this->site_dir, 0777, true );
	}

	protected function tearDown(): void {
		self::removeTree( $this->dir );
		self::removeTree( WP_LANG_DIR );
		parent::tearDown();
	}

	/** Cleanup has to survive a failing assertion, which can leave a subdirectory behind. */
	private static function removeTree( $dir ) {
		foreach ( glob( $dir . '/*' ) as $path ) {
			is_dir( $path ) ? self::removeTree( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}

	private function writeMofile( $path ) {
		file_put_contents( $path, 'not really a .mo, only its readability matters here' );
		return $path;
	}

	/** Every other plugin and theme on the site goes through this filter too. */
	public function testOtherDomainsAreLeftAlone() {
		$this->writeMofile( $this->site_dir . '/comiceasel-de_DE.mo' );
		$mofile = $this->dir . '/comic-easel-de_DE.mo';

		$this->assertSame( $mofile, ceo_legacy_textdomain_mofile( $mofile, 'some-other-plugin' ) );
		$this->assertSame( $mofile, ceo_legacy_textdomain_mofile( $mofile, 'default' ) );
	}

	/** A translation under the current name wins, even with the old file still sitting there. */
	public function testAFileUnderTheCurrentNameIsUsedAsIs() {
		$mofile = $this->writeMofile( $this->dir . '/comic-easel-de_DE.mo' );
		$this->writeMofile( $this->site_dir . '/comiceasel-de_DE.mo' );

		$this->assertSame( $mofile, ceo_legacy_textdomain_mofile( $mofile, 'comic-easel' ) );
	}

	/** The case the shim exists for: the site's own translation, under the old name. */
	public function testTheSiteTranslationUnderTheLegacyNameIsFound() {
		$legacy = $this->writeMofile( $this->site_dir . '/comiceasel-de_DE.mo' );

		$this->assertSame( $legacy, ceo_legacy_textdomain_mofile( $this->dir . '/comic-easel-de_DE.mo', 'comic-easel' ) );
	}

	/** A legacy file left in the directory WordPress asked about is found as well. */
	public function testALegacyFileInTheRequestedDirectoryIsFound() {
		$legacy = $this->writeMofile( $this->dir . '/comiceasel-de_DE.mo' );

		$this->assertSame( $legacy, ceo_legacy_textdomain_mofile( $this->dir . '/comic-easel-de_DE.mo', 'comic-easel' ) );
	}

	/** With a copy in both places the site's own translation is the one that counts. */
	public function testTheSiteTranslationWinsOverACopyInTheRequestedDirectory() {
		$site = $this->writeMofile( $this->site_dir . '/comiceasel-de_DE.mo' );
		$this->writeMofile( $this->dir . '/comiceasel-de_DE.mo' );

		$this->assertSame( $site, ceo_legacy_textdomain_mofile( $this->dir . '/comic-easel-de_DE.mo', 'comic-easel' ) );
	}

	/**
	 * With no legacy file anywhere the original path has to come back untouched: WordPress treats
	 * the return value as the file to load and reports on it, so handing back a path that does not
	 * exist either would only move the failure.
	 */
	public function testTheOriginalPathIsReturnedWhenNoLegacyFileExists() {
		$mofile = $this->dir . '/comic-easel-de_DE.mo';

		$this->assertSame( $mofile, ceo_legacy_textdomain_mofile( $mofile, 'comic-easel' ) );
	}

	/**
	 * Only the filename is rewritten. A plugin directory that happens to be named
	 * comic-easel-something must not be renamed out from under the path.
	 */
	public function testOnlyTheFilenameIsRewritten() {
		$nested = $this->dir . '/comic-easel-1.17';
		mkdir( $nested );
		$legacy = $this->writeMofile( $nested . '/comiceasel-de_DE.mo' );

		$this->assertSame( $legacy, ceo_legacy_textdomain_mofile( $nested . '/comic-easel-de_DE.mo', 'comic-easel' ) );
	}
}
