<?php

class FrmTestModeAppHelper {

	/**
	 * @var string $plug_version
	 */
	public static $plug_version = '1.0';

	/**
	 * @return string
	 */
	public static function plugin_version() {
		return self::$plug_version;
	}

	/**
	 * @return string
	 */
	public static function path() {
		return dirname( dirname( __DIR__ ) );
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public static function plugin_url( $path = '' ) {
		return plugins_url( $path, self::path() . '/formidable-test-mode.php' );
	}
}
