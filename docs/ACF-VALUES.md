# ACF values and consuming-theme upgrade notes

Use Timber's existing per-call bridge for presentation fields. PressGang adds
no wrapper, automatic global filter, configuration or dependency for this.
Existing themes need no changes on upgrade. ACF remains optional; these ACF
examples require it, while ordinary Timber metadata access still works without it.

```php
$rows = $post->meta( 'flexible_content', [ 'transform_value' => true ] );
```

ACF recursively invokes Timber's replacement formatters, including inside
groups, repeaters and flexible content. Arrays retain their row/layout structure;
object leaves use the existing Timber class maps. Opt in the whole root only
when its consumers expect the transformed types.

## Return types and boundaries

Verified against Timber 2.5.1 and ACF 6.8.9:

| ACF type | Nonempty transformed value |
| --- | --- |
| `post_object` | Timber Post, or PostArrayObject when multiple |
| `relationship` | Timber PostArrayObject |
| `image` | Timber Image |
| `file` | Timber Attachment (or attachment subclass) |
| `gallery` | PostArrayObject containing attachment/image posts |
| `taxonomy` | Timber Term for select/radio; array of terms otherwise |
| `user` | Timber User, or array of users when multiple |
| `date_picker`, `date_time_picker` | DateTimeImmutable |

Object-like fields generally return `false` when empty. Collections are not
native PHP arrays. Check empty/deleted selections and callers' return types
when migrating; do not assume exact parity with the legacy mapper.

ACF's standard taxonomy type is `taxonomy`, not `term`. No custom `term` type
was registered in the audited penarc site. The mapper's historical `term` case
remains unchanged for other consumers.

The switch applies to Timber metadata access, not ordinary `get_field()`,
`get_sub_field()` or options-page reads. Options and block contexts still use
`TimberMapper`. The options cache remains `theme_option_field_objects` in group
`pressgang`, invalidated on `acf/save_post`. It holds ACF-formatted, Timber-unmapped
field objects; it is not an unformatted-ID cache. Nested options remain unchanged.

## Known upstream limitations

Use **one formatted mode per field/entity per request**, across PHP, Twig and
third-party ACF reads. Include nested sub-fields in that audit.

ACF 6.8.9 shares its formatted-value cache between normal ACF and transformed
reads. In live probes, a normal nested-image read followed by a transformed read
returned an ACF image array both times. Reversing the order returned a Timber
Image both times. `transform_value => false` alone does not undo a transformed
read. Normal WP_Post arrays may still become arrays of Timber posts through
Timber's fallback conversion, so successful relationship conversion does not
prove that all nested field types escaped the cache issue.

Timber 2.5.1 also lacks exception-safe restoration of its temporary ACF filters.
A throwing formatter left the original ACF image formatter removed and Timber's
replacement installed. Catching the exception does not make subsequent ACF
reads safe. Avoid reentrant transformed reads inside formatting callbacks;
PressGang does not provide scope management or vendor patches.

Keep the existing explicit mapper when a request needs normal ACF arrays/date
strings as well as Timber objects. These version-specific limitations should be
rechecked on dependency upgrades; this change does not fix them upstream.

## Opting out and reading IDs

Transformation remains off by default. Opt in or out for a selected root at its
`meta()` call. Do not enable the global `timber/meta/transform_value` filter in a
migration: query IDs, date strings and image/file arrays have existing consumers.
There is no per-type or nested-path switch in this policy. Keep a mixed-use root
untransformed and use explicit mapping where necessary.

For normal ACF formatting, use ordinary `meta()` or `transform_value => false`
consistently from the start of the request. For IDs/storage-oriented input that
must coexist with transformed reads, use one of these:

```php
// Unformatted ACF values: disable BOTH flags.
$ids = $post->meta( 'related_events', [
    'transform_value' => false,
    'format_value'    => false,
] );

// Native WordPress storage, bypassing ACF.
$ids = $post->raw_meta( 'related_events' );
```

`format_value => false` alone loses to an enabled transform: Timber calls
`get_field(..., true)` in that path. Storage values are not normal ACF-formatted
values: a raw repeater root can be a row count, and unformatted ACF sub-fields
can be keyed by field keys. Do not substitute either escape into a template
expecting image arrays or formatted dates.

If a child theme already registers a global transform filter, remove that
registration to return to the default. There is no additional PressGang switch.

## Worked penarc migrations

These are opt-in edits for a consuming theme, not changes applied to penarc by
this parent-theme documentation update. Audit all reads of each selected field
first. In the inspected source, these four fields feed presentation getters;
query builders using their names compare the current post ID rather than reading
these field values. Request-wide plugin behaviour still needs a rendered-page check.

The existing shape is:

```php
return TimberMapper::to_timber_posts( $this->get_post()->meta( 'staff_authors' ) );
```

