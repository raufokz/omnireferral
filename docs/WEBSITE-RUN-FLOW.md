# OmniReferral — Complete Website Run-Flow Documentation

> **Type:** Developer handoff / system analysis
> **Method:** Direct inspection of the existing codebase (routes, controllers, models, middleware, policies, migrations, views). No code was changed.
> **Stack:** Laravel 11/12-style app (`bootstrap/app.php` middleware config), Blade server-rendered views, MySQL, Spatie Permission package, Stripe + GoHighLevel (GHL) integrations.
> **Date:** 2026-06-19

A note on terminology used throughout: the app has **two parallel permission systems** — a primary `users.role` *workspace* selector (buyer/seller/agent/admin/staff) and a secondary Spatie permission layer (`$user->can('ability')`). Most access control in practice is driven by `users.role` plus a `Gate::before` bridge. This is important and is explained in section **B / H**.

---

## A. Website Summary

**What it is:** OmniReferral is a **real-estate lead-generation and referral platform**. Real-estate agents buy lead packages; the platform captures buyer/seller leads from public forms, qualifies/routes them, and delivers them into agent dashboards. It also runs a **public agent directory** and a **property marketplace (listings)**.

**Problem it solves:** Connects qualified buyer/seller leads to agents, and gives agents a workspace to manage those leads, their public profile, and their listings — while admins/staff oversee the whole funnel.

**Main users / roles:**
- **Guest / public visitor** — browses marketing pages, the agent directory, and the marketplace; submits leads, enquiries, contact and review forms. No login.
- **Agent / realtor** — buys a package (via GHL/Stripe), gets a workspace to manage leads, listings, messages, and their public profile.
- **Seller** — owns properties, can submit listings (assigned to an agent). (Role exists; public registration is disabled — see notes.)
- **Buyer** — saved homes / requests workspace. (Role exists; same registration note.)
- **Staff** — internal operations (ISA, sales, marketing, web_dev sub-teams). Gets a limited admin-like workspace.
- **Admin** — full control center: users, agent profiles, leads, properties, enquiries, content, packages, GHL.
- **Super Admin** — `users.is_super_admin = true`; break-glass "can do anything" via `Gate::before`.

**Core business workflow:**
1. Visitor submits a lead form → `Lead` row created (`status=new`) → admins/staff notified → synced to GHL → auto-routed to an agent if routing is configured.
2. Agent purchases a package (handled by GoHighLevel + Stripe); a GHL webhook provisions the `User` + `RealtorProfile`, emails login credentials.
3. Admin/staff review and assign leads, approve agent profiles and property listings.
4. Approved agent profiles appear in the public **directory**; approved properties appear in the **marketplace**.

---

## B. User Roles and Permissions

### The two permission systems

1. **Workspace role** — `users.role` enum: `buyer | seller | agent | admin | staff` (default `buyer`). Checked by `User::hasAnyWorkspaceRole()`, `isAdmin()`, `isStaff()`, `isAgent()`, etc. This is the *primary* selector and drives dashboards and the `role:` middleware.
2. **Spatie permissions** — fine-grained abilities like `leads.view`, `properties.review`. Used via `$user->can('ability')` and Policies. A `Gate::before` hook in `app/Providers/AppServiceProvider.php` **grants these abilities by role** so the system works even without Spatie rows assigned:
   - **Admins** get a large allow-list (users.*, leads.*, properties.*, realtor_profiles.*, enquiries.*, packages.manage, blog.manage, etc.).
   - **Staff** get a narrower allow-list (view/update/review, no delete/manage of most resources).
   - **Super Admin** (`is_super_admin`) → `Gate::before` returns `true` for everything.

### Access gates
- `Gate::define('admin.access')` → true for **any staff-or-admin** (`isStaff()` = role admin OR staff).
- `Gate::define('super-admin.access')` → true only for `is_super_admin`.

### Middleware (registered in `bootstrap/app.php`)
| Alias | Class | Purpose |
|---|---|---|
| `auth` | Laravel default | Must be logged in |
| `active.account` | `EnsureActiveAccount` | Logs out + redirects if `status !== 'active'` (pending/suspended) |
| `must_reset_password` | `MustResetPassword` | Forces redirect to `/account/security` if `must_reset_password=true` (except security/logout routes) |
| `role:agent` / `role:seller,agent` / `role:staff` | `RoleMiddleware` | `abort(403)` unless `users.role` is in the list |
| `can:admin.access` / `can:super-admin.access` | Gate | Admin / super-admin area |
| `admin` | `AdminMiddleware` | `abort(403)` unless `isAdmin()` (defined but **not used in `web.php`** — admin routes use `can:admin.access` instead) |

Global appended middleware: `TrackAffiliateCookie`, `EnsureListingDeviceCookie` (device fingerprint for favorites). Prepended: `NoCdnCache`.

### Role capability matrix (effective)
| Capability | Guest | Buyer | Seller | Agent | Staff | Admin | Super Admin |
|---|---|---|---|---|---|---|---|
| Browse public pages / directory / marketplace | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Submit lead/enquiry/contact/review | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Own dashboard | — | buyer | seller | agent | staff | admin | super-admin |
| Create/edit own listings | — | — | ✅ (owner) | ✅ (profile) | ✅ (review/edit) | ✅ | ✅ |
| Manage leads (assign/status/import/export) | — | — | — | own assigned only | ✅ | ✅ | ✅ |
| Approve agent profiles / properties | — | — | — | — | ✅ (review) | ✅ | ✅ |
| Manage users / packages / content / GHL | — | — | — | — | partial | ✅ | ✅ |
| GHL settings *write* | — | — | — | — | — | view only | ✅ |

---

## C. Full Website Run-Flow (high level)

