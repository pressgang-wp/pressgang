# Blocks

PressGang provides declarative Gutenberg block registration, keeping your block setup clean and your codebase shipshape. Define your blocks in config, and PressGang handles the registration, path resolution, and lifecycle hooks.

## How It Works

Blocks are registered via `config/blocks.php`. Each entry points to a directory containing a `block.json` file. PressGang's `Blocks` configuration class:

1. Resolves the block path (checking the child theme first, then the parent theme).
2. Validates and reads the `block.json` file.
3. Registers the block type with WordPress.
4. Invokes any `on_register` callback defined in the block's ACF `renderCallback`.
5. Fires a `pressgang_block_registered_{name}` action for additional setup.

## Directory Structure

Each block lives in its own directory under `blocks/`:

```
blocks/
  hero/
    block.json
    hero.twig
  testimonial/
    block.json
    testimonial.twig
```

The `block.json` file follows the standard [WordPress block.json](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/) format.

## Configuration

### Simple Registration

List paths to block directories in `config/blocks.php`:

```php
return [
    '/blocks/hero',
    '/blocks/testimonial',
    '/blocks/call-to-action',
];
```

### With Additional Arguments

Pass extra arguments for `register_block_type()` using an associative array:

```php
return [
    '/blocks/hero' => [
        'style' => 'hero-styles',
    ],
    '/blocks/testimonial',
];
```

## Child/Parent Theme Resolution

PressGang automatically checks the child theme directory first when resolving block paths. This means you can override a parent theme's block by creating a block with the same path in your child theme. The resolved paths are cached for performance.

```
1. Check: get_stylesheet_directory() . '/blocks/hero'  (child theme)
2. Fallback: get_template_directory() . '/blocks/hero'  (parent theme)
```

## ACF Blocks

PressGang has first-class support for [ACF blocks](https://www.advancedcustomfields.com/resources/blocks/). When a block's `block.json` defines an ACF `renderCallback`, PressGang will:

1. Look for the class specified in `acf.renderCallback[0]`.
2. If the class exists and has a static `on_register()` method, call it with the block settings.

This allows blocks to perform one-time setup (like registering field groups) at registration time.

### Example block.json

```json
{
    "name": "acf/hero",
    "title": "Hero",
    "description": "A hero banner block.",
    "category": "theme",
    "acf": {
        "mode": "preview",
        "renderCallback": ["App\\Blocks\\Hero", "render"]
    }
}
```

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `pressgang_block_registered_{name}` | action | Fired after a block is registered. Receives the block settings array. |

The `{name}` placeholder is the block's `name` field from `block.json` (e.g. `pressgang_block_registered_acf/hero`).

## Block Rendering Rules

{% hint style="info" %}
Block rendering should be a pure function of block context — no side effects, no queries in the render path that aren't cached.
{% endhint %}

* Keep block templates simple and focused.
* Prefer Twig templates over inline PHP render callbacks.
* For new UI elements, prefer blocks over shortcodes — they give editors a much better experience.
