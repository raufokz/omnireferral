# GoHighLevel Onboarding Workflow Setup Guide

## Issue Summary

When a user fills the GoHighLevel onboarding form directly, the portal access email is not sent because the GoHighLevel workflow is not configured to send the webhook to the website.

## Root Cause

Database investigation revealed:
- **0 webhook events** for onboarding_completed
- **0 onboarding logs** 
- User exists but incomplete (onboarding_completed_at=NULL, must_reset_password=false, ghl_contact_id=NULL)

This means the GoHighLevel workflow is **NOT configured** to send the webhook to the website.

## Solution: Configure GoHighLevel Workflow

### Step 1: Access GoHighLevel Automation

1. Log in to your GoHighLevel account
2. Navigate to **Automation** → **Workflows**
3. Click **+ Create Workflow**

### Step 2: Configure Workflow Trigger

**Workflow Name:**
```
OmniReferral Onboarding Completed
```

**Trigger:**
- Type: **Form Submitted**
- Form: Select your **Onboarding Form**

**Filters (if available):**
- Only run for the correct onboarding form

### Step 3: Add Webhook Action

**Action 1: Webhook**

**Webhook Method:**
```
POST
```

**Webhook URL:**
```
https://omnireferrals.com/webhooks/gohighlevel/onboarding
```

**Headers:**
```
Content-Type: application/json
X-OmniReferral-Webhook: {your-webhook-secret}
```

**How to get webhook secret:**
1. Log in to OmniReferral admin dashboard
2. Navigate to **Admin** → **GoHighLevel** → **Settings**
3. Copy the **Webhook Secret** value
4. Use this exact value in the GoHighLevel webhook header

### Step 4: Configure Webhook Payload

Send these fields at minimum:

```json
{
  "event_type": "onboarding_completed",
  "form_name": "{{form.name}}",
  "form_id": "{{form.id}}",
  "contact_id": "{{contact.id}}",
  "first_name": "{{contact.first_name}}",
  "last_name": "{{contact.last_name}}",
  "name": "{{contact.name}}",
  "email": "{{contact.email}}",
  "phone": "{{contact.phone}}",
  "role": "agent",
  "source": "gohighlevel_onboarding",
  "submitted_at": "{{date.now}}"
}
```

**Also include all onboarding custom fields:**
- brokerage
- city
- state
- active_agent
- plan_id (if available)
- payment_id (if available)
- user_id (if passed as hidden field)
- Any other custom onboarding fields

### Step 5: Test the Workflow

1. Save and activate the workflow
2. Fill out the GoHighLevel onboarding form with test data
3. Check OmniReferral admin dashboard:
   - Navigate to **Admin** → **GoHighLevel** → **Logs**
   - Verify webhook event appears in "Webhook Events" section
   - Verify onboarding log appears in "Onboarding Sync Logs" section
4. Check if portal access email was sent to the test email

### Step 6: Troubleshooting

If webhook is not received:

**Check GoHighLevel:**
- Workflow execution history
- Webhook action response
- Webhook status code (should be 200)
- Payload sent
- Header sent
- Contact email value
- Correct form selected in trigger

**Check OmniReferral:**
- Webhook secret matches between GoHighLevel and OmniReferral settings
- Webhook URL is correct: `https://omnireferrals.com/webhooks/gohighlevel/onboarding`
- No firewall blocking the webhook
- Laravel logs: `storage/logs/laravel.log`
- Webhook events table in database

**Common Issues:**
1. **Webhook secret mismatch** - Ensure the secret in GoHighLevel header matches OmniReferral admin settings
2. **Wrong webhook URL** - Ensure the URL is exactly `https://omnireferrals.com/webhooks/gohighlevel/onboarding`
3. **Workflow not active** - Ensure the workflow is turned on/active
4. **Wrong form selected** - Ensure the correct onboarding form is selected in the trigger

## Website Backend Behavior

Once the webhook is properly configured, the website will:

1. **Verify webhook secret** using `X-OmniReferral-Webhook` header
2. **Accept JSON payload** from GoHighLevel
3. **Log raw payload** in webhook_events table
4. **Normalize GoHighLevel fields** to match database schema
5. **Match existing user** by:
   - ghl_contact_id
   - email
   - phone
6. **If user exists:**
   - Update missing fields
   - Set ghl_contact_id if missing
   - Set onboarding_completed_at = now()
   - Set status = active/approved
   - Enable portal access (must_reset_password = true)
7. **If user does not exist:**
   - Create user
   - role = agent/realtor
   - status = active/approved
   - onboarding_completed_at = now()
   - Generate secure set-password token
8. **Update or create realtor profile:**
   - user_id
   - brokerage_name
   - service_city
   - service_state
   - is_active_agent
   - profile_status = approved
   - submission_source = gohighlevel_onboarding
   - approved_at = now()
9. **Generate portal access link** using secure set-password
10. **Send portal access email** with temporary password
11. **Log email status** in onboarding_logs table

## Admin Dashboard Features

After webhook is configured, admins can:

**View Logs:**
- Navigate to **Admin** → **GoHighLevel** → **Logs**
- See all webhook events and onboarding logs
- Filter by event type, status, or search by email/remote ID
- View detailed webhook payload

**Resend Portal Access Email:**
- In the onboarding logs table, click "Resend Email" button
- Only available if:
  - User has email
  - Webhook was processed
  - Onboarding was completed
  - User status is active/approved
- If not eligible, exact reason is shown

**Retry Failed Webhooks:**
- In webhook events table, click "Retry" for pending events
- Re-sends the webhook payload to the endpoint

## Testing Checklist

- [ ] GoHighLevel workflow created with correct trigger
- [ ] Webhook action configured with correct URL
- [ ] Webhook secret configured in header
- [ ] Payload includes all required fields
- [ ] Workflow is active
- [ ] Test form submission completed
- [ ] Webhook event appears in OmniReferral logs
- [ ] Onboarding log appears in OmniReferral logs
- [ ] User created/updated in database
- [ ] Realtor profile created/updated
- [ ] Portal access email received
- [ ] User can log in with temporary password
- [ ] User can set their permanent password

## Security Notes

- Never share webhook secret publicly
- Use HTTPS for webhook URL
- Webhook secret should be unique and complex
- Rotate webhook secret periodically
- Monitor webhook logs for suspicious activity

## Support

If you encounter issues:

1. Check GoHighLevel workflow execution history
2. Check OmniReferral admin logs
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify webhook secret matches
5. Verify webhook URL is correct
6. Test webhook using Admin → GoHighLevel → Testing tools