```
Public site (HomeController, RealtorController, PropertyController, LeadController, ContactController, ReviewController)
   │  forms → Lead / Contact / Enquiry / Testimonial / PropertyFavorite
   ▼
Lead pipeline (LeadController::store → event LeadReceived → notify admin/staff → SyncLeadToGoHighLevel → LeadRoutingService::assignIfConfigured)
   │
   ▼
Auth (AuthController) — login by workspace (agent/admin/staff). No public self-registration form (CTAs removed; GHL handles onboarding).
   │
   ├── Agent workspace (Agent\PortalController, Agent\LeadController, Dashboard\EnquiryController)
   ├── Buyer/Seller workspace (DashboardController)
   ├── Staff workspace (Admin\DashboardController::staff)
   ├── Admin control center (Admin\* controllers)
   └── Super-admin dashboard (Admin\DashboardController::superAdmin)
   │
   ▼
Integrations: GoHighLevel webhooks (onboarding/purchase/lead-status/events) + Stripe webhook. Outbound jobs sync users/leads to GHL.
```

---

## D. Visitor / Customer Journey

1. **Homepage `/`** (`HomeController@index`) — featured agents (cached), packages, testimonials, partner logos, latest blogs, team, and 6 paginated marketplace properties.
2. Browse **`/listings`** (marketplace) and **`/listings/{property}`** (detail). Detail page shows gallery, "listed by" agent, related listings (same ZIP), comments, favorite button, and an **enquiry form**.
3. Browse **`/agents`** directory, **`/agents/{location}`**, **`/agent/{slug}`** profile.
4. Submit:
   - **Lead form** → `POST /lead-store` (`LeadController@store`).
   - **Property enquiry** → `POST /properties/{property}/enquiry` (creates a `Lead`, emails admin + agent).
   - **Agent inquiry** → `POST /agent/{agent}/inquiry` (creates `Contact` + `Lead`).
   - **Contact** → `POST /contact`. **Review** → `POST /reviews`. **Favorite** → `POST /properties/{property}/favorite` (device-cookie based, works for guests). **Comment** → `POST /properties/{property}/comments`.
5. Guest favorites are viewable at **`/my-favourites`** (`GuestFavouritesController`) keyed off the listing-device cookie.

The visitor never authenticates. All public POSTs are CSRF-protected and rate-limited (`throttle:leads`, `throttle:contact`, etc., configured in `AppServiceProvider`).

---

## E. Agent / Realtor Journey

**Account creation is integration-driven, not a public form.** The `/join-as-agent` routes 301-redirect to `/agents`, and `/onboarding/{role}` just informs the user that "GoHighLevel handles onboarding automatically." An agent account is created one of two ways:
1. **GHL `package_purchased` webhook** (`GoHighLevelWebhookController@packagePurchased`) — provisions a `User` (role=agent, status=active, `must_reset_password=true` for new users), creates a `RealtorProfile`, assigns the package, and emails portal login credentials (`SendPortalLoginAccessEmailJob`).
2. **Admin creates the profile** in the back office (`StaffAgentProfileController@store`) — creates the `User` + `RealtorProfile` with a random password and `must_reset_password=true`.

**Logging in:** `/login` → choose workspace = **agent** → redirected to `dashboardRoute()` = `/agent/dashboard`. First login forces a password reset (`MustResetPassword` → `/account/security`).

**Agent workspace** (`Agent\PortalController`, all under `auth + active.account + must_reset_password + role:agent`):
- `/agent/dashboard` — overview: lead pipeline, listing capacity (driven by package `listingLimit()`), messages, analytics trends.
- `/agent/profile` (`GET/PUT`) — edit name/contact/brokerage/license/specialties/bio + headshot upload.
- `/agent/leads` — leads where `assigned_agent_id = me`; `POST /agent/leads/{lead}/status`.
- `/agent/listings` (`GET`) + `POST /agent/listings` (create, gated by package listing limit) — see **L**.
- `/agent/messages` — `Contact` rows where `recipient_user_id = me`; `POST .../status`.
- `/dashboard/enquiries*` — enquiry threads (see **M**).

`PortalController::ensureAgentProfile()` lazily creates a `RealtorProfile` (firstOrCreate) so every agent always has one. Listing creation is **blocked** unless the agent's current plan is a `lead` category package with `listingLimit() > 0` and remaining slots.

---

## F. Admin Journey

1. Login `/login` → workspace **admin** → `Admin\DashboardController@index` at `/admin`.
   - If `is_super_admin` → redirect to `/super-admin/dashboard`.
   - If `role=staff` → redirect to `/staff/dashboard`.
2. The dashboard (`Admin\DashboardController::dashboard()`) computes: lead counts & stages, team queues, pipeline health, user-submitted listings, pending accounts, recent enquiries, MRR estimate, analytics trends (daily/weekly/monthly/yearly), property-type distribution, recent audit log (if `audit.view`).
3. Sidebar navigates to the admin sections (all under `can:admin.access`):
   - **Users** (`/admin/users`), **Agent Profiles** (`/admin/agent-profiles`), **Leads** (`/admin/leads`), **Properties** (`/admin/properties`), **Enquiries** (`/admin/enquiries`), **Activity** (`/admin/activity`), **Exports** (`/admin/exports`), **Search** (`/admin/search`), **Blog**, **Testimonials**, **Packages**, **Pricing Plans**, **Webhook Events**, **GoHighLevel** control panel.

Staff see the same controllers but the `Gate::before` allow-list restricts destructive abilities (no delete/manage for most resources). GHL settings *writes* are super-admin only.

---

## G. Route-by-Route Explanation

All routes are defined in **`routes/web.php`** (no separate `api.php`; AJAX uses the same web routes returning JSON when `wantsJson()`/`expectsJson()`).

