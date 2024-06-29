# Views

In PressGang, views are responsible for rendering the HTML output for the front-end of the website. Views are created using the [Twig](https://twig.symfony.com/) templating engine, provided by the [Timber library](https://upstatement.com/timber/). This approach separates the presentation logic from the business logic, promoting cleaner and more maintainable code.

## Twig Templates

Twig is a modern templating engine for PHP, offering a clean syntax that is easy to read and write. Timber integrates Twig into WordPress, allowing you to use Twig templates for rendering your theme views.

See:-

* https://twig.symfony.com/
* https://upstatement.com/timber/
* https://timber.github.io/docs/v2/

### Using Timber to via PressGang Controllers to Render Views

While Timber provides a simple way to render Twig templates with context data, see - https://timber.github.io/docs/v2/getting-started/introduction/#a-view.
PressGang takes this a step further by introducing the concept of Controllers to use in your WordPress templates to prepare this context data.

In a typical PressGang Controller, you prepare the context and then render a Twig template, this is handled in the 
`AbstractContrller` which takes a string `$template` argument in its constructor for the template name to render, it also attaches the `Timber::context()` to the $context class property.

```php
	use Timber\Timber;
	
	class AbstractController {
	
		public function __construct( string|null $template = null ) {
			$this->template = $template;
			$this->context  = Timber::context();
		}

	}
```

The Controller's `get_context` function helps build the context.

E.g. in the `PostsController`

```php
	protected function get_context(): array {
		$post = $this->get_post();

		$this->context['post']             = $post;
		$this->context[ $this->post_type ] = $post;

		return $this->context;
	}
```

### Views

The `views` folder in your child theme is by default the home of your twig templates.

Timber will look for that template in different directories. It will first look in the child theme and then falls back to the parent theme (it’s the same logic as in WordPress). https://timber.github.io/docs/v2/guides/template-locations/

You may want to follow the organisational structure used in the PressGang theme:

--layouts  
--macros  
--partials  
--scaffold  
--shared



