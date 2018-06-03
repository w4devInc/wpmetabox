<?php
namespace W4dev\Wpmetabox;

/**
 * OCN Metabox Factory
 * @package OCN
**/

class Factory
{
	public static $metaboxes = [];

	/* Get all available metaboxes */
	public static function get_metaboxes()
	{
		return self::$metaboxes;
	}

	public static function register_metabox($metabox)
	{
		self::$metaboxes[] = $metabox;
	}

	public static function register_metaboxes($metaboxes = [])
	{
		foreach ($metaboxes as $metabox) {
			self::register_metabox($metabox);
		}
	}
}