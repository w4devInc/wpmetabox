# Wpmetabox

A metabox abstraction layer for WordPress plugin & theme development.

## Installation

### Using git clone
```
$ git clone https://github.com/w4devinc/wpmetabox.git
```

## Basic Usage

Create a metabox class extending our base class

```php
class Metabox_Additional_Post_Settings extends W4dev\Wpmetabox\Base
{
	public function __construct()
	{
		/* type = post|term */
		$this->type = 'post';
		/* an unique string identifier */
		$this->id = 'form_settings';
		/* metabox title */
		$this->title = __('Additional Post Settings', 'ocn');
		/* screens would be array of post_types / taxonomies */
		$this->screens = ['post'];
	}

	public function get_fields($post_id)
	{
		$fields = [
			[
				'priority'		=> 10,
				'key' 			=> 'sub_title',
				'name' 			=> 'sub_title',
				'label' 		=> esc_html__('Sub title', 'ocn'),
				'type' 			=> 'text',
				'post_field' 	=> 'post_excerpt'
			],
			[
				'priority'		=> 11,
				'key' 			=> 'description',
				'name' 			=> 'description',
				'label' 		=> esc_html__('Description', 'ocn'),
				'type' 			=> 'textarea',
				'post_field' 	=> 'post_content'
			],
			[
				'priority'		=> 15,
				'key' 			=> 'pdf_file',
				'name' 			=> 'pdf_file',
				'label' 		=> esc_html__('PDF file', 'ocn'),
				'type' 			=> 'media'
			]
		];

		return $fields;
	}

	public function render($post)
	{
		parent::render($post->ID);
	}

	public function save_values($id, $data = [])
	{
		parent::save_values($id, $data);
	}
}
```

Register the metabox class
```php
W4dev\Wpmetabox\Factory::register_metabox(new Metabox_Additional_Post_Settings());
```

Invoke the metabox service
```php
new W4dev\Wpmetabox\Service::init();
```