### Public (no auth)
| Route | Controller | Purpose |
|---|---|---|
| `GET /` | `HomeController@index` | Homepage |
| `GET /pricing` | `PricingController@index` | Pricing |
| `GET /packages/{slug}/checkout` · `POST .../stripe-checkout` · `GET .../success` | `PricingController` | Stripe checkout flow |
| `GET /about /faq /privacy-policy /terms-of-service /resources /news /careers /surveys-campaigns` + policy pages | `HomeController` / closures | Marketing/content |
| `GET /reviews` · `POST /reviews` | `ReviewController` | Testimonials list + submit |
| `GET /listings` | `HomeController@listings` | Marketplace |
| `GET /listings/{property}` | `PropertyController@show` | Listing detail |
| `GET /my-favourites` | `GuestFavouritesController@index` | Guest saved listings |
| `POST /properties/{property}/favorite` | `PropertyController@toggleFavorite` | Toggle favorite (device cookie) |
| `POST /properties/{property}/comments` | `PropertyCommentController@store` | Listing comment |
| `POST /properties/{property}/enquiry` | `PropertyController@storeEnquiry` | Listing enquiry → Lead |
| `GET /agents` · `/agents/{location}` · `/agent/{agent}` · `/agent/{agent}/preview` | `RealtorController` | Directory + profile |
| `POST /agent/{agent}/inquiry` | `RealtorController@inquiry` | Agent contact/referral → Contact + Lead |
| `GET /blog` · `/blog/{blog}` | `BlogController` | Blog |
| `POST /contact` | `ContactController@submit` | Contact form |
| `POST /lead-store` | `LeadController@store` | Main lead capture |
| `GET /login` · `POST /login` · forgot/reset password | `Auth\AuthController` | Auth |
| `POST /webhooks/gohighlevel/{onboarding,purchase,lead-status,events}` · `POST /webhooks/stripe` | Webhook controllers | CSRF-exempt integrations |
| `GET /sitemap.xml` | closure | Cached sitemap |
| `GET /onboarding/{role}` · `/join-as-agent*` | closures/redirects | Legacy → directory/login |

### Authenticated (`auth` + `active.account` + `must_reset_password`)
- **Account:** `/account/profile` (GET/PUT), `/account/security` (GET), `/account/password` (POST).
- **Dashboards:** `/dashboard` (router → role dashboard), `/affiliate`.
- **`role:agent`:** `/agent/dashboard`, `/agent/profile`, `/agent/leads`, `/agent/listings`, `/agent/messages`, `/dashboard/enquiries*`.
- **`role:seller,agent`:** `/properties/{property}/edit|update|destroy`.
- **`role:staff`:** `/staff/dashboard`.
- **`can:super-admin.access`:** `/super-admin/dashboard`.
- **`can:admin.access`:** all `/admin/*` (users, agent-profiles, leads, properties, enquiries, activity, exports, search, blog, testimonials, packages, pricing-plans, webhook-events, gohighlevel).

> **Note:** `dashboard.seller.listings`, `dashboard.buyer*` and similar routes are *referenced* by controllers (e.g. `PropertyController` redirects to `dashboard.seller.listings`) but the buyer/seller dashboard routes are **not declared in `web.php`** — see **T (Issues)**.

---

## H. Authentication Flow

**File:** `app/Http/Controllers/Auth/AuthController.php`, middleware in `app/Http/Middleware/`.

- **Login (`POST /login`)** validates `role` (must be one of `agent|admin|staff`), `email`, `password`. It looks up the user by email, checks the hash (`User::passwordMatches`), logs in, regenerates the session, then enforces:
  1. `status=pending` → logout + "waiting for approval" error.
  2. `status=suspended` → logout + "account not active" error.
  3. `user.role !== chosen role` → logout + "that account belongs to the X workspace" error.
  4. Otherwise → `redirectBasedOnRole()` → `User::dashboardRoute()`.
- **`dashboardRoute()`** maps role → route: super-admin → `/super-admin/dashboard`; admin → `/admin`; staff → `/staff/dashboard`; agent → `/agent/dashboard`; seller → `dashboard.seller`; buyer → `dashboard.buyer`; default → `/dashboard`.
- **Password handling:** hashed via the `password => 'hashed'` cast. New provisioned users get `must_reset_password=true`; `MustResetPassword` middleware forces them to `/account/security` before any other authenticated page.
- **Forgot/reset password** uses Laravel's `Password` broker (`/forgot-password`, `/reset-password/{token}`).
- **Session/token:** standard Laravel session auth (cookie). No API tokens. `remember` checkbox supported.
- **Logout (`POST /logout`)** invalidates session and redirects to `/`.
- **Unauthorized access:** unauthenticated → Laravel redirects to `login`; wrong role on a `role:`-guarded route → `abort(403)`; non-active account hitting any authed route → `EnsureActiveAccount` logs out and redirects to login with a message.
- **There is no public registration route.** `loginWorkspaces()` only offers agent/admin/staff. Buyer/seller accounts only exist via admin creation or integrations.

---

## I. Agent Onboarding Flow

Two creation paths converge on the same data shape (`User` role=agent + `RealtorProfile`):

### Path 1 — GHL `package_purchased` webhook
`GoHighLevelWebhookController@packagePurchased` (CSRF-exempt, secret-validated via `X-OmniReferral-Webhook` header against `GhlSetting` or config):
1. Records the webhook (idempotency via `WebhookInboxService`; duplicate → 200 ignore).
2. Requires `email`. In a DB transaction: `User::firstOrNew(email)`, fill name/phone/role/status=active/ghl_contact_id/city/state/zip; for **new** users provision a password (`PasswordProvisioningService`), set `must_reset_password=true`.
3. Assign package → `current_plan_id`; ensure `affiliate_code`.
4. `RealtorProfile::updateOrCreate(user_id)` with brokerage/service area/specialties/bio/default headshot.
5. Dispatch `SyncUserToGoHighLevel`; for new users send `SendPortalLoginAccessEmailJob`; notify admins (`NewAgentOnboardingNotification`).

