<?php
namespace W4dev\Wpmetabox;

class Service
{
	protected static $registered_types = [];
	protected static $initialized_types = [];

	public static function init()
	{
		foreach (Factory::get_metaboxes() as $metabox_instance) {
			if (! in_array($metabox_instance->type, self::$registered_types)) {
				self::$registered_types[] = $metabox_instance->type;
			}
		}

		if (in_array('post', self::$registered_types) && ! in_array('post', self::$initialized_types)) {
			self::$initialized_types[] = 'post';
			new \W4dev\Wpmetabox\Post_Metabox_Service();
		}

		if (in_array('term', self::$registered_types) && ! in_array('term', self::$initialized_types)) {
			self::$initialized_types[] = 'term';
			new \W4dev\Wpmetabox\Term_Metabox_Service();
		}
	}
}

	