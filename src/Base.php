<?php
namespace W4dev\Wpmetabox;
use \W4dev\Wpform\Form\Simple as Form_Simple;

/**
 * Metabox Abstraction
 * metabox is a module who keeps a setting panel, and handle storage. metabox can be used post, term, user edit/create screen.
 * @package OCN
**/

abstract class Base
{
	/* metabox type - post, term, user */
	public $type;

	/* metabox id */
	public $id;

	/* metabox title */
	public $title;

	/* taxonomy */
	public $taxonomy;

	/* screens */
	public $screens = [];

	/* content */
	public $context = 'normal';

	/* priority */
	public $priority = 'default';

	/* callback arguments */
	public $callback_args = [];

	/* store metabox field values in a single postmeta field */
	public $single_storage = false;

	/* single postmeta field name */
	public $single_storage_key = '';

	/* render metabox to screen */
	public function render($id)
	{
		wp_nonce_field('w4dev_metabox_nonce_'. $this->id, $this->id);

		$fields = $this->get_fields($id);
		$values = $this->get_values($id);
		$settings = [
			'id' => $this->id,
			'no_form' => true,
			'submit_bottom' => false,
			'class' => 'wf-metabox'
		];

		$form = new Form_Simple(compact(['settings', 'fields', 'values']));
		$form->render();
	}

	/* check if field values should be saved */
	public function can_save($id)
	{
		// Add nonce for security and authentication.
        $nonce_name = isset($_POST[$this->id]) ? $_POST[$this->id] : '';
        $nonce_action = 'w4dev_metabox_nonce_'. $this->id;
 
        // Check if nonce is set.
        if (! isset($nonce_name)) {
            return false;
        }
 
        // Check if nonce is valid.
        if (! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return false;
        }
 
 		if ('term' == $this->type) {
			// Check if user has permissions to save data.
			if (! current_user_can( 'edit_term', $id ) ) {
				return false;
			}
		} else {
			// Check if user has permissions to save data.
			if (! current_user_can( 'edit_post', $id ) ) {
				return false;
			}
	 
			// Check if not an autosave.
			if (wp_is_post_autosave( $id ) ) {
				return false;
			}

			// Check if not a revision.
			if (wp_is_post_revision( $id ) ) {
				return false;
			}
		}

		return true;
	}

	/* get metabox field values */
	public function get_values($id)
	{
		$values = [];
		if ('term' == $this->type) {
			$values = $this->get_term_values($id);
		} elseif ('post' == $this->type) {
			$values = $this->get_post_values($id);
		}

		return $values;
	}

	/* save metabox field values */
	public function save_values($id, $data = [])
	{
		$values = [];
		foreach ($this->get_fields($id) as $field) {
			if (! empty($field['key'])) {
				if (isset($data[$field['key']])) {
					$value = $data[$field['key']];

					/* repeater field carries a hidden field group, we will clear that out */
					if ('repeater' == $field['type'] && isset($value['KEY'])) {
						unset($value['KEY']);
					}

				} elseif(isset($field['default'])) {
					$value = $field['default'];
				} else {
					$value = '';
				}
				$values[$field['key']] = $value;
			}
		}

		/* if defined, store data on a single meta field */
		if ('term' == $this->type) {
			$this->save_term_values($id, $values);
		} else {
			$this->save_post_values($id, $values);
		}
	}


	/* save metabox field values */
	protected function save_term_values($id, $values = [])
	{
		/* if defined, store data on a single meta field */
		if ($this->single_storage) {
			update_term_meta($id, $this->single_storage_key, $values);
		} else {
			foreach ($values as $key => $value) {
				update_term_meta($id, $key .'_'. $this->taxonomy, $value);
			}
		}
	}

	/* get term metabox field values */
	protected function get_term_values($id)
	{
		$values = [];
		if ($this->single_storage) {
			$values = get_term_meta($id, $this->single_storage_key, true);
		} else {
			foreach ($this->get_fields($id) as $field) {
				if (! empty($field['key'])) {
					$values[$field['key']] = get_term_meta($id, $field['key'] .'_'. $this->taxonomy, true);
				}
			}
		}

		return $values;
	}


	/* save metabox field values */
	protected function save_post_values($id, $values = [])
	{
		$post_data = [];
		foreach ($this->get_fields($id) as $field) {
			if (! empty($field['post_field'])) {
				if (isset($values[$field['key']])) {
					$post_data[$field['post_field']] = $values[$field['key']];
					unset($values[$field['key']]);
				}
			}
		}

		if (! empty($post_data)) {
			// save without hooks
			global $wpdb;
			$wpdb->update($wpdb->posts, $post_data, ['ID' => $id]);
		}
		# OCN_Utils::d($post_data);

		/* if defined, store data on a single meta field */
		if ($this->single_storage) {
			update_post_meta($id, $this->single_storage_key, $values);
		} else {
			foreach ($values as $key => $value) {
				update_post_meta($id, $key, $value);
			}
		}
	}

	/* get post metabox field values */
	protected function get_post_values($id)
	{
		// OCN_Utils::d(get_post($id));
		$values = [];
		if ($this->single_storage) {
			$values = get_post_meta($id, $this->single_storage_key, true);
		} else {
			foreach ($this->get_fields($id) as $field) {
				if (! empty($field['post_field'])) {
					$values[$field['key']] = get_post_field($field['post_field'], $id);
				} elseif (! empty($field['key'])) {
					$values[$field['key']] = get_post_meta($id, $field['key'], true);
				}
			}
		}

		return $values;
	}
}