### Path 2 — Admin-created profile
`StaffAgentProfileController@store` (`/admin/agent-profiles`):
- Validates the full profile payload (name, brokerage, service city/state, bio min 40 chars, profile_status, optional headshot upload/url, social links, etc.).
- Creates `User` (role=agent, random 32-char password, `must_reset_password=true`). **User `status` is derived from chosen `profile_status`**: suspended→suspended, published/featured→active, draft→pending.
- Creates `RealtorProfile` with approval fields set based on status (`approved_at`/`approved_by_user_id` when published/featured; `rejected_at` when suspended).

### Profile status model (`RealtorProfile`)
`profile_status` enum + labels (`statusOptions()`):
| `profile_status` | Label shown | Public directory? | User `status` |
|---|---|---|---|
| `draft` | **Pending Review** | ❌ | pending |
| `published` | **Approved** | ✅ | active |
| `featured` | **Featured** | ✅ (sorted first) | active |
| `suspended` | **Suspended** | ❌ | suspended |

**Public visibility** (`isPublicVisible()` / `scopePublicEligible`) requires `profile_status ∈ {published, featured}` AND `rejected_at IS NULL` AND the **owning user is `active`**. The directory query (`RealtorController`) additionally requires non-empty `bio`, `service_city`, `service_state`.

- **Pending (draft):** user can log in only after status becomes active; profile hidden from directory.
- **Approved (published/featured):** appears in directory; agent dashboard fully usable.
- **Suspended:** removed from directory; user can't log in (`EnsureActiveAccount`).
- **Missing required fields:** `store/update` validation returns errors; profile won't save.

> Note: `RealtorProfile` also has legacy `approved_at`/`scopeApproved`, but **current visibility is driven by `profile_status`**, not `approved_at`. `scopePublicDirectory` and `scopeApproved` are kept for backward-compat (`scopePublicDirectory` is `@deprecated`).

---

## J. Admin Dashboard Flow

**File:** `app/Http/Controllers/Admin/DashboardController.php`. One private `dashboard($view)` method renders admin/staff/super-admin views with the same stat bundle.

- **Overview data** (counts via Eloquent): leads, realtor profiles, properties, active/featured/pending listings, pending accounts, draft/published/featured agent profiles, contacts, enquiries, packages, favorites, estimated revenue, **lead pipeline value** (sums a hard-coded `revenueMap` by `package_type`: starter 199 / growth 349 / elite 549), **MRR estimate** (join users→packages monthly_price), users total/active/suspended, testimonials.
- **Lead stages / team queues / pipeline health** are derived from `Lead.status` counts.
- **Analytics trends** built by `countTrendFor()` / `revenueTrendFor()` over daily/weekly/monthly/yearly windows (helpers live in a shared `Controller` base / trait).
- **Recent audit** only loaded if `can('audit.view')`.
- **Sidebar nav** lives in `resources/views/partials/dashboard/nav-item.blade.php` inside `layouts/dashboard.blade.php`; mobile uses a JS toggle (`[data-sidebar-toggle]` adds `.sidebar-open`).

Each admin section is a dedicated controller (see **O**). Settings/account pages (`Account\ProfileController`, `Account\SecurityController`) are shared by all authenticated roles.

---

## K. Agent Profiles Admin Flow

**File:** `app/Http/Controllers/Admin/StaffAgentProfileController.php`. Views in `resources/views/pages/admin/agent-profiles/`.

- **List (`@index`)** — `RealtorProfile` query with `user` + `createdByUser`, newest first. Authorized by `RealtorProfilePolicy@viewAny` (staff or `realtor_profiles.view`).
- **Search (`q`)** — matches brokerage, service city/state/zip, license, and the related user's name/display_name/email/phone.
- **Filters** — `state`, `market` (city or market_areas), `brokerage`, `featured` (yes/no), `per_page` (10/25/50/100).
- **Status tabs** — `all | draft | published | featured | suspended`, each with a live count (`counts` array).
- **Pagination** — `paginate($perPage)->withQueryString()`.
- **Add Agent Profile (`create`/`store`)** — creates User + RealtorProfile (see **I**), audits via `AdminAudit::log`.
- **Edit (`show`/`update`)** — updates user + profile; recomputes approval fields and user `status` from chosen `profile_status`; headshot upload/url.
- **Approve = `publish`** (`POST .../publish`) → `profile_status=published`, set `approved_*`, user `status=active`. Returns JSON (with refreshed counts) when `wantsJson()`.
- **Feature** (`POST .../feature`) → `profile_status=featured`, approval fields, user active.
- **Suspend** (`POST .../suspend`) → `profile_status=suspended`, `rejected_*` set, user `status=suspended`, removed from directory.
- **Status counts** — recomputed inline from `RealtorProfile::draft()/published()/featured()/suspended()->count()`.
- **Profile completion %** — *not computed server-side here*; any completion meter is view-level (the model exposes the fields). The dashboard tracks rating/leads_closed as "directory growth" signals instead.
- **UI update after action** — JSON responses return new status label + counts for in-place updates; non-AJAX falls back to `back()->with('success', …)`.
- **API failure** — validation errors return to the form with `withErrors`; missing user → `abort(404)`.

---

## L. Properties Flow

**File:** `app/Http/Controllers/PropertyController.php`; admin side `Admin\PropertyManagementController` (resource routes); policy `PropertyPolicy`.

- **Create:**
  - **Agent** `POST /agent/listings` — must have a `lead`-category plan with `listingLimit()>0` and free slots; sets `source='Agent Dashboard Upload'`, `realtor_profile_id`, `owner_user_id`, `listed_by_id` = self.
  - **Seller** `POST /agent/listings` (same action) — must pick `listing_realtor_profile_id` (a published/featured agent of an active user); `owner_user_id`=seller, `listed_by_id`=that agent's user.
  - Both require ≥1 image (gallery built by `prepareGalleryPayload`), slug auto-generated.
  - Agent/seller submissions start **`status='Pending'`, `approval_status='pending'`** (require admin review). Admin-created listings are auto-approved/active.
