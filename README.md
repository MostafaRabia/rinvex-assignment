# Rinvex Assignment — Filament Skill Resource

This repository implements the “Senior Full‑Stack Filament Assignment” requirements using Laravel, Filament, Livewire, and Spatie Media Library. The main domain is a Skill resource with rich form, listing, actions, and tests that reach 100% coverage for Skill-related code.

## Features

- **Skill Resource (CRUD):** Filament pages for list, create, view, and edit.
- **Table Columns:** Name, category, proficiency level (avg summary), active toggle, timestamps.
- **Filters:** Category select, proficiency-from text filter, ternary active filter, grouping by category/proficiency.
- **Actions:**
  - Record actions: View, Edit, Archive (only visible when active, sets `is_active=false`).
  - Bulk actions: Delete, Archive selected (sets `is_active=false`).
  - Header action: Seed skills from external API (`dummyjson`) with success/failure notifications.
- **Form UX:** Wizard with conditional fields (proficiency required and visible only when `category=technical`), markdown description, media attachments, tags repeater, notes with live counter and 200 max length.
- **Infolist UX:** Conditional visibility for proficiency level, boolean icon for active, humanized timestamps, markdown description, attachments gallery.
- **Media & Tags:** Integrated Spatie Media Library uploads and structured tags/notes.
- **Testing:** Comprehensive Pest tests for forms, business rules, table filters/search, per-record/bulk actions, and seeding logic.

## Tech Stack

- Laravel 11
- Filament (Resources, Tables, Forms, Infolists, Actions)
- Livewire
- Spatie Media Library
- PestPHP

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- A database (SQLite/MySQL/PostgreSQL). Default `.env` can use SQLite.

### Installation

1. Install PHP dependencies:
	```bash
	composer install
	```
2. Copy environment and generate key:
	```bash
	cp .env.example .env
	php artisan key:generate
	```
3. Configure database in `.env` (for SQLite):
	```bash
	touch database/database.sqlite
	echo "DB_CONNECTION=sqlite" >> .env
	```
	Or set `DB_CONNECTION`, `DB_DATABASE`, etc. for MySQL/PostgreSQL.
4. Run migrations:
	```bash
	php artisan migrate
	```

### Running the App

```bash
php artisan serve
```

Filament admin panel pages for Skills are available under the cluster `Settings`. Ensure you have an authenticated user (create via factory or registration) to access actions that dispatch notifications to the current user.

## Usage Notes

- Proficiency level is only applicable when `category=technical` and must be between 1 and 5.
- Archive action is only visible for active skills and sets `is_active=false`.
- Seeding header action fetches 10 items from `dummyjson` and creates new skills, skipping duplicates by `name`.

## Testing & Coverage

Run only Skill tests:
```bash
php artisan test --filter=SkillTest
```

Run full test suite:
```bash
php artisan test
```

Coverage (Skill resource): Tests in `tests/Feature/SkillTest.php` cover page loading, form visibility/validation, business rules, table filters/search, record and bulk actions, and seeding success/failure + duplicate skipping.

## Project Structure Highlights

- `app/Models/Skill.php`: Eloquent model with casts and media support.
- `app/Filament/Clusters/Settings/SkillResource.php`: Resource wiring.
- `app/Filament/Clusters/Settings/Pages/*`: List, Create, View, Edit pages.
- `app/Filament/Clusters/Settings/Tables/SkillsTable.php`: Table schema, filters, actions, and `seedSkills()`.
- `app/Filament/Clusters/Settings/Schemas/SkillForm.php`: Wizard form schema.
- `app/Filament/Clusters/Settings/Schemas/SkillInfolist.php`: Infolist schema.
- `database/factories/SkillFactory.php`: Factory for tests and seeding.
- `tests/Feature/SkillTest.php`: Comprehensive Pest tests.

## Notes on Assignment Compliance

- Implements Filament resource with advanced table/form/infolist features.
- Adds realistic actions (record, bulk, header) with notifications.
- Enforces validation and business rules in UI and tests.
- External data fetch integrated and fully testable via HTTP fakes.

## License

MIT
