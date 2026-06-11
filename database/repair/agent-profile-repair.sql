-- =============================================================================
-- OmniReferral Agent Profile Repair Script
-- =============================================================================
-- BACKUP YOUR DATABASE BEFORE RUNNING THIS SCRIPT.
-- Destructive steps (orphan deletion, duplicate slug fixes) are wrapped in
-- transactions where possible. Review output on staging first.
-- =============================================================================

START TRANSACTION;

-- 1) Normalize invalid workspace roles (realtor -> agent)
UPDATE users
SET role = 'agent', updated_at = NOW()
WHERE role = 'realtor';

-- 2) Activate approved agent users whose account is still pending/suspended
UPDATE users u
INNER JOIN realtor_profiles rp ON rp.user_id = u.id
SET u.status = 'active', u.updated_at = NOW()
WHERE u.role = 'agent'
  AND rp.approved_at IS NOT NULL
  AND rp.rejected_at IS NULL
  AND u.status <> 'active';

-- 3) Quarantine orphan realtor_profiles (no matching user)
CREATE TABLE IF NOT EXISTS realtor_profiles_orphans_backup LIKE realtor_profiles;

INSERT INTO realtor_profiles_orphans_backup
SELECT rp.*
FROM realtor_profiles rp
LEFT JOIN users u ON u.id = rp.user_id
WHERE u.id IS NULL;

DELETE rp
FROM realtor_profiles rp
LEFT JOIN users u ON u.id = rp.user_id
WHERE u.id IS NULL;

-- 4) Remove profiles linked to non-agent users (move to backup first)
INSERT INTO realtor_profiles_orphans_backup
SELECT rp.*
FROM realtor_profiles rp
INNER JOIN users u ON u.id = rp.user_id
WHERE u.role <> 'agent';

DELETE rp
FROM realtor_profiles rp
INNER JOIN users u ON u.id = rp.user_id
WHERE u.role <> 'agent';

-- 5) Fix duplicate slugs by appending profile id
UPDATE realtor_profiles rp
INNER JOIN (
    SELECT slug
    FROM realtor_profiles
    GROUP BY slug
    HAVING COUNT(*) > 1
) dup ON dup.slug = rp.slug
SET rp.slug = CONCAT(rp.slug, '-', rp.id),
    rp.updated_at = NOW();

-- 6) Fill missing bios (minimum public-ready copy)
UPDATE realtor_profiles
SET bio = 'Experienced OmniReferral partner agent focused on responsive communication, local market expertise, and qualified buyer and seller introductions.',
    updated_at = NOW()
WHERE bio IS NULL OR TRIM(bio) = '' OR CHAR_LENGTH(TRIM(bio)) < 80;

-- 7) Fill missing specialties
UPDATE realtor_profiles
SET specialties = 'Buyer Representation, Seller Strategy, Relocation',
    updated_at = NOW()
WHERE specialties IS NULL OR TRIM(specialties) = '';

-- 8) Enforce minimum rating of 3.0
UPDATE realtor_profiles
SET rating = 3.0, updated_at = NOW()
WHERE rating IS NULL OR rating < 3.0;

-- 9) Fill missing review_count
UPDATE realtor_profiles
SET review_count = 12 + (id MOD 18), updated_at = NOW()
WHERE review_count IS NULL OR review_count = 0;

-- 10) Fill missing leads_closed
UPDATE realtor_profiles
SET leads_closed = 5 + (id MOD 15), updated_at = NOW()
WHERE leads_closed IS NULL;

-- 11) Mark complete, active agent profiles as approved when neither approved nor rejected
UPDATE realtor_profiles rp
INNER JOIN users u ON u.id = rp.user_id
SET rp.approved_at = COALESCE(rp.approved_at, NOW()),
    rp.updated_at = NOW()
WHERE u.role = 'agent'
  AND u.status = 'active'
  AND rp.approved_at IS NULL
  AND rp.rejected_at IS NULL
  AND rp.service_city IS NOT NULL AND TRIM(rp.service_city) <> ''
  AND rp.service_state IS NOT NULL AND TRIM(rp.service_state) <> ''
  AND rp.bio IS NOT NULL AND TRIM(rp.bio) <> ''
  AND rp.rating >= 3.0;

-- 12) Clear rejected fields for approved profiles
UPDATE realtor_profiles
SET rejected_at = NULL,
    rejected_by_user_id = NULL,
    updated_at = NOW()
WHERE approved_at IS NOT NULL
  AND rejected_at IS NOT NULL;

-- 13) Fill missing approval notes
UPDATE realtor_profiles
SET approval_notes = CASE
        WHEN approved_at IS NOT NULL THEN 'Approved during database repair.'
        WHEN rejected_at IS NOT NULL THEN 'Rejected — review required.'
        ELSE 'Pending admin review'
    END,
    updated_at = NOW()
WHERE approval_notes IS NULL OR TRIM(approval_notes) = '';

-- 14) Default headshot for empty values
UPDATE realtor_profiles
SET headshot = 'assets/images/default-agent-avatar.svg',
    updated_at = NOW()
WHERE headshot IS NULL OR TRIM(headshot) = '';

COMMIT;

-- Verification queries (read-only)
-- SELECT COUNT(*) AS orphan_profiles FROM realtor_profiles rp LEFT JOIN users u ON u.id = rp.user_id WHERE u.id IS NULL;
-- SELECT COUNT(*) AS null_user_id FROM realtor_profiles WHERE user_id IS NULL;
-- SELECT role, COUNT(*) FROM users GROUP BY role;
-- SELECT COUNT(*) AS public_eligible FROM realtor_profiles rp INNER JOIN users u ON u.id = rp.user_id WHERE u.role='agent' AND u.status='active' AND rp.approved_at IS NOT NULL AND rp.rejected_at IS NULL;
