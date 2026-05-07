# Database & Authorization Architecture (OmniReferral)

This document matches the migrations and application code in this repository. Use it for deployment ordering and future refactors.

## P0 — Critical security & integrity

- **Users are master for auth/account** (`users`: credentials, status, workspace enum `role`, billing pointers).
- **Spatie is the authorization source** for fine-grained abilities; policies use `$user->can('…')` with legacy `isStaff()` / `isAgent()` fallbacks until fully migrated.
- **`realtor_profiles` is agent-only 1:1**: enforced by `UserObserver` (creates profile when `role === agent`, deletes when demoted) + DB unique `user_id`.
- **Public agents**: `RealtorProfile::scopePublicDirectory()` — `approved_at` + user `role=agent` + `status=active`.
- **Property listing identity**: `PropertyListingIdentityService` runs on `Property::saved` so `listed_by_id` always matches `realtor_profiles.user_id` when `realtor_profile_id` is set. `owner_user_id` remains the listing owner (often seller).

## P1 — Business logic & FKs

- **Leads ↔ properties**: migration `2026_05_08_100000_add_property_id_to_leads_table` adds nullable `property_id` FK; `Lead::property()` relationship.
- **Lead matches**: DB unique `(lead_id, agent_id)`; validate agent is an agent user in forms/policies.
- **Contacts**: column `role` renamed to `sender_role` to avoid confusion with `users.role` (migration `2026_05_08_100200_…`).
- **Webhooks**: optional correlation via `webhook_events.related_type` / `related_id`.
- **Audit split**: use `App\Support\ActivityLogger` — `domain()` → `audit_logs`, `adminUi()` → `admin_activity_logs`.

## P2 — Cleanup

- Deprecate `leads.property_image` after populating `property_id` and using property gallery.
- Consolidate `users.affiliate_code` with `affiliate_profiles.referral_code` (see `User::referralAffiliateCode()`).
- Rename remaining scattered `role` checks to `@can` / policies over several releases.

## Safe migration order (production)

1. Backup database.
2. Deploy code that supports **both** old and new columns (already done for contacts sender_role / dual policy paths).
3. `php artisan migrate` — runs new migrations only.
4. Optional data SQL (below) in maintenance window.
5. `php artisan optimize:clear` then `config:cache`, `route:cache`, `view:cache`.
6. Smoke-test login, listing create, lead assign, enquiry reply.

## Rollback strategy

- New migrations include `down()` where safe. For rename migrations, restore backup if rename fails (SQLite renaming may require `doctrine/dbal` on older stacks).

## Data cleanup SQL (optional, run after backup)

```sql
-- Orphan realtor profiles for non-agents (should be zero if UserObserver ran)
DELETE rp FROM realtor_profiles rp
INNER JOIN users u ON u.id = rp.user_id
WHERE u.role <> 'agent';

-- Align affiliate referral codes from legacy users.affiliate_code
UPDATE affiliate_profiles ap
INNER JOIN users u ON u.id = ap.user_id
SET ap.referral_code = COALESCE(NULLIF(TRIM(ap.referral_code), ''), u.affiliate_code)
WHERE u.affiliate_code IS NOT NULL AND u.affiliate_code <> '';
```

## Relationship map (ideal)

See the main project README / product spec: **users** 1:1 **realtor_profiles** (agents only); **users** 1:1 **affiliate_profiles**; **properties** N:1 **users** (owner, listed_by), N:1 **realtor_profiles**; **leads** N:1 **users** (assignee), N:1 **properties** (optional); **enquiries** N:1 **properties**, 0..1 **contacts** (unique `contact_id` if legacy 1:1 preserved).
