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

	public static function create_metabox($id, $class_name)
	{
		if (class_exists($class_name)) {
			return new $class_name();
		}

		throw new Exception(__('Metabox not found', 'ocn'));
	}
}