- **Edit/Update** (`role:seller,agent` for `/properties/{property}/edit|update`; admin via resource) — `PropertyPolicy@update` (owner/profile match or staff). **Non-staff edits to a not-yet-approved listing force re-submission** (`status=Pending`, `approval_status=pending`). Staff get extra status options including `Pending`.
- **Delete** (`@destroy`) — soft delete (`SoftDeletes`); `PropertyPolicy@delete` (admin `properties.delete` or owner).
- **Review** (`POST /admin/properties/{property}/review`) — `PropertyPolicy@review` (staff or `properties.review`); approve→`approved`+`Active`+published_at, reject→`rejected`+`Pending`; audited.
- **Ownership:** `owner_user_id` (seller/agent who uploaded) + `realtor_profile_id` (representing agent) + `listed_by_id` (display "listed by" user, kept in sync by `PropertyListingIdentityService` on save).
- **Marketplace visibility** (`scopeMarketplaceVisible`): `approval_status=approved` AND `status='Active'`.
- **Approval/publish status:** yes — `approval_status` (`pending|approved|rejected`) + display status (`Active|Pending|Sold|Off-Market`).
- **Images/files:** stored on the `public` disk under `properties/listings`; `images` is a JSON array, `image` is the featured one; helper accessors resolve URLs. Gallery supports reorder (`gallery_order`), featured selection (`featured_image`), and removal (`remove_images`).

---

## M. Enquiries and Leads Flow

There are **two related concepts**: `Lead` (the main funnel) and `Enquiry`/`Contact` (message threads).

### Leads
- **Capture:** `POST /lead-store` (`LeadController@store`, `StoreLeadRequest`). Normalizes ZIP/address by intent, handles `property_image` upload, **dedupes** via `Lead::duplicateQuery` on normalized email/phone (409/`info` on duplicate), assigns package + `package_type`, scores the lead (`scoreLeadFromRequest`, 0–100), sets `status='new'`, `source='website'`, stores `form_data` (IP, UA, referrer).
- **After create:** `event(LeadReceived)` → notify admins/staff (`NewLeadCreatedNotification`) → `SyncLeadToGoHighLevel::dispatch` → `LeadRoutingService::assignIfConfigured` (auto-assign to an agent if routing is set up). Success message differs based on whether it was routed.
- **Other lead sources:** listing enquiry (`PropertyController@storeEnquiry`), agent directory inquiry (`RealtorController@inquiry`, also creates a `Contact`), CSV/Sheets import (`Admin\LeadManagementController`).
- **Lead Registry / management** (`/admin/leads`, `Admin\LeadManagementController`): filter (`LeadFilterService`), summary counts, paginate; CSV export (sync or queued via `GenerateDataExport`), multi-format import with preview/commit (`LeadMultiFormatImportService`), Google-Sheets sync (SSRF-hardened to docs.google.com HTTPS only).
- **Lead status / assign / activity:** `Admin\LeadController` (`POST /admin/leads/{lead}/status|assign|activity`). Statuses: `new, contacted, in_progress, qualified, assigned, closed, not_interested`. `LeadActivity` tracks history; `LeadMatch` links agents.
- **Agent side:** agent sees `assigned_agent_id = self` leads; `POST /agent/leads/{lead}/status` (`Agent\LeadController`).
- **Email notifications:** customer status-change notifications via `LeadCustomerNotifier` (triggered on GHL `lead_status` webhook); admin/staff in-app notifications on creation.

### Enquiries (`Enquiry` + `EnquiryReply`) and Contacts
- `Enquiry` ties a `property`, `sender`, `receiver` (agent/owner) and a thread of `EnquiryReply`. Statuses `pending|replied|closed`; `markRepliedIfNeeded()` and `syncLinkedContact()` keep a linked `Contact.message_status` in sync.
- **Agent threads:** `Dashboard\EnquiryController` (`/dashboard/enquiries*`) — list/show, store reply (throttled), update status. Scoped by `Enquiry::forParticipant`.
- **Admin threads:** `Admin\EnquiryController` (`/admin/enquiries*`) — list, show, reply, status, CSV/XLSX export.
- **Contacts** are the raw inbound messages (contact form, agent directory inquiry); agent messages inbox reads `Contact` where `recipient_user_id = self`.

> The listing-enquiry form (`storeEnquiry`) creates a **Lead** (not an Enquiry) and emails both admin and the assigned agent. Added 2026-06-18.

---

## N. Marketplace Flow

- **Display:** `/listings` (`HomeController@listings`) and homepage section show `Property::marketplaceVisible()` (approved + Active) with favorite summary and "listed by" presentation. Detail at `/listings/{property}`.
- **Listings shown:** any approved+active property regardless of source (agent upload, seller upload, admin/seeded import).
- **Search/filter:** marketplace listing/index filtering is handled in the listings view/controller (ZIP-based related listings on detail; query filters on the index). Agent **directory** filtering is richer (`AgentDirectory` service: search, state/city, brokerage, zip, specialty, rating, featured).
- **Visitor interaction:** favorite (device-cookie, guest-friendly), comment, and enquiry. Enquiry → Lead → emails.
- **Public data:** approved/active listings, published/featured agent profiles. **Admin-only:** pending/rejected listings, all leads, user PII, GHL settings. **Agent-only:** their own assigned leads, their listings, their messages.

---

## O. API / Backend Flow

No REST `api.php`; everything is web routes. AJAX endpoints detect `wantsJson()`/`expectsJson()` and return JSON (e.g., agent-profile feature/publish/suspend, lead store, property enquiry).

