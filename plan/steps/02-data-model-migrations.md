# Step 02 – Datenmodell & Migrationen

## Ziel
Tabellen und Eloquent-Models für MVP: `workspaces`, `profiles`, `links`, `themes`, `click_events`, `event_logs`; Cashier-Tabellen via Migration.

## Entitäten
- **Workspace**: owner `user_id`, `name`, `plan` (enum/string)
- **Profile**: `workspace_id`, `slug` (unique), `display_name`, `bio`, `avatar_path`, `theme_id`, `theme_variables` (json), `is_published`, `published_at`
- **Link**: `profile_id`, `title`, `url`, `position`, `is_active`, `opens_in_new_tab`, `tracking_enabled`
- **Theme**: `name`, `slug`, `variables` (json), `preview_image_path` optional
- **ClickEvent**: `link_id`, `profile_id`, `session_id`, `ip_hash`, `user_agent`, `country`, `created_at`
- **EventLog**: `workspace_id`, `event_type`, `payload` (json)

## Abnahme
- `php artisan migrate:fresh --seed` (Theme-Seeder)
- Factories für Profile, Link
