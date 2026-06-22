<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OmniReferral API',
    description: 'REST API for the OmniReferral real estate referral and lead management platform.

Provides authentication, user management, realtor profiles, GoHighLevel CRM integration,
onboarding webhooks, and administrative functions.

## Authentication
Most endpoints require a Bearer token obtained via the Login or Register endpoints.
Pass the token in the `Authorization` header as `Bearer <token>`.

## Webhooks
GoHighLevel webhook endpoints are public (CSRF exempt) and authenticated via
the `X-OmniReferral-Webhook` header secret.',
    contact: new OA\Contact(email: 'admin@omnireferrals.com'),
    license: new OA\License(name: 'Proprietary'),
)]
#[OA\Server(url: 'https://omnireferrals.com', description: 'OmniReferral Production API Server')]
#[OA\Server(url: 'http://localhost:8000', description: 'Local Development Server')]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'sanctum',
    description: 'Enter Sanctum token in format: Bearer <token>'
)]

#[OA\Tag(name: 'Auth', description: 'Authentication - login, logout, forgot/reset/set password')]
#[OA\Tag(name: 'Users', description: 'User profile management')]
#[OA\Tag(name: 'Realtors', description: 'Realtor/agent profiles, listing, search, approval')]
#[OA\Tag(name: 'GoHighLevel', description: 'GoHighLevel CRM integration - webhooks, contact sync, lead sync, event tracking')]
#[OA\Tag(name: 'Webhooks', description: 'Inbound webhook receivers for GoHighLevel and external systems')]
#[OA\Tag(name: 'Admin', description: 'Admin endpoints - dashboard, logs, settings, user management')]
#[OA\Tag(name: 'Email System', description: 'Email delivery, test tools, portal access resend, password reset emails')]
#[OA\Tag(name: 'Leads', description: 'Lead management - status updates, assignment, activity tracking')]
#[OA\Tag(name: 'Properties', description: 'Property listings - CRUD, review, publish, feature')]
#[OA\Tag(name: 'Enquiries', description: 'Enquiry management - view, reply, export')]
#[OA\Tag(name: 'Marketplace', description: 'Public property marketplace search and browsing')]
#[OA\Tag(name: 'Packages', description: 'Pricing plans and package management')]
#[OA\Tag(name: 'Content', description: 'Blog, testimonials, and content management')]

class OpenApiController extends Controller
{
    // This controller exists solely to host the OpenAPI attributes.
    // All endpoint attributes are on their respective controllers.
}