**Key controllers & responsibilities:**
- **Public:** `HomeController`, `PricingController`, `RealtorController`, `PropertyController`, `PropertyCommentController`, `BlogController`, `ContactController`, `ReviewController`, `LeadController`, `GuestFavouritesController`.
- **Auth/Account:** `Auth\AuthController`, `Account\ProfileController`, `Account\SecurityController`.
- **Agent:** `Agent\PortalController`, `Agent\LeadController`, `Dashboard\EnquiryController`, `DashboardController` (buyer/seller/affiliate).
- **Admin:** `DashboardController`, `UserManagementController`, `UserModerationController`, `StaffAgentProfileController`, `LeadManagementController`, `LeadController`, `PropertyManagementController`, `EnquiryController`, `ActivityLogController`, `DataExportController`, `PlatformSearchController`, `BlogController`, `TestimonialController`, `PackageController`, `PricingPlanController`, `WebhookEventController`, `GoHighLevelController`.
- **Webhooks:** `GoHighLevelWebhookController` (onboarding/purchase/lead-status), `GoHighLevelEventWebhookController` (generic events, queued via `ProcessGoHighLevelWebhookJob`), `StripeWebhookController`.

**Services (`app/Services/`):** `LeadRoutingService`, `LeadFilterService`, `LeadMultiFormatImportService`, `LeadCustomerNotifier`, `OnboardingSyncService`, `PasswordProvisioningService`, `GoHighLevelService`, `WebhookInboxService`, `StripeCheckoutService`, `EnquiryFromContactService`, `EnquiryReply*`, `PropertyListingIdentityService`.

**Validation:** inline `$request->validate()` plus FormRequests (`StoreLeadRequest`, `StoreListingEnquiryRequest`, `SyncGoogleSheetRequest`). **Error handling:** webhooks return JSON status codes and log via `Log::`; user actions return `withErrors`/`with('error', …)`. **Auth middleware:** see **B/H**. **DB:** Eloquent models; transactions wrap multi-table writes (onboarding, profile create, listing enquiry). **Rate limiting:** defined in `AppServiceProvider` (`leads`, `contact`, `auth-login`, etc.).

---

## P. Database Flow

**Important tables (models in `app/Models/`):**
| Table | Purpose | Key columns / relationships |
|---|---|---|
| `users` | All accounts | `role` enum, `status` enum, `is_super_admin`, `must_reset_password`, `current_plan_id→packages`, `referred_by_user_id→users`, `ghl_contact_id`, `affiliate_code`, address fields |
| `realtor_profiles` | Agent public profile (1:1 with user) | `user_id` (unique), `slug` (unique, route key), `profile_status`, `service_*`, `brokerage_name`, `license_number`, `rating`, `approved_at/by`, `rejected_at/by`, `created_by_user_id` |
| `buyer_profiles` | Buyer details (1:1) | `user_id` (added 2026-06-17) |
| `properties` | Listings | `slug`, `status`, `approval_status`, `realtor_profile_id`, `owner_user_id`, `listed_by_id`, `images` (json), soft deletes |
| `leads` | Lead funnel | `lead_number`, `intent`, `status`, `source`, `package_type/id`, `assigned_agent_id→users`, `reviewed_by_id`, `property_id`, normalized email/phone, `form_data` (json), soft deletes |
| `lead_activities`, `lead_matches` | Lead history & agent matching | `lead_id`, `agent_id` |
| `enquiries` + `enquiry_replies` | Listing message threads | `property_id`, `sender_user_id`, `receiver_user_id`, `contact_id`, `status` |
| `contacts` | Raw inbound messages | `recipient_user_id`, `realtor_profile_id`, `sender_role`, `message_status` |
| `packages`, `pricing_plans` | Lead/assistant packages & marketing pricing | `slug`, `category`, `monthly_price`, `one_time_price`, listing limits |
| `property_favorites`, `property_comments` | Engagement | `property_id`, `user_id`, `device_fingerprint` |
| `affiliate_profiles`, `affiliate_referral_clicks` | Referral program | `referral_code`, `user_id` |
| `testimonials`, `blogs`, `partners`, `team_members` | Content | submission workflow on testimonials |
| `admin_activity_logs`, `audit_logs` | Audit trail | `actor`, action, target |
| `webhook_events`, `gohighlevel_webhook_logs`, `onboarding_logs` | Integration logs/idempotency | correlation columns, processed_at |
| `ghl_settings`, `ghl_field_mappings`, `settings` | Config | encrypted secrets, field mappings |
| `notifications`, `data_exports`, permission tables | Laravel/Spatie | — |

**Relationships:** `User 1—1 RealtorProfile` (enforced unique in migrations `2026_06_12_*`); `RealtorProfile 1—* Property`; `User 1—* Property` (owner & listed_by); `Lead *—1 User` (assigned_agent); `Lead 1—* LeadActivity/LeadMatch`; `Property 1—* Enquiry/Contact/Favorite/Comment`; `User self-ref` referrals.

**Status fields:** `users.status`, `realtor_profiles.profile_status`, `properties.approval_status` + `status`, `leads.status`, `enquiries.status`, `contacts.message_status`. **Audit/by fields:** `approved_by_user_id`, `rejected_by_user_id`, `reviewed_by_user_id`, `created_by_user_id`. **Timestamps:** standard `created_at/updated_at` + domain timestamps (`approved_at`, `contacted_at`, `closed_at`, `onboarding_completed_at`). **Indexes:** added in `2026_05_08_124900_add_scaling_indexes` and normalized identity columns on leads. Soft deletes on `properties` and `leads`.

---

## Q. Complete Data Lifecycle Example — New Agent Joins

