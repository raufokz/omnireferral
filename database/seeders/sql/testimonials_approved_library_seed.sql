-- OmniReferral approved testimonials seed.
-- Uses only existing testimonials columns from the current Laravel migrations:
-- name, audience, company, location, submitted_by_email, submitted_by_user_id,
-- photo, rating, quote, audio_path, video_url, is_featured, is_published,
-- sort_order, submission_status, reviewed_by_user_id, reviewed_at, created_at, updated_at.
-- This script is idempotent by submitted_by_email and does not insert manual IDs.

SET @now := NOW();

DROP TEMPORARY TABLE IF EXISTS omni_seed_numbers;
CREATE TEMPORARY TABLE omni_seed_numbers (
    n INT PRIMARY KEY
);

INSERT INTO omni_seed_numbers (n) VALUES
    (1),(2),(3),(4),(5),(6),(7),(8),(9),(10),
    (11),(12),(13),(14),(15),(16),(17),(18),(19),(20),
    (21),(22),(23),(24),(25),(26),(27),(28),(29),(30);

DROP TEMPORARY TABLE IF EXISTS omni_testimonial_seed;
CREATE TEMPORARY TABLE omni_testimonial_seed (
    name VARCHAR(255) NOT NULL,
    audience VARCHAR(255) NOT NULL,
    company VARCHAR(255) NULL,
    location VARCHAR(255) NULL,
    submitted_by_email VARCHAR(255) NULL,
    submitted_by_user_id BIGINT UNSIGNED NULL,
    photo VARCHAR(255) NULL,
    rating TINYINT UNSIGNED NOT NULL,
    quote TEXT NOT NULL,
    audio_path VARCHAR(255) NULL,
    video_url VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL,
    is_published TINYINT(1) NOT NULL,
    sort_order INT UNSIGNED NOT NULL,
    submission_status VARCHAR(255) NOT NULL,
    reviewed_by_user_id BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 30 Buyer testimonials.
INSERT INTO omni_testimonial_seed
SELECT
    ELT(n, 'Avery Brooks','Maya Chen','Landon Pierce','Nora Ellis','Theo Martin','Isla Bennett','Caleb Ortiz','Sienna Gray','Evan Carter','Lila Morgan','Julian Reed','Amara Lewis','Micah Hayes','Elena Foster','Rowan Price','Mila Sullivan','Dylan Harper','Naomi Cruz','Gavin Wright','Clara Vaughn','Eli Dawson','Zara Mitchell','Owen Parker','Talia Rivers','Miles Grant','Arielle Lane','Jonah Scott','Leah Kim','Mateo Collins','Sage Turner'),
    'buyer',
    ELT(((n - 1) % 5) + 1, 'First-Time Buyer','Relocation Buyer','Move-Up Buyer','Condo Buyer','Investor Buyer'),
    ELT(((n - 1) % 10) + 1, 'Dallas, TX','Tampa, FL','Phoenix, AZ','Atlanta, GA','Raleigh, NC','Austin, TX','Orlando, FL','Charlotte, NC','Nashville, TN','Scottsdale, AZ'),
    CONCAT('seed-buyer-', LPAD(n, 2, '0'), '@example.test'),
    NULL,
    CONCAT('images/reviews/review-', ((n - 1) % 4) + 1, '.svg'),
    5,
    CONCAT(
        ELT(((n - 1) % 5) + 1,
            'OmniReferral helped us understand what mattered before we spoke with an agent',
            'The buyer intake helped us compare neighborhoods without feeling rushed',
            'We came in with questions and moved from online browsing to a serious next step',
            'The experience helped us share our timing and budget with more confidence',
            'The clarity helped us stay organized while relocating from out of state'
        ),
        '. ',
        ELT(((n - 1) % 5) + 1,
            'The first agent conversation felt prepared and useful',
            'Every follow-up had a clear reason behind it',
            'We stopped repeating the same details to different people',
            'The process felt calmer and more trustworthy',
            'The handoff felt personal instead of automated'
        ),
        '. As a ',
        ELT(((n - 1) % 5) + 1, 'first-time buyer','relocation buyer','move-up buyer','condo buyer','investor buyer'),
        ' in ',
        ELT(((n - 1) % 10) + 1, 'Dallas, TX','Tampa, FL','Phoenix, AZ','Atlanta, GA','Raleigh, NC','Austin, TX','Orlando, FL','Charlotte, NC','Nashville, TN','Scottsdale, AZ'),
        ', ',
        ELT(((n - 1) % 6) + 1,
            'the experience gave us a practical plan instead of more noise',
            'we always understood why the next step mattered',
            'the handoff made the search feel more focused',
            'the communication felt calm even when timing was tight',
            'the process helped us make decisions with confidence',
            'we felt like our needs were actually read before anyone called'
        ),
        '.'
    ),
    NULL,
    NULL,
    IF(n IN (1,8,15,22,29), 1, 0),
    1,
    100 + n,
    'approved',
    NULL,
    DATE_SUB(@now, INTERVAL (130 - n) DAY),
    DATE_SUB(@now, INTERVAL (130 - n) DAY),
    @now
FROM omni_seed_numbers;

-- 30 Seller testimonials.
INSERT INTO omni_testimonial_seed
SELECT
    ELT(n, 'Marcus Hale','Priya Nelson','Camden Ross','Sofia Bell','Andre Mason','Vivian Cole','Grant Fisher','Noelle Bryant','Emmett Shaw','Ivy Wallace','Hannah Blake','Roman Diaz','Keira Stone','Felix Monroe','Aaliyah James','Bennett Wells','Maren Brooks','Silas Hughes','Tessa Ford','Adrian Quinn','Jade Spencer','Cole Bennett','Mira Weston','Finn Archer','Paige Holland','Rafael Dean','Selena Hart','Wyatt Palmer','Daphne Reed','Nico Warren'),
    'seller',
    ELT(((n - 1) % 5) + 1, 'Seller Client','Home Seller','Property Owner','Listing Client','Move-Out Seller'),
    ELT(((n - 1) % 10) + 1, 'Miami, FL','Houston, TX','Las Vegas, NV','Jacksonville, FL','Denver, CO','San Antonio, TX','Fort Worth, TX','Mesa, AZ','Columbus, OH','Savannah, GA'),
    CONCAT('seed-seller-', LPAD(n, 2, '0'), '@example.test'),
    NULL,
    CONCAT('images/reviews/review-', ((n - 1) % 4) + 1, '.svg'),
    5,
    CONCAT(
        ELT(((n - 1) % 5) + 1,
            'OmniReferral gave us a better way to explain our goals before the listing conversation',
            'Selling can get noisy, but this process helped us organize the details that usually get scattered',
            'The seller experience felt polished and helped us feel prepared for pricing questions',
            'The team helped us move quickly without losing a premium feel',
            'The handoff helped us connect with someone who already understood the situation'
        ),
        '. ',
        ELT(((n - 1) % 5) + 1,
            'The follow-up felt polished and professional',
            'The agent handoff was much smoother than expected',
            'We felt more confident before making decisions',
            'Communication stayed clear from start to finish',
            'The entire experience felt more credible'
        ),
        '. As a ',
        ELT(((n - 1) % 5) + 1, 'seller client','home seller','property owner','listing client','move-out seller'),
        ' in ',
        ELT(((n - 1) % 10) + 1, 'Miami, FL','Houston, TX','Las Vegas, NV','Jacksonville, FL','Denver, CO','San Antonio, TX','Fort Worth, TX','Mesa, AZ','Columbus, OH','Savannah, GA'),
        ', ',
        ELT(((n - 1) % 6) + 1,
            'the team respected our timeline and kept expectations clear',
            'we had cleaner notes ready before the listing conversation',
            'the process reduced the back-and-forth that usually slows things down',
            'the first follow-up already reflected what we had shared',
            'the experience felt organized without feeling impersonal',
            'we could tell the handoff was built for a real seller conversation'
        ),
        '.'
    ),
    NULL,
    NULL,
    IF(n IN (2,9,16,23,30), 1, 0),
    1,
    200 + n,
    'approved',
    NULL,
    DATE_SUB(@now, INTERVAL (100 - n) DAY),
    DATE_SUB(@now, INTERVAL (100 - n) DAY),
    @now
FROM omni_seed_numbers;

-- 30 Agent testimonials.
INSERT INTO omni_testimonial_seed
SELECT
    ELT(n, 'Jordan Miles','Kendall Reeves','Riley Sutton','Morgan Blake','Taylor Finch','Alexis Moore','Cameron Hayes','Reese Porter','Drew Lawson','Parker Silva','Emery Brooks','Quinn Carter','Hayden Ellis','Skyler James','Logan Pierce','Harper Lane','Casey Morgan','Blake Rivera','Ari Jordan','Dakota Gray','Rowan Bennett','Jules Foster','Milan Price','Remy Cole','Shawn Walker','Devon Cruz','Jamie Ellis','Avery Quinn','Robin Hart','Sloan Parker'),
    'agent',
    ELT(((n - 1) % 5) + 1, 'Broker Associate','Team Lead','Listing Specialist','Buyer Specialist','Managing Broker'),
    ELT(((n - 1) % 10) + 1, 'Boca Raton, FL','Dallas, TX','Austin, TX','Atlanta, GA','Charlotte, NC','Phoenix, AZ','Tampa, FL','Orlando, FL','Nashville, TN','Raleigh, NC'),
    CONCAT('seed-agent-', LPAD(n, 2, '0'), '@example.test'),
    NULL,
    CONCAT('images/reviews/review-', ((n - 1) % 4) + 1, '.svg'),
    5,
    CONCAT(
        ELT(((n - 1) % 5) + 1,
            'OmniReferral helped us understand lead intent before the first call',
            'The biggest change for our team was separating serious opportunities from casual inquiries',
            'The lead flow feels more intentional and gives our team better notes before outreach',
            'For agents, speed only matters when the context is good, and this protected follow-up time',
            'Our team needed cleaner handoffs and more consistent client context'
        ),
        '. ',
        ELT(((n - 1) % 5) + 1,
            'Our first conversations became much stronger',
            'We spent less time chasing vague leads',
            'The team could follow up faster and with better context',
            'Conversion quality improved without adding more admin work',
            'The experience felt more like a partner workflow than a lead list'
        ),
        '. As a ',
        ELT(((n - 1) % 5) + 1, 'broker associate','team lead','listing specialist','buyer specialist','managing broker'),
        ' in ',
        ELT(((n - 1) % 10) + 1, 'Boca Raton, FL','Dallas, TX','Austin, TX','Atlanta, GA','Charlotte, NC','Phoenix, AZ','Tampa, FL','Orlando, FL','Nashville, TN','Raleigh, NC'),
        ', ',
        ELT(((n - 1) % 6) + 1,
            'the notes helped our agents personalize outreach immediately',
            'the quality control made the opportunity easier to prioritize',
            'the process fit naturally into our existing follow-up rhythm',
            'our team had fewer cold starts and better opening conversations',
            'the platform helped us protect time for real client work',
            'the consistency across handoffs made coaching easier'
        ),
        '.'
    ),
    NULL,
    NULL,
    IF(n IN (3,10,17,24), 1, 0),
    1,
    300 + n,
    'approved',
    NULL,
    DATE_SUB(@now, INTERVAL (70 - n) DAY),
    DATE_SUB(@now, INTERVAL (70 - n) DAY),
    @now
FROM omni_seed_numbers;

-- 20 Community testimonials.
INSERT INTO omni_testimonial_seed
SELECT
    ELT(n, 'Iris Monroe','Graham Ellis','Maya Stone','Leo Carter','Nina Wells','Omar Bennett','Vera Quinn','Theo Brooks','Malia Foster','Cruz Harper','Elise Palmer','Dante Reed','June Porter','Kai Sullivan','Lena Wright','Miles Chen','Rhea Collins','Samir Grant','Tori Lane','Wesley Park'),
    'community',
    ELT(((n - 1) % 5) + 1, 'Community Member','Referral Partner','Platform User','Local Partner','Network Member'),
    ELT(((n - 1) % 10) + 1, 'Chicago, IL','Minneapolis, MN','Richmond, VA','Portland, OR','Salt Lake City, UT','Kansas City, MO','Greenville, SC','Boise, ID','Madison, WI','Knoxville, TN'),
    CONCAT('seed-community-', LPAD(n, 2, '0'), '@example.test'),
    NULL,
    CONCAT('images/reviews/review-', ((n - 1) % 4) + 1, '.svg'),
    5,
    CONCAT(
        ELT(((n - 1) % 5) + 1,
            'OmniReferral makes it easier to see how each side of the referral journey fits together',
            'From a community perspective, the strongest part is helping people share feedback without a complicated account flow',
            'The platform feels thoughtfully organized and builds trust around real follow-through',
            'The experience explains the difference between a lead and a qualified handoff',
            'What stood out was connecting local needs with a cleaner referral experience'
        ),
        '. ',
        ELT(((n - 1) % 5) + 1,
            'The platform felt active, credible, and easy to understand',
            'The communication felt more human than a typical marketplace',
            'The experience made the network feel more dependable',
            'The public pages gave us confidence in the process',
            'It was easy to see why agents and clients would use it again'
        ),
        '. As a ',
        ELT(((n - 1) % 5) + 1, 'community member','referral partner','platform user','local partner','network member'),
        ' in ',
        ELT(((n - 1) % 10) + 1, 'Chicago, IL','Minneapolis, MN','Richmond, VA','Portland, OR','Salt Lake City, UT','Kansas City, MO','Greenville, SC','Boise, ID','Madison, WI','Knoxville, TN'),
        ', ',
        ELT(((n - 1) % 6) + 1,
            'the experience made the network feel approachable',
            'the public proof helped the platform feel more established',
            'the flow made it easy to understand where each request goes',
            'the process felt designed around accountability',
            'the platform gave local users a clearer path to the right person',
            'the experience connected practical needs with a polished handoff'
        ),
        '.'
    ),
    NULL,
    NULL,
    IF(n IN (4,11,18), 1, 0),
    1,
    400 + n,
    'approved',
    NULL,
    DATE_SUB(@now, INTERVAL (40 - n) DAY),
    DATE_SUB(@now, INTERVAL (40 - n) DAY),
    @now
FROM omni_seed_numbers
WHERE n <= 20;

START TRANSACTION;

UPDATE testimonials AS t
JOIN omni_testimonial_seed AS s
    ON t.submitted_by_email = s.submitted_by_email
SET
    t.name = s.name,
    t.audience = s.audience,
    t.company = s.company,
    t.location = s.location,
    t.submitted_by_user_id = s.submitted_by_user_id,
    t.photo = s.photo,
    t.rating = s.rating,
    t.quote = s.quote,
    t.audio_path = s.audio_path,
    t.video_url = s.video_url,
    t.is_featured = s.is_featured,
    t.is_published = s.is_published,
    t.sort_order = s.sort_order,
    t.submission_status = s.submission_status,
    t.reviewed_by_user_id = s.reviewed_by_user_id,
    t.reviewed_at = s.reviewed_at,
    t.updated_at = @now;

INSERT INTO testimonials (
    name,
    audience,
    company,
    location,
    submitted_by_email,
    submitted_by_user_id,
    photo,
    rating,
    quote,
    audio_path,
    video_url,
    is_featured,
    is_published,
    sort_order,
    submission_status,
    reviewed_by_user_id,
    reviewed_at,
    created_at,
    updated_at
)
SELECT
    s.name,
    s.audience,
    s.company,
    s.location,
    s.submitted_by_email,
    s.submitted_by_user_id,
    s.photo,
    s.rating,
    s.quote,
    s.audio_path,
    s.video_url,
    s.is_featured,
    s.is_published,
    s.sort_order,
    s.submission_status,
    s.reviewed_by_user_id,
    s.reviewed_at,
    s.created_at,
    s.updated_at
FROM omni_testimonial_seed AS s
WHERE NOT EXISTS (
    SELECT 1
    FROM testimonials AS t
    WHERE t.submitted_by_email = s.submitted_by_email
);

COMMIT;

-- Verification queries.
SELECT COUNT(*) AS total_testimonials
FROM testimonials;

SELECT audience, COUNT(*) AS total_by_audience
FROM testimonials
WHERE is_published = 1
  AND submission_status = 'approved'
GROUP BY audience
ORDER BY FIELD(audience, 'buyer', 'seller', 'agent', 'community'), audience;

SELECT
    COUNT(*) AS approved_and_published,
    SUM(is_published = 1) AS published_count,
    SUM(submission_status = 'approved') AS approved_count
FROM testimonials;

SELECT COUNT(*) AS featured_count
FROM testimonials
WHERE is_featured = 1
  AND is_published = 1
  AND submission_status = 'approved';