These replacements preserve the getters' native-array contracts. Timber performs
the conversion; the remaining normalization materializes collections, removes
null members for missing targets, and reindexes the result. This matters in the
actual dataset: post 20166's `other_arc_staff` collection contains a null member.
`?: []` handles empty fields, and the array branch also accepts Timber's fallback
conversion of an already formatted WP_Post array. That branch does not fix the
cache problem for other field types.

`src/Traits/HasAuthors.php`:

```php
/**
 * Returns staff authors.
 *
 * @return array<int, \PenArc\Models\StaffMember>
 */
protected function get_staff_authors(): array {
    $posts = $this->get_post()->meta( 'staff_authors', [ 'transform_value' => true ] ) ?: [];
    $posts = is_array( $posts ) ? $posts : iterator_to_array( $posts );

    return array_values( array_filter( $posts ) );
}
```

`src/Traits/HasArticleRelationships.php`:

```php
/**
 * Returns contact staff members.
 *
 * @return array<int, \Timber\Post>
 */
protected function get_contact_staff_members(): array {
    $posts = $this->get_post()->meta( 'contact_staff_members', [ 'transform_value' => true ] ) ?: [];
    $posts = is_array( $posts ) ? $posts : iterator_to_array( $posts );

    return array_values( array_filter( $posts ) );
}
```

`src/Controllers/ResearchProjectController.php`:

```php
/**
 * Returns other ARC staff.
 *
 * @return array<int, \Timber\Post>
 */
protected function get_other_arc_staff(): array {
    $posts = $this->get_post()->meta( 'other_arc_staff', [ 'transform_value' => true ] ) ?: [];
    $posts = is_array( $posts ) ? $posts : iterator_to_array( $posts );

    return array_values( array_filter( $posts ) );
}

/**
 * Returns ARC lead.
 *
 * @return array<int, \Timber\Post>
 */
protected function get_arc_lead(): array {
    $posts = $this->get_post()->meta( 'arc_lead', [ 'transform_value' => true ] ) ?: [];
    $posts = is_array( $posts ) ? $posts : iterator_to_array( $posts );

    return array_values( array_filter( $posts ) );
}
```

Remove the unused `use PressGang\ACF\TimberMapper;` import from each edited file.
Do not pass the transformed collection back into `to_timber_posts()`: that helper
accepts native arrays and otherwise returns an empty array. It remains supported,
with its existing filtering and reindexing behaviour, for unmigrated consumers.

These examples were compared with the existing helper on live selections:
`staff_authors` on post 23295, `contact_staff_members` on 10460,
`other_arc_staff` on 20166 and `arc_lead` on 20169. All four preserved the
selected IDs and order after normalization. The comparison isolated the ACF
value cache between modes; it does not demonstrate safe mixed-mode reads.

If callers accept collections and selections cannot contain missing targets,
a direct transformed read with `?: []` and an `iterable` return type can omit
normalization. That is a deliberate contract change, not the compatibility
example above. Keeping the existing mapper is also a supported choice.

For an audited nested presentation root, Twig can use the same upstream argument:

```twig
{% set rows = post.meta('flexible_content', { transform_value: true }) %}
```

All supported descendants transform, including dates and files. Do not turn this
on until the macros receiving those rows expect those types.

## Evidence and performance

Live WP-CLI probes on penarc (PHP 8.3.8, Timber 2.5.1, ACF 6.8.9) demonstrated:

| Read | Result |
| --- | --- |
| Post 17, `flexible_content[2].people` | PostArrayObject, 9 StaffMember objects |
| Post 65513, `flexible_content[0].cards[0].image` | Timber Image, ID 49660 |
| Post 20184, `lead_collaborators[0].partner_organisation` | Timber Term, ID 3345 |
| Post 66610, `themes` | `false`; stored fixture is empty |
| Existing `themes` schema with an in-memory row | PostArrayObject containing ResearchProject 20184 |
| In-memory group containing relationship/date | PostArrayObject and DateTimeImmutable |

Independent probes started with a fresh request-local ACF value store; the
in-memory fixtures did not write content. Do not clear ACF's caches in application
code as a workaround.

The database contained 1,524 published publications and 46 events. Three fields
on 24 publications (72 calls) took a median 1.799 ms for warm normal ACF reads,
versus 3.623 ms transformed. Across all publications (4,572 calls), warm times
were 118.715 ms versus 225.346 ms. Both warm modes issued zero additional SQL.
Timber performs 9 field-type lookups and 36 filter mutation calls for each
transformed read, including scalar fields and cache hits.

A cold source-post read of a 27-item relationship used 2 queries with normal ACF
versus 28 transformed; iterating the transformed collection added 6 queries.
The numeric-ID path eagerly creates posts. Source-post metadata priming alone
does not eliminate related-object N+1 queries. Transform only fields needed for
the displayed page and measure rendered listings, not just warmed CLI loops.

See [Timber's ACF documentation](https://timber.github.io/docs/v2/integrations/advanced-custom-fields/)
for the upstream API. The runtime findings above qualify its per-call opt-out
advice for the versions tested.
