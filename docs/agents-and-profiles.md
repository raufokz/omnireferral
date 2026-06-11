# Users + Agent Profiles (OmniReferral)

This document describes how workspace users, agent profiles, public directory visibility, and admin approval work together.

## Data model

```
users.id  ──1:1──>  realtor_profiles.user_id  (agents only)
```

- Every `realtor_profiles` row **must** reference a valid `users.id` (FK, not nullable).
- Only users with `role = agent` should have a profile (`UserObserver` enforces this).
- The workspace role enum is `buyer | seller | agent | admin | staff`. There is **no** `realtor` role — normalize legacy `realtor` values to `agent` using `database/repair/agent-profile-repair.sql`.

## Two-layer approval

| Layer | Column | Effect |
|-------|--------|--------|
| Account | `users.status` | `pending` users cannot sign in; `active` users can access the agent portal |
| Public profile | `realtor_profiles.approved_at` | Controls directory listing and `/agents/{slug}` visibility |

Approving an **account** (`POST admin/users/{user}/review`) does not publish the public profile. Use **Agent Profiles** in admin (`/admin/agent-profiles`) to approve or reject the profile itself.

When a profile is **approved**:

- `users.status = active`
- `realtor_profiles.approved_at = now()`
- `realtor_profiles.approved_by_user_id = admin id`
- `rejected_at` / `rejected_by_user_id` cleared

When **rejected**:

- `rejected_at = now()`, `approved_at = null`
- Linked user stays `pending` or can be suspended

## Public directory rules

Agents appear in `/agents` and the sitemap only when `RealtorProfile::scopePublicEligible()` passes:

- `users.status = active`
- `users.role = agent`
- Valid `realtor_profiles.user_id`
- `approved_at IS NOT NULL`
- `rejected_at IS NULL`
- Non-empty `service_city`, `service_state`, `bio`
- `rating >= 3.0`

## Agent onboarding

Public application: **`GET /join-as-agent`**

On submit the app:

1. Creates `users` (`role=agent`, `status=pending`)
2. Creates `realtor_profiles` linked by `user_id`
3. Generates a unique slug
4. Stores headshot upload or default avatar
5. Sets `approval_notes = "Pending admin review"`
6. Notifies active admin users by email
7. Redirects to `/join-as-agent/success`

Rate limit: `throttle:agent-join` (3/min per IP). Honeypot field: `company_website`.

## Headshot fallback

Use `App\Support\AgentAvatar::url($user, $profile)` or `@include('partials.agent-avatar')`:

1. Profile headshot
2. User avatar (`users.avatar`)
3. Default: `public/assets/images/default-agent-avatar.svg`

## Seeding

`OmniReferralSeeder` inserts **users first**, then `realtor_profiles` with matching `user_id`:

- `role = agent`, `status = active`
- `approved_at` set for demo directory data
- `rating >= 3.0`, bio ≥ 80 characters
- Default headshot when image assets are missing

Never insert `realtor_profiles` with `NULL user_id`.

## Database repair

Run after backup:

```bash
mysql -u USER -p DATABASE < database/repair/agent-profile-repair.sql
```

The script normalizes roles, quarantines orphan profiles, fixes duplicate slugs, fills incomplete data, and approves eligible profiles.

## Admin routes

| Route | Purpose |
|-------|---------|
| `GET /admin/agent-profiles` | Pending / approved / rejected queues |
| `GET /admin/agent-profiles/{id}` | Review, edit, approve, reject |
| `POST .../approve` | Publish profile + activate user |
| `POST .../reject` | Reject with notes |

Permissions: `realtor_profiles.view`, `.update`, `.approve`, `.reject` (Spatie).

## Model scopes

**User:** `agents()`, `active()`, `withApprovedProfile()`

**RealtorProfile:** `approved()`, `notRejected()`, `complete()`, `ratingAtLeast()`, `publicEligible()`, `pendingReview()`
