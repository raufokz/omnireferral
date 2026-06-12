# Agent Growth Directory (OmniReferral)

The platform is a **massive agent directory** optimized for SEO, lead capture, and Featured placement upsells — not agent verification.

## Profile status

| Status | Public visibility |
|--------|-------------------|
| `draft` | Hidden |
| `published` | Visible (default for new staff-created profiles) |
| `featured` | Visible + priority sort + ⭐ badge |

Public visibility uses **`profile_status` only**. Legacy `approved_at` / `rejected_at` columns are not used for directory display.

## Staff acquisition flow

Agents are **not** self-registered. ISA, Sales, Marketing, and Admin staff create profiles at:

- `GET /admin/agent-profiles/create`
- `POST /admin/agent-profiles`

Staff can source agents from Zillow, Realtor.com, social media, brokerage sites, etc. Each profile creates:

1. A `users` row (`role=agent`, internal email optional)
2. A linked `realtor_profiles` row (`user_id` required)

## Public routes

| Route | Purpose |
|-------|---------|
| `/agents` | Main directory |
| `/agents/{location}` | State or city SEO page (e.g. `texas`, `dallas`) |
| `/agent/{slug}` | Indexable SEO profile page |
| `/agent/{slug}/preview` | JSON for modal popup |
| `/agent/{slug}/inquiry` | Centralized lead capture |

Card clicks open a **modal popup** (not a separate navigation). SEO pages still exist for crawlers.

## Sorting

Featured first → highest rating → newest.

## Contact privacy

Agent **email and phone are never shown** on cards, modals, or public SEO pages.

## Lead routing

`Contact Agent` and `Request Referral` submit to **OmniReferral admin**:

- `contacts.recipient_user_id = NULL`
- `leads.source = agent_directory`
- `form_data` includes target agent profile metadata

## Featured placement

Profiles become `featured` when:

1. Admin marks featured manually, or
2. Linked user has an active paid plan (backfill migration)

Sell upgrades on `/pricing` (Featured vs Free comparison).

## Headshot fallback

1. Profile headshot  
2. User avatar  
3. OmniReferral logo (`images/omnireferral-logo.png`)

## Seeding

`OmniReferralSeeder` inserts users first, then profiles with `profile_status` published/featured.
