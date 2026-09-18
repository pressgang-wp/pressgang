## ACF value transformation (enabled in this theme)

This theme sets `'transform_acf_values' => true` in `config/timber.php`, so
`$post->meta()`, `$term->meta()` and their Twig equivalents return Timber
objects for ACF fields. See docs/ACF-VALUES.md for the full contract.

| ACF type | `meta()` returns |
| --- | --- |
| `relationship` | `Timber\PostArrayObject` — may hold `null` for deleted targets and includes unpublished posts |
| `post_object` | Timber post (collection when multiple) |
| `taxonomy` | Timber term (select/radio) or array of terms |
| `image` / `file` | `Timber\Image` / `Timber\Attachment` (use `.src`, not ACF's `.url`) |
| `date_picker`, `date_time_picker` | `DateTimeImmutable` |

Empty object-like fields return `false`, and sometimes `''`. Collections are
not native arrays.

- Read stored IDs with `raw_meta()` wherever values feed a query or an
  ID-based function: `->whereInIds( $post->raw_meta( 'featured' ) ?: [] )`,
  `->whereTax( 'category', $post->raw_meta( 'topics' ) ?: null, 'term_id' )`.
  `raw_meta()` bypasses ACF, so it is safe beside transformed reads.
- For a list of related posts that controllers pass to Twig, resolve the stored
  IDs through Quartermaster instead of normalising the collection:
  `whereInIds( $ids, allowEmpty: true )->orderBy( 'post__in', 'ASC' )` with
  `status( 'publish' )`. This returns published posts in editor order, and
  deleted or draft targets drop out.
- Do not pass transformed collections to `TimberMapper::to_timber_posts()`: it
  accepts native arrays only and returns `[]` for anything else.
- Twig reads of transformed fields work directly. `get_post()`, `get_term()`
  and `get_image()` accept values that are already Timber objects, and
  `|date` accepts `DateTimeImmutable`.
- Read each field in one formatted mode per request. ACF shares its format cache
  between normal and transformed reads, so avoid `get_field()` on a field the
  theme also reads through `meta()`.