1. **Discovery:** Agent visits `/pricing`, picks a lead package. (Public agent registration form has been removed; CTAs point to pricing/GHL.)
2. **Purchase:** Checkout handled through GoHighLevel + Stripe (`PricingController` / `StripeCheckoutService`). On completion GHL calls `POST /webhooks/gohighlevel/purchase`.
3. **Webhook auth:** `GoHighLevelWebhookController@packagePurchased` validates the `X-OmniReferral-Webhook` secret, records the event (idempotent).
4. **Provision (transaction):** `User::firstOrNew(email)` → role=agent, status=**active**, `must_reset_password=true` (new), assign package, generate `affiliate_code`; `RealtorProfile::updateOrCreate` with service area + default headshot.
5. **Notify:** dispatch `SyncUserToGoHighLevel`; `SendPortalLoginAccessEmailJob` emails login URL + temporary password; admins get `NewAgentOnboardingNotification`.
6. **First login:** `/login` (workspace=agent) → `MustResetPassword` forces `/account/security` to set a new password.
7. **Profile status:** new profile defaults to `draft` (Pending Review) unless created already published. The agent can edit profile/listings but is **not yet in the public directory**.
8. **Admin review:** `/admin/agent-profiles` shows draft profiles; admin opens the profile, reviews, clicks **Approve/Publish** (or **Feature**).
9. **DB change:** `profile_status=published`, `approved_at`/`approved_by_user_id` set, user `status=active`.
10. **Public:** profile now passes `isPublicVisible()` → appears at `/agents` and `/agent/{slug}`; listed in sitemap.
11. **Operate:** agent manages assigned leads, uploads listings (subject to package `listingLimit()` + admin approval), responds to messages/enquiries.
12. **Listings public:** once an admin approves a listing (`approval_status=approved`, `status=Active`), it shows in the marketplace and is attributed to the agent via `listed_by_id`.

---

## R. Loading / Error / Success Handling

- **Success:** Laravel flash messages (`->with('success'|'info'|'error', …)`) rendered in the layout; AJAX actions return `{success, message, …}` JSON. Profile feature/publish/suspend also return refreshed counts for in-place UI updates.
- **Validation errors:** `$request->validate()` / FormRequests → redirect back with `withErrors` + old input; custom messages provided for auth, lead, profile, and property forms.
- **API errors:** webhooks return proper HTTP codes (401 unauthorized, 422 missing data, 404 not found, 409 duplicate lead, 500 with logged trace). `storeEnquiry` wraps in try/catch and logs.
- **No data:** dashboards build empty collections / zero counts safely; directory/marketplace paginators render empty states in views.
- **No permission:** `abort(403)` (role middleware / policy) or login redirect.
- **Loading states:** server-rendered Blade — full-page loads, so spinners are minimal; AJAX endpoints (favorite toggle, profile status, enquiry submit) rely on view-level JS. This is an area for UX improvement (see **T/U**).

---

## S. Responsive Behavior

- **Layouts:** `resources/views/layouts/app.blade.php` (public) and `layouts/dashboard.blade.php` (authenticated). Styling is custom CSS in `resources/css` / `public/css` (compiled via Vite), not utility-first Tailwind in markup.
- **Dashboard sidebar:** `<aside id="dashboardSidebar">` with a JS toggle button `[data-sidebar-toggle]` that adds/removes a `.sidebar-open` class on the shell; `aria-expanded` is maintained. On desktop the sidebar is persistent; on mobile it collapses behind the toggle.
- **Header:** public header in `partials/header.blade.php`; `auth-home-bar` for logged-in state.
- **Tables/cards/forms/filters/modals:** admin tables (users, leads, agent-profiles) are wide and rely on container styling; on small screens horizontal scrolling/overflow is the main risk area (see issues). Pagination uses Laravel paginator views.
- **Agent portal** has its own sidebar partial (`pages/dashboards/partials/agent-portal-sidebar.blade.php`).

> A full responsive audit requires reviewing the compiled CSS; from the markup, the mechanism is a class-toggle drawer rather than a CSS-only breakpoint menu, so JS must load for mobile nav to work.

---

## T. Current Issues Found

**Confirmed from code inspection:**
1. **Missing buyer/seller dashboard routes.** `User::dashboardRoute()` and `DashboardController` redirect to `dashboard.buyer` / `dashboard.seller` / `dashboard.seller.listings`, and `PropertyController` redirects to `dashboard.seller.listings`, **but these named routes are not declared in `routes/web.php`** (only `/dashboard` and `/affiliate` plus agent/admin/staff are). A buyer/seller login or a seller listing action would hit a `RouteNotFoundException`. **High priority.**
2. **Dead code in `DashboardController@index`.** Everything after `abort(403, …)` (the `$allRoleCards`/`$quickActions`/`view('pages.dashboard')` block) is unreachable. Confusing; should be removed or the abort relocated.
3. **`AdminMiddleware` (`admin` alias) is registered but unused** — admin routes use `can:admin.access`. Two ways to express "admin", one dormant.
4. **Two permission systems** (workspace `role` vs Spatie abilities) bridged by a large hard-coded `Gate::before` allow-list. Easy to drift; abilities must be added in two places. Documented but fragile.
5. **Hard-coded revenue map** (`starter/growth/elite → 199/349/549`) duplicated in `Admin\DashboardController`, `DashboardController`, and `Agent\PortalController` instead of reading `packages` prices. Revenue/MRR figures can silently diverge from real pricing.
6. **CONFIRMED BUG — listing-enquiry data is silently dropped.** `PropertyController@storeEnquiry` calls `Lead::create([... 'type', 'enquiry_type', 'message' ...])`, but **`Lead::$fillable` contains none of `type`, `enquiry_type`, or `message`**, so Laravel's mass-assignment protection discards them. Worse, the `2026_06_18_..._add_listing_enquiry_fields_to_leads_table` migration adds `type` and `enquiry_type` columns **but no `message` column at all**. Result: listing enquiries are created with default `type='buyer'`, no `enquiry_type`, and the customer's message text is lost (it survives only inside `form_data` if added there — it currently is not). *Fix:* add `type`, `enquiry_type` (and a `message` column + fillable, or store the message in `notes`/`form_data`). **High priority.**
7. **Listing limit logic only counts `lead`-category plans** — agents on a non-lead plan get "no listing access," which may be intended but is easy to misconfigure.
8. **Marketplace search/filter** is thinner than the agent directory; index filtering is not centralized in a service like `AgentDirectory`/`LeadFilterService`.
9. **Loading/empty states** are server-rendered; AJAX interactions lack consistent loading indicators.
10. **Webhook security falls open in local/testing** (`isAuthorized` returns true when no secret is configured in local/testing env) — fine for dev, must ensure a secret is set in production.

