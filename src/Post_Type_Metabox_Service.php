<?php
namespace W4dev\Wpmetabox;
use \W4dev\Wpform\Api as Form_Api;

class Post_Type_Metabox_Service
{
	public function __construct()
	{
		add_action('admin_enqueue_scripts'			, [$this, 'enqueue_scripts']);
		add_action('add_meta_boxes'					, [$this, 'post_meta_boxes'], 10, 2);
		add_action('save_post'						, [$this, 'save_post']);
	}

	/* load js/css on admin post create/edit page */
	public function enqueue_scripts()
	{
		global $pagenow;
		/* load form scripts on post creation/editing & terms creation/editing page */
		if (in_array($pagenow, ['post.php', 'post-new.php'])) {
			Form_Api::register_form_scripts();
			Form_Api::enqueue_form_scripts();
		}

		/* load media support as we may need this */
		/* TODO - load this conditionally whether required */
		if (function_exists('wp_enqueue_media') && ! did_action('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}

	/* register meta boxes on the screen */
	public function post_meta_boxes($post_type, $post)
	{
		foreach (Factory::get_metaboxes() as $metabox_instance) {
			try {
				if (in_array($post_type, $metabox_instance->screens)) {
					add_meta_box(
						$metabox_instance->id, 
						$metabox_instance->title, 
						[$metabox_instance, 'render'], 
						$metabox_instance->screens, 
						$metabox_instance->context, 
						$metabox_instance->priority, 
						$metabox_instance->callback_args
					);
				}
			} catch(Exception $e){
				// not caching exception
			}
		}
	}

	/* store metabox field values */
	public function save_post($id)
	{
		foreach (Factory::get_metaboxes() as $metabox_instance) {
			try {
				if ('post' == $metabox_instance->type && $metabox_instance->can_save($id)) {
					$metabox_instance->save_values($id, stripslashes_deep($_POST));
				}
			} catch(Exception $e){
				// not caching exception
			}
		}
	}
}

	