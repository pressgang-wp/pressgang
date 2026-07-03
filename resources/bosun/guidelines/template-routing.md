## Template routing (enabled in this theme)

This theme opts into convention-based template routing
(`TemplateRoutingServiceProvider` in `config/service-providers.php`).
Do NOT create per-template PHP stub files — requests resolve to controllers
by convention:

- `search` => `SearchController`, `front-page` => `FrontPageController` (StudlyCase)
- `archive-{type}` => pluralised `{Types}Controller` (`archive-event` => `EventsController`)
- `single-{type}` / `taxonomy-{tax}` => `{Subject}Controller`
  (`single-event` => `EventController`, `taxonomy-event-type` => `EventTypeController`)
- The matching `{candidate}.twig` in `views/` renders automatically.
- Hyphenated template names work for underscored post type/taxonomy keys.

`config/controllers.php` maps names that defy convention. Page templates are
registered file-lessly in `config/page-templates.php` (no `page-templates/`
directory) and resolve to `{Slug}Controller` or PageController.

A physical template file always wins over dispatch — only add one for
genuine logic (e.g. conditional controller selection), and say why.
