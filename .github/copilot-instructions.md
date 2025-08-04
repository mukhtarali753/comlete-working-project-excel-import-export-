# Copilot Instructions for themes-subthemes-boards-stages-leads

## Project Overview
This is a Laravel-based application for managing themes, subthemes, boards, stages, and leads. The codebase uses standard Laravel conventions for controllers, models, migrations, and views, but has some project-specific patterns and workflows.

## Key Components
- **app/Models/**: Eloquent models for core entities (e.g., `Business`, `Theme`, `Board`, etc.).
- **app/Http/Controllers/**: Resource controllers for CRUD and business logic. Controllers often use resourceful routes.
- **database/migrations/**: Migration files define table schemas. Recent changes may reduce columns to only essentials (e.g., `file_name`, `user_id` for `businesses`).
- **resources/views/**: Blade templates for UI. Preview and Excel views are common for entities.
- **routes/web.php**: Route definitions, typically using `Route::resource` for RESTful endpoints.

## Data Flow & Patterns
- Most entities follow a standard CRUD pattern: migration → model → controller → Blade view.
- Controllers validate requests, interact with models, and return JSON or Blade views.
- AJAX is used in Blade files for create, update, and delete actions.
- Only essential fields are kept in migrations and models (e.g., `Business` now only has `file_name` and `user_id`).

## Developer Workflows
- **Migrations**: Run `php artisan migrate` to apply schema changes.
- **Development Server**: Use `php artisan serve` to start the app.
- **Testing**: Tests are in `tests/Feature` and `tests/Unit`. Run with `php artisan test`.
- **Asset Compilation**: Use Laravel Mix (`npm run dev` or `npm run prod`).

## Project-Specific Conventions
- Remove unused columns from migrations and models as requirements change.
- Use resourceful controllers and routes for all main entities.
- AJAX in Blade views expects JSON responses from controllers.
- Foreign keys (e.g., `user_id`) reference the `users` table and use `onDelete('cascade')`.

## Integration Points
- Uses standard Laravel packages and Eloquent relationships.
- May use third-party JS libraries in Blade views (e.g., Toastr, Luckysheet, XLSX).

## Examples
- See `app/Models/Business.php` and its migration for minimal model pattern.
- See `app/Http/Controllers/BusinessController.php` for resourceful controller with AJAX support.
- See `resources/views/businesses/preview.blade.php` for AJAX-driven UI.

---
If you are unsure about a workflow or pattern, check the relevant controller, model, and migration for the entity you are working on. When in doubt, follow Laravel conventions unless a project-specific pattern is documented here.
