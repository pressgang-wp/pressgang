# Snippets

In PressGang, snippets are self-contained classes responsible for specific functionalities within a theme. These
snippets follow a consistent initialization pattern, making it easier to manage, reuse, and maintain code. By
encapsulating functionality in snippets, developers can keep the functions.php file clean and promote modern
Object-Oriented standards.

## SnippetInterface

The `SnippetInterface` defines the standard structure for snippet classes. Each snippet is responsible for a specific
functionality and is initialized through its constructor.

### Interface Definition

```php
namespace PressGang\Snippets;

/**
 * Interface SnippetInterface
 *
 * Defines the standard structure for snippet classes within a PressGang theme.
 */
interface SnippetInterface {

	/**
	 * Constructor for the snippet.
	 *
	 * @param array $args Associative array of arguments for the snippet initialization.
	 */
	public function __construct(array $args); {}
	
}
```

## Configuring Snippets

Snippets are imported into a theme via the `config/snippets.php` file. This file can be used to pass an `$args` array to
the snippet in question.

### Configuration Format

Each entry in the configuration array corresponds to a snippet class. The key can be either the fully qualified class
name or a simple class name (if it follows the PressGang or child theme Snippets namespace convention). The array value
consists of arguments to be passed to the snippet’s constructor.

```php
return [
	'Fully\\Qualified\\Namespace\\SpecificSnippet' => ['arg1' => 'value1'],
	'GeneralSnippet' => ['arg2' => 'value2'],
];
```

## Benefits of Using Snippets

* **Code Reuse:** Snippets allow functionality to be reused across themes.
* **Clean Functions File:** By encapsulating functionality in snippets, the functions.php file remains clean and organized.
* **Modern Standards:** Encourages the use of modern Object-Oriented programming standards.

## PressGang\Snippets

A separate repository exists for reusable snippets that can be incorporated into your theme development at https://github.com/pressgang-wp/pressgang-snippets
