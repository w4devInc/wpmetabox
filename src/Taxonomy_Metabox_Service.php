<?php
namespace W4dev\Wpmetabox;
use \W4dev\Wpform\Api as Form_Api;

class Taxonomy_Metabox_Service
{
	public function __construct()
	{
		add_action('admin_enqueue_scripts'			, [$this, 'enqueue_scripts']);
		add_action('edit_tag_form_fields'			, [$this, 'term_meta_boxes'], 10, 2);
		add_action('edit_category_form_fields'		, [$this, 'term_meta_boxes'], 10, 2);
		add_action('edit_term'						, [$this, 'edit_term'], 10, 3);
	}

	/* load js/css on admin post create/edit page */
	public function enqueue_scripts()
	{
		global $pagenow;
		/* load form scripts on post creation/editing & terms creation/editing page */
		if (in_array($pagenow, ['term.php', 'edit-tags.php'])) {
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
	public function term_meta_boxes($tag)
	{
		foreach (Factory::get_metaboxes() as $metabox_instance) {
			try {
				if (in_array($tag->taxonomy, $metabox_instance->screens)) {
					$metabox_instance->render($tag);
				}
			} catch(Exception $e){
				// not caching exception
			}
		}
	}

	/* store metabox field values */
	public function edit_term($id, $tt_id, $taxonomy)
	{
		foreach (Factory::get_metaboxes() as $metabox_instance) {
			try {
				if ('term' == $metabox_instance->type && $metabox_instance->can_save($id)) {
					$metabox_instance->taxonomy = $taxonomy;
					$metabox_instance->save_values($id, stripslashes_deep($_POST));
				}
			} catch(Exception $e){
				// not caching exception
			}
		}
	}
}

	