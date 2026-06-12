# TODO
- [ ] Verify whether `resources/css/modules/agent-directory.css` exists.
- [ ] If missing: create `resources/css/modules/agent-directory.css`.
- [ ] Update `vite.config.js` to include `resources/css/modules/agent-directory.css` in the `laravel({ input: [...] })` array.
- [ ] Confirm `resources/views/pages/agents.blade.php` uses `@vite('resources/css/modules/agent-directory.css')` (or remove if not needed).
- [ ] Run build/test steps: `npm install`, `npm run build`, `php artisan optimize:clear`, `php artisan view:clear`, `php artisan cache:clear`.
- [ ] Test `/agents` route loads without ViteException.
