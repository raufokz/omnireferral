# Pricing Premium SaaS Upgrade — TODO

## Phase 1: Routing + package detail pages (prevent 404s)
- [ ] Add routes:
  - [ ] GET `/pricing/quick-lead`
  - [ ] GET `/pricing/power-lead`
  - [ ] GET `/pricing/prime-lead`
- [ ] Create 3 detail views:
  - [ ] `resources/views/pages/pricing/quick-lead.blade.php`
  - [ ] `resources/views/pages/pricing/power-lead.blade.php`
  - [ ] `resources/views/pages/pricing/prime-lead.blade.php`
- [ ] Ensure detail pages “GET STARTED” link goes to checkout slugs:
  - [ ] quick-lead → `quick-leads`
  - [ ] power-lead → `power-leads`
  - [ ] prime-lead → `prime-leads`

## Phase 2: Pricing page conversion flow + card CTA behavior
- [ ] Update `resources/views/partials/pricing-plan-switcher.blade.php`:
  - [ ] Real estate package CTA button text: **EXPLORE PLAN**
  - [ ] Real estate CTA links go to `/pricing/*-lead` (NOT checkout)

## Phase 3: Premium SaaS pricing content + sections
- [ ] Upgrade `resources/views/pages/pricing.blade.php` to match spec:
  - [ ] Replace “Why OmniReferral” section with 6 premium feature cards
  - [ ] Add “How it works” 5-step section
  - [ ] Add premium FAQ accordion
  - [ ] Add Trust & Credibility section
  - [ ] Add Testimonials section
  - [ ] Add Final CTA section with “Talk To Sales” + “View Packages”
- [ ] Create comparison table partial:
  - [ ] New partial for Quick/Power/Prime fixed comparison fields
  - [ ] Highlight Power Lead as Most Popular

## Phase 4: Package detail page parity with spec
- [ ] Each detail page includes:
  - [ ] Full package overview
  - [ ] Everything included / benefits
  - [ ] Best for + expected outcomes
  - [ ] Process overview
  - [ ] FAQs (accordion)
  - [ ] Comparison section (Quick/Power/Prime)
  - [ ] Trust indicators
  - [ ] CTA section (GET STARTED + TALK TO SALES)

## Phase 5: Content model enrichment
- [ ] Update `app/Support/PricingContent.php` real-estate plans for:
  - [ ] best_for
  - [ ] expected outcomes
  - [ ] support level indicators
  - [ ] onboarding information
  - [ ] ROI-focused messaging
  - [ ] lead/referral potential
  - [ ] service highlights
- [ ] Ensure no undefined keys render in templates.

## Phase 6: Styling for “2x more premium” look
- [ ] Update `resources/css/modules/pricing.css` (and any required new CSS) to style:
  - [ ] Premium feature grids
  - [ ] SaaS comparison table
  - [ ] Accordion + testimonials
  - [ ] Spacing/typography improvements (without changing brand language)

## Phase 7: Checkout 404 verification
- [ ] Verify:
  - [ ] `/packages/{slug}/checkout` works for the 3 real-estate slugs
  - [ ] `/packages/{slug}/stripe-checkout` creates sessions (if Stripe is configured)
  - [ ] `/packages/{slug}/success` renders correctly
  - [ ] No broken links from pricing cards or detail pages

## Done criteria
- [ ] No 404s for pricing detail pages and checkout
- [ ] Pricing page and detail pages match spec sections
- [ ] CTA flow: Pricing → Explore Plan → Package Details → GET STARTED → Checkout
- [ ] Thorough testing (routing + UI sections + mobile layout)
