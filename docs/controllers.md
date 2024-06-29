# Controllers

## Overview

In PressGang, controllers manage the display logic for different types of pages and templates, often reflecting the WordPress template hierarchy, e.g., `PageController`. Conventionally, they use singular names (e.g., `PostController`) to represent single pages, and plural names (e.g., `PostsController`) to represent archive pages. Essentially, these classes apply the logic to build the Timber context as passed to the views (Twig templates).
### AbstractController Base Class

The AbstractController class provides common functionalities for all controllers, including context management and template rendering.

### Key Methods

* `__construct(string|null $template = null)`: Initializes the controller with the specified Twig template.
* `get_context()`: Retrieves the current Timber context.
* `render()`: Renders the Twig template with the current context.

## Example: `PageController`

```php
namespace PressGang\Controllers;

use Timber\Post;
use Timber\Timber;

class PageController extends AbstractController {
    protected Post $post;

    public function __construct(string|null $template = 'page.twig') {
        parent::__construct($template);
    }

    protected function get_post(): Post {
        if (empty($this->post)) {
            $this->post = Timber::get_post();
        }
        return $this->post;
    }

    protected function get_context(): array {
        $post = $this->get_post();
        $this->context['page'] = $post;
        $this->context['post'] = $post;
        return $this->context;
    }
}
```

## Usage in Templates

Controllers are then utilized in standard WordPress templates - e.g. the `PageController` can be utilized in a `page.php` or `front-page.php` template file as follows:

```php
use PressGang\Controllers\PageController;

PressGang\PressGang::render(controller: PageController::class, twig: 'front-page.twig');
```

or

```php

( new PressGang\PageController( 'front-page.twig' ) )->render();
```

This means that PressGang maintains the standard WordPress theme structure while incorporating Object-Oriented principles effectively allowing you to reuse, inherit or override code for building the view context across themes. Let's see how we do that in a child theme:

## Extending Controllers in Child Themes

To extend the functionality of a parent theme controller in a child theme, create a new controller class in the child theme that inherits from the parent controller.

### Example: Extending PageController

Create a ChildPageController in the child theme:

```php
namespace ChildTheme\Controllers;

use PressGang\Controllers\PageController;

class ChildPageController extends PageController {
    protected function get_context(): array {
        $context = parent::get_context();
        $context['custom_data'] = 'Additional data';
        return $context;
    }
}
```

This setup allows the child theme to inherit and extend the logic defined in the parent theme controllers, promoting code reuse and maintainability. 
