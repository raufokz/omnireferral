# OmniReferral

OmniReferral is a modern Laravel-based real estate referral platform for buyers, sellers, agents, and internal teams. This foundation includes a branded public website, pricing comparison, lead capture, agent directory, blog pages, contact and survey pages, sample dashboards, seeded listings, and a scalable data model for future expansion.

## Stack

- Laravel 12
- PHP 8.2+
- SQLite for local development
- Vite for asset bundling
- Blade templates, custom CSS, and vanilla JavaScript interactions

## Included Foundation

- Human-centered homepage with buyer and seller lead capture tabs
- How It Works workflow for ISA, Sales, Marketing, and Web teams
- Pricing page with Quick, Power, Prime, and VA package cards
- Agent directory and agent profile pages
- Blog, reviews, resources, careers, news, FAQ, privacy, and terms pages
- Contact page with embedded Google Maps iframe and GoHighLevel survey embed
- Seeded packages, testimonials, team members, listings, agents, and leads
- Simplified dashboard and admin overview pages

## Local Setup

1. Copy `.env.example` to `.env`
2. Create the SQLite database file if needed:
   - `New-Item -ItemType File database\database.sqlite`
3. Install PHP dependencies:
   - `composer install`
4. Install frontend dependencies:
   - `npm install`
5. Generate the app key:
   - `php artisan key:generate`
6. Run migrations and seed data:
   - `php artisan migrate:fresh --seed`
7. Build frontend assets:
   - `npm run build`
8. Start the local server:
   - `php artisan serve --host=127.0.0.1 --port=8001`

## Important Notes

This build provides a strong production-style foundation, but several enterprise integrations are represented as ready-to-wire placeholders rather than fully live connections:

- Google Maps API key integration
- GoHighLevel authenticated API workflows
- Stripe and PayPal checkout flows
- Google and Facebook social authentication
- Full role-protected auth dashboards and messaging workflows
- Zillow, Redfin, and Jet enrichment APIs

## Verified Commands

- `php artisan migrate:fresh --seed`
- `npm run build`
- `php artisan test`
- `php artisan route:list`
