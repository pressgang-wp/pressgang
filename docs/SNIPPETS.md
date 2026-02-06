# Snippets

In PressGang, snippets are self-contained classes responsible for specific functionalities within a theme. These
snippets follow a consistent initialization pattern, making it easier to manage, reuse, and maintain code. By
encapsulating functionality in snippets, developers can keep the `functions.php` file clean and promote modern
Object-Oriented standards.

Think of snippets as your ship's provisions — pre-packaged, self-contained, ready to be loaded aboard any theme.

## SnippetInterface

The `SnippetInterface` defines the standard structure for snippet classes. Each snippet is responsible for a specific
functionality and is initialized through its constructor.

### Interface Definition

```php
namespace PressGang\Snippets;

interface SnippetInterface {

    /**
     * Constructor for the snippet.
     *
     * @param array $args Associative array of arguments for the snippet initialization.
     */
    public function __construct(array $args);

}
```

Each snippet's constructor is invoked during theme setup, so it should register any necessary WordPress hooks, enqueue scripts/styles, and perform any other configuration.

## Configuring Snippets

Snippets are imported into a theme via the `config/snippets.php` file. This file can be used to pass an `$args` array to
the snippet in question.

### Configuration Format

Each entry in the configuration array corresponds to a snippet class. The key can be either the fully qualified class
name or a simple class name (if it follows the PressGang or child theme Snippets namespace convention). The array value
consists of arguments to be passed to the snippet's constructor.

```php
return [
    'Fully\\Qualified\\Namespace\\SpecificSnippet' => ['arg1' => 'value1'],
    'GeneralSnippet' => ['arg2' => 'value2'],
];
```

## Snippet Template Locations

PressGang automatically adds the `pressgang-snippets` vendor views directory to Timber's template paths. This means any Twig templates bundled with snippet packages are automatically available to your theme — no manual path configuration needed.

## Benefits of Using Snippets

* **Code Reuse:** Snippets allow functionality to be reused across themes. Write once, deploy across your entire fleet.
* **Clean Functions File:** By encapsulating functionality in snippets, the `functions.php` file remains clean and organized.
* **Modern Standards:** Encourages the use of modern Object-Oriented programming standards.
* **Composable:** Snippets can be distributed via Composer packages, making it easy to share functionality between projects.

## PressGang Snippets

A separate repository exists for reusable snippets that can be incorporated into your theme development at [pressgang-wp/pressgang-snippets](https://github.com/pressgang-wp/pressgang-snippets).
