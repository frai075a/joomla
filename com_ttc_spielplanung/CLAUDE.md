# com_ttc_spielplanung

Joomla 5 MVC component ("Spielplanung" — team roster and match-availability management for TTC Nordend Frankfurt). Namespace: `Ttc\Component\Spielplanung`.

## Layout

- `administrator/` — backend: manage relevant categories (`kategorien` view), team roster / captain assignment (`mmb` view), and a read-only cross-team season overview (`saisonplanung` view — all teams, all games, confirmed players per game, ordered by roster position).
- `site/` — frontend: logged-in players manage their own match availability (`spielplan` view, "Meine Spiele") and view their own team's full season with per-game confirmed-player lists (`saisonplanung` view, "Saisonplanung", read-only, scoped to the logged-in user's team).
- Frontend `saisonplanungmf` ("Saisonplanung managen (MF)") allows active roster captains to manage availability for players from the same or numerically higher active team. Display and save game scope always remains the logged-in captain's team. `SaisonplanungmfModel` extends the existing availability writer with an authorized player lookup; user_id identifies the selected player, while created_by/modified_by identify the logged-in actor. Menu metadata lives in site/tmpl/saisonplanungmf/default.xml. **It overrides `saveAndNotify()` to skip `CaptainNotificationService` entirely** — captain-mail notification is deliberately only for a player's own "Meine Spiele" change (`SpielplanModel::saveAndNotify()`), not for a captain editing on someone else's behalf.
- Site and admin season views keep separate models/views/templates. Shared read queries live in administrator/src/Repository/SpielplanungRepository.php: match details/opponent, active-user team scope, confirmed-player ordering, and relevant categories. Backend ListModel retains pagination/filter state; site models retain their user scope and return formats.
- Both sides use the shared repository through Joomla's Administrator namespace autoload mapping. Pass the model's database connection to its constructor; the repository does not access Factory/current-user state. Use createUserGamesQuery for frontend display and save validation, and createGamesQuery only for backend lists or explicit further restriction. Language files remain separate.
- All three frontend dialogs (`spielplan`, `saisonplanung`, `saisonplanungmf`) share the same two GET filters, both read in each view's `display()` and threaded through `getGames($onlyFuture, $vorrundeOnly)` down to `SpielplanungRepository::createUserGamesQuery()`: "Nur zukünftige Spiele" (`only_future`, gated by the `only_future_submitted` hidden field so an unsubmitted form still defaults to true) and "Vorrunde" (`vorrunde`, no submitted-guard — unchecked simply means false). Add any future shared frontend filter the same way: repository method parameter first, then thread it through all three Models/Views/Controllers/templates together, never just one dialog.
- The three filter forms render inside a shared `.com-ttc-spielplanung-filter-box` bordered/boxed container (styled inline per template, same CSS values duplicated in each — there's no shared frontend stylesheet to put it in once). Keep new frontend filter checkboxes inside that box, consistent with the existing ones.
- Only `en-GB` language exists; it is also the UI's *German* text (label values are German, e.g. `COM_TTC_SPIELPLANUNG_FIELD_CAPTAIN="Mannschaftsführer"`). There is no separate `de-DE` file — don't create one unless asked.

## Domain model

- `#__ttc_relevante_kategorien` — join table: Joomla `#__categories.id` (`category_id`) ↔ a team's `sort_order` (matches `#__ttc_spielplan.mannschaft`). Defines which Joomla categories are "relevant" teams for this club, with a display order and `state` flag.
- `#__ttc_mmb` ("Mannschaftsmeldebogen" — team roster) — one row per `user_id`, links a user to at most one team (`category_id`, nullable = unassigned), an in-team `position` (1–99, unique ordering managed via `MmbModel::updatePosition`/`shiftPositions`), and `is_captain` (added in 1.0.5). **At most one captain per `category_id`** — enforced server-side in `MmbModel::saveRows()` by clearing `is_captain` on every other user in that category when a row is saved with the flag set; the frontend JS in `tmpl/mmb/default.php` mirrors this for UX but is not authoritative.
- `#__ttc_spielplanung` — one row per `(user_id, game_id)`: a player's Zusage/Absage (`status`: 1 = yes, 0 = no) for a specific match.
- `#__ttc_spielplan` — **not owned by this component.** It belongs to `com_spielplan` (see sibling directory). Read-only joins here (`sp.mannschaft`, `sp.datum`, `sp.heimmannschaft`/`sp.auswaertsmannschaft`, `sp.ort`). Never add it to this component's install/uninstall SQL.
- `#__ttc_hinrueckgrenze` — **not owned by this component either** (no install/uninstall SQL here for it; it pre-exists in the DB). Single column `datum` (DATE), one row per season/history; the *current* Vor-/Rückrunde cutoff is always `MAX(datum)`, never the newest-inserted row by id. Used only to implement the "Vorrunde" filter (`sp.datum <= MAX(datum)`).

## Conventions observed in this codebase

- **DB schema changes**: bump `<version>` in `com_ttc_spielplanung.xml`, add the column to `administrator/sql/install.mysql.utf8.sql` (fresh installs) **and** a new `administrator/sql/updates/<version>.sql` file (upgrades) — Joomla's `<update><schemas>` mechanism runs whichever update files are newer than the installed schema version. Both must be kept in sync by hand; there's no migration-generation tooling. `administrator/sql/updates/` is currently empty (only `index.html`) — the next update file starts a fresh sequence, it doesn't continue old 1.0.x numbering.
- **`script.php`'s `postflight()` is a second, idempotent safety net** for schema drift, independent of `sql/updates/`: on every install/update/discover_install it checks `#__ttc_mmb` for `is_captain` via `getTableColumns()` and `ALTER TABLE`s it in if missing, to repair installs that went through a previously-broken upgrade path. It deliberately lets `$db->execute()` failures bubble up rather than swallowing them. If you add a column that must survive a broken-upgrade scenario the same way, extend this method's column check rather than relying on `sql/updates/` alone.
- **Language keys**: always add a new `COM_TTC_SPIELPLANUNG_*` key rather than reusing Joomla core keys (`JGLOBAL_NAME`, `JGLOBAL_SELECT`, …) for anything that might need component-specific wording later — this was a deliberate ask (see `COM_TTC_SPIELPLANUNG_FIELD_PLAYER`, `COM_TTC_SPIELPLANUNG_SELECT_MANNSCHAFTSZUORDNUNG`).
- **Mmb (`administrator/tmpl/mmb/default.php`) is not a JForm edit dialog** — it's a single-page inline-edit grid: one `<form>` posts all rows at once to `mmb.save`, keyed as `mmb[<user_id>][field]`. There is no per-row modal/JForm XML for this view. Don't go looking for one.
- **Frontend mail**: SpielplanModel::saveAndNotify() commits availability using saveGames() and then invokes site/src/Service/CaptainNotificationService.php. The service groups changes per captain, formats/sends mail, and returns warnings. Lookup/send failures must never undo a successful save. Controllers display the returned warnings. `SaisonplanungmfModel::saveAndNotify()` overrides this to call only `saveGames()`, never the notifier — don't "fix" that override to match the parent, it's intentional (see Layout).
- Controllers handle tokens, permissions, request decoding, messages, and redirects. Business writes/transactions live in models: KategorienModel::saveSelection(), MmbModel::saveRows()/movePosition()/updatePosition(), and SpielplanModel::saveAndNotify()/saveGames(). Models enforce authorization for direct callers as well.

## Regression tests

- `tests/regression.php` is a standalone PHP script (not PHPUnit) — run with `php -d extension=pdo_sqlite tests/regression.php`. It declares double/fake versions of the Joomla classes it needs (`Factory`, `BaseController`, `AdminController`, `HtmlView`, `Text`, `HTMLHelper`, `Route`, a minimal query builder, mailer double) in their real namespaces, then `require`s the actual component source files so production code runs unmodified against an in-memory SQLite database.
- When you add or change a model/controller/view, check whether it's in the `require` list near the top of the `namespace {}` block and add it if it now has behavior worth covering — the existing coverage spans Saisonplanung (both sides), Mmb, CaptainNotificationService, Spielplan, Saisonplanungmf, Kategorien, and `script.php`.
- The mailer double supports forcing a failure mode (`Factory::$mailFailure = 'throw'|'false'`) specifically to exercise CaptainNotificationService's warning path without a real SMTP server.

## Backend entry maintenance (1.5.0)

- `eintraege` lists every `#__ttc_spielplanung` record with pagination, player and match names, and audit metadata in row tooltips. LEFT JOINs preserve orphaned records.
- `eintraege.remove` and `eintraege.removeAll` are POST actions protected by CSRF tokens. Models require both `core.manage` and `core.delete`. They delete availability records only; users, games and roster tables remain untouched. No notification mail is sent.
- The delete-all action applies to the entire availability table, regardless of the current page; the UI explicitly confirms this scope.

- Since 1.5.2, `eintraege` offers an exact player-ID dropdown (including orphaned users) and allowlisted player/match/date sorting in both directions. Filter/sort selections use component model state, are part of the list cache key and are carried in pagination submissions. Applying selections resets pagination. Delete-all still affects the entire table.

## Three-state availability (1.6.0)

- Both editing dialogs use `site/tmpl/status.php`: Absage (0), Neutral (SQL NULL, center), Zusage (1). Missing availability defaults to neutral. Existing 0/1 rows are preserved on upgrade.
- HTTP submits the explicit token `neutral`; controllers map only that token to PHP null. Missing fields remain invalid. Models accept null, 0/1 and their string representations. SQL uses unquoted NULL, and change detection keeps NULL distinct from 0. Neutral to neutral sends no mail; personal changes to/from neutral do. Captain edits still never send mail.
- Install SQL and 1.6.0 migration set status nullable with DEFAULT NULL. postflight also repairs the definition when needed. Only status=1 contributes to confirmed-player lists.

## Season initialization (1.7.0)

- Neutral availability is labelled `offen`; HTTP token and SQL NULL semantics are unchanged.
- `saisoninitialisieren` checks for exactly December 31 of the current year in `#__ttc_hinrueckgrenze`. The year follows the Joomla site timezone and is recomputed on POST. The button label is `Saison YYYY/YYYY+1` and disappears when the row exists.
- The initialize action requires CSRF, core.manage and core.create. It only inserts the date into the externally owned table; no table creation, deletion or schema ownership is added. Prior dates remain unchanged. A MySQL advisory lock serializes requests from this dialog, with a fresh existence check under the lock.