**Areas to verify (not confirmable from this pass):** exact responsive breakpoints (compiled CSS), `BuyerProfile` usage (model + table exist; buyer dashboard route missing), whether all GHL field mappings are wired.

---

## U. Recommended Fix Plan

**High priority (correctness / broken flows):**
1. **Declare buyer/seller dashboard routes** (`dashboard.buyer`, `dashboard.buyer.saved`, `dashboard.buyer.requests`, `dashboard.seller`, `dashboard.seller.listings`, `dashboard.seller.requests`) pointing at the existing `DashboardController` methods, **or** change `dashboardRoute()`/redirects to only target implemented roles. *Files:* `routes/web.php`, `app/Models/User.php`, `app/Http/Controllers/PropertyController.php`, `app/Http/Controllers/DashboardController.php`.
2. **Fix listing-enquiry data loss (confirmed bug, T#6):** add `type`/`enquiry_type` to `Lead::$fillable`, and either add a `message` column (+ fillable) or persist the enquiry message into `notes`/`form_data`. *Files:* `app/Models/Lead.php`, `app/Http/Controllers/PropertyController.php`, listing-enquiry migration.
3. **Remove unreachable code** after `abort(403)` in `DashboardController@index`.
4. **Ensure GHL/Stripe webhook secrets are set in production** and document the header contract.

**Medium priority (maintainability / UX):**
5. **Centralize the revenue/pricing map** — read from `packages` (monthly/one-time price) instead of hard-coded arrays in three controllers.
6. **Consolidate permissions** — either lean fully on Spatie (assign roles+permissions in a seeder) or fully on `users.role`; document the chosen single source of truth. Remove the unused `admin` middleware alias or use it.
7. **Add a marketplace filter service** mirroring `AgentDirectory`/`LeadFilterService` for consistent, testable search.
8. **Add loading/disabled states** to AJAX buttons (favorite, profile status, enquiry submit) and consistent toast handling.
9. **Server-side profile-completion %** for the agent-profiles admin list if the UI shows a meter.

**Low priority (polish):**
10. Empty-state copy/illustrations for directory, marketplace, dashboards.
11. Helper text on lead/property forms; spacing/animation polish; responsive table → card transforms on mobile for admin tables (overflow-x guard).

**Safest to do first:** #3 (remove dead code), #2 (verify fillable), then #1 (routes) behind tests.

---

## V. Files and Components Involved (index)

- **Routing:** `routes/web.php`, `bootstrap/app.php` (middleware aliases & global middleware).
- **Auth:** `app/Http/Controllers/Auth/AuthController.php`; middleware `EnsureActiveAccount`, `MustResetPassword`, `RoleMiddleware`, `AdminMiddleware`.
- **Authorization:** `app/Providers/AppServiceProvider.php` (Gate::before + gates + rate limits); policies `PropertyPolicy`, `RealtorProfilePolicy`, `LeadPolicy`, `EnquiryPolicy`, `UserPolicy`; `app/Support/AuthorizesWithPermissions`.
- **Models:** `User`, `RealtorProfile`, `Property`, `Lead`, `Enquiry`, `Contact`, `Package`, `PricingPlan`, `AffiliateProfile`, `Testimonial`, GHL models, audit/webhook models (`app/Models/`).
- **Public controllers:** `HomeController`, `RealtorController`, `PropertyController`, `PropertyCommentController`, `LeadController`, `ContactController`, `ReviewController`, `PricingController`, `BlogController`, `GuestFavouritesController`.
- **Workspace controllers:** `Agent\PortalController`, `Agent\LeadController`, `Dashboard\EnquiryController`, `DashboardController`, `Account\*`.
- **Admin controllers:** `Admin\DashboardController`, `UserManagementController`, `UserModerationController`, `StaffAgentProfileController`, `LeadManagementController`, `Admin\LeadController`, `PropertyManagementController`, `EnquiryController`, `ActivityLogController`, `DataExportController`, `PlatformSearchController`, `BlogController`, `TestimonialController`, `PackageController`, `PricingPlanController`, `WebhookEventController`, `GoHighLevelController`.
- **Webhooks/Jobs:** `Webhooks\GoHighLevelWebhookController`, `GoHighLevelEventWebhookController`, `StripeWebhookController`; jobs `SyncLeadToGoHighLevel`, `SyncUserToGoHighLevel`, `ProcessGoHighLevelWebhookJob`, `SendPortalLoginAccessEmailJob`, `GenerateDataExport`.
- **Services:** `app/Services/*` (routing, filtering, import, onboarding sync, password provisioning, GHL, webhook inbox, stripe checkout, property identity).
- **Views:** `resources/views/home.blade.php`, `layouts/{app,dashboard}.blade.php`, `pages/**`, `partials/**` (esp. `partials/dashboard/nav-item`, `pages/dashboards/partials/agent-portal-sidebar`).
- **Migrations:** `database/migrations/` (users, realtor_profiles hardening & 1:1 constraints, properties workflow, leads expansion/normalization, GHL/settings/onboarding tables).

---

*End of run-flow documentation. Items in section T marked "verify" should be confirmed before relying on them; everything else was read directly from the source files cited.*
