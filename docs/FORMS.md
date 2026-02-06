# Forms and Validation

PressGang provides a structured form handling pipeline with built-in validation, CSRF protection, and error handling. No more tangled `$_POST` processing scattered across your theme — PressGang keeps your forms battened down and secure.

## Architecture

```
Form submit → WordPress admin-post → FormSubmission::handle_form_submission()
                                        ├── Nonce verification
                                        ├── Input flashing
                                        ├── Validator pipeline
                                        ├── process_submission() (your logic)
                                        └── Redirect with status
```

## FormSubmission (Base Class)

The abstract `FormSubmission` class handles the form lifecycle:

1. **Nonce verification** — rejects requests with invalid or missing nonces.
2. **Input flashing** — sanitises and stores submitted values in the session (via `Flash`), so forms can be repopulated after validation errors.
3. **Validation** — runs all configured validators and collects errors.
4. **Processing** — calls your `process_submission()` implementation on success.
5. **Redirect** — sends the user back to the referring page with success/error flags.

### Creating a Form Handler

Extend `FormSubmission` and implement `process_submission()`:

```php
namespace MyTheme\Forms;

use PressGang\Forms\FormSubmission;

class NewsletterSubmission extends FormSubmission {

    protected function process_submission(): void {
        $email = sanitize_email($_POST['email'] ?? '');

        // Your newsletter subscription logic here
        subscribe_to_newsletter($email);
    }
}
```

### Initialising and Registering Hooks

Form handlers register themselves with WordPress via `admin_post` actions:

```php
// In a snippet or functions.php
use MyTheme\Forms\NewsletterSubmission;

NewsletterSubmission::init([
    'action' => 'newsletter_signup',
    'validators' => [
        new \PressGang\Forms\Validators\EmailValidator(['email']),
    ],
]);
```

This registers handlers for both logged-in (`admin_post_{action}`) and logged-out (`admin_post_nopriv_{action}`) users.

## Built-in: ContactSubmission

PressGang ships with a `ContactSubmission` class that handles contact form emails out of the box:

* Sends email via `wp_mail()` to the site admin.
* Supports optional Twig templates for email formatting.
* Configurable success/error messages.
* Filterable recipient via `pressgang_contact_to_email`.
* Filterable subject via `pressgang_contact_subject`.

```php
use PressGang\Forms\ContactSubmission;

ContactSubmission::init([
    'action' => 'contact_form',
    'template' => 'emails/contact.twig',  // optional
    'success_message' => __("Thanks for your message!", THEMENAME),
    'validators' => [
        new \PressGang\Forms\Validators\EmailValidator(),
        new \PressGang\Forms\Validators\MessageValidator(),
        new \PressGang\Forms\Validators\RecaptchaValidator(),
    ],
]);
```

## Validators

Validators implement the `ValidatorInterface`:

```php
namespace PressGang\Forms\Validators;

interface ValidatorInterface {
    public function validate(): array;
}
```

The `validate()` method returns an empty array on success, or an array of error messages on failure.

### Built-in Validators

| Validator | Purpose |
|---|---|
| `EmailValidator` | Validates that a submitted email address is well-formed |
| `MessageValidator` | Validates that a message field is not empty |
| `RecaptchaValidator` | Validates a Google reCAPTCHA response |

### Creating a Custom Validator

```php
namespace MyTheme\Forms\Validators;

use PressGang\Forms\Validators\ValidatorInterface;

class PhoneValidator implements ValidatorInterface {

    public function validate(): array {
        $phone = sanitize_text_field($_POST['phone'] ?? '');

        if (empty($phone) || !preg_match('/^\+?[\d\s\-()]+$/', $phone)) {
            return [__('Please provide a valid phone number.', THEMENAME)];
        }

        return [];
    }
}
```

## Form Template (Twig)

Your Twig form template must include a nonce field and target the `admin_post` endpoint:

```twig
<form method="post" action="{{ site.url }}/wp-admin/admin-post.php">
    <input type="hidden" name="action" value="contact_form">
    {{ function('wp_nonce_field', 'contact_form', '_wpnonce', true, false) }}

    <input type="email" name="contact[email]" value="{{ flash('contact.email') }}">
    <textarea name="contact[message]">{{ flash('contact.message') }}</textarea>

    <button type="submit">{{ __('Send', THEMENAME) }}</button>
</form>
```

## Security

{% hint style="warning" %}
All PressGang forms enforce WordPress security conventions — nonce verification, input sanitisation, and capability checks are mandatory. Never trust raw user input!
{% endhint %}

* Nonce validation is automatic — handled by `FormSubmission::handle_form_submission()`.
* All input should be sanitised using `sanitize_text_field()`, `sanitize_email()`, etc.
* Validation logic must live in validators, not in controllers.
* Controllers may only consume validated data — they must never process form submissions directly.
