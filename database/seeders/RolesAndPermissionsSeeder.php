<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! class_exists(\Spatie\Permission\Models\Role::class)
            || ! class_exists(\Spatie\Permission\Models\Permission::class)
            || ! class_exists(\Spatie\Permission\PermissionRegistrar::class)
        ) {
            // Spatie package not installed yet in this environment.
            return;
        }

        // Clear cached roles/permissions so changes apply immediately.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $permissions = collect([
            // Platform-wide
            'admin.access',
            'audit.view',
            'settings.manage',
            'integrations.manage',

            // Users & profiles
            'users.view',
            'users.create',
            'users.update',
            'users.suspend',
            'users.delete',
            'users.export',
            'realtor_profiles.view',
            'realtor_profiles.update',
            'realtor_profiles.approve',
            'realtor_profiles.reject',

            // Listings/properties
            'properties.view',
            'properties.create',
            'properties.update',
            'properties.delete',
            'properties.review',
            'properties.publish',
            'properties.unpublish',
            'properties.feature',

            // Leads
            'leads.view',
            'leads.update',
            'leads.assign',
            'leads.export',
            'leads.import',

            // Enquiries/contacts
            'enquiries.view',
            'enquiries.reply',
            'enquiries.export',
            'contacts.view',
            'contacts.moderate',

            // Packages/billing
            'packages.manage',

            // Affiliates
            'affiliates.manage',

            // Webhooks
            'webhook_events.view',
            'webhook_events.replay',

            // Content
            'blog.manage',
            'testimonials.manage',
            'partners.manage',
            'team.manage',
            'media.manage',
        ])->unique()->values();

        $this->upsertPermissions($permissions, $guard);

        // Roles: keep labels readable; Spatie treats these as the authoritative role names.
        $superAdmin = \Spatie\Permission\Models\Role::findOrCreate('Super Admin', $guard);
        $admin = \Spatie\Permission\Models\Role::findOrCreate('Admin', $guard);
        $staff = \Spatie\Permission\Models\Role::findOrCreate('Staff', $guard);
        $agent = \Spatie\Permission\Models\Role::findOrCreate('Agent', $guard);
        $seller = \Spatie\Permission\Models\Role::findOrCreate('Seller', $guard);
        $buyer = \Spatie\Permission\Models\Role::findOrCreate('Buyer', $guard);

        // Assign permissions. Super Admin gets allow-all via Gate::before (break-glass).
        $admin->syncPermissions([
            'admin.access',
            'audit.view',
            'settings.manage',
            'integrations.manage',
            'users.view',
            'users.create',
            'users.update',
            'users.suspend',
            'users.delete',
            'users.export',
            'realtor_profiles.view',
            'realtor_profiles.update',
            'realtor_profiles.approve',
            'realtor_profiles.reject',
            'properties.view',
            'properties.create',
            'properties.update',
            'properties.delete',
            'properties.review',
            'properties.publish',
            'properties.unpublish',
            'properties.feature',
            'leads.view',
            'leads.update',
            'leads.assign',
            'leads.export',
            'leads.import',
            'enquiries.view',
            'enquiries.reply',
            'enquiries.export',
            'contacts.view',
            'contacts.moderate',
            'blog.manage',
            'testimonials.manage',
            'partners.manage',
            'team.manage',
            'media.manage',
            'packages.manage',
            'affiliates.manage',
            'webhook_events.view',
            'webhook_events.replay',
        ]);

        // Staff: operational but constrained.
        $staff->syncPermissions([
            'admin.access',
            'audit.view',
            'users.view',
            'users.update',
            'users.export',
            'realtor_profiles.view',
            'properties.view',
            'properties.review',
            'leads.view',
            'leads.update',
            'leads.assign',
            'leads.export',
            'leads.import',
            'enquiries.view',
            'enquiries.reply',
            'enquiries.export',
            'contacts.view',
            'packages.manage',
            'affiliates.manage',
            'webhook_events.view',
        ]);

        // Agent/Seller/Buyer roles are primarily enforced via Policies + ownership rules.
        $agent->syncPermissions(['properties.create', 'properties.update', 'leads.view', 'contacts.view']);
        $seller->syncPermissions(['properties.create', 'properties.update', 'enquiries.view', 'contacts.view']);
        $buyer->syncPermissions(['enquiries.view', 'contacts.view']);

        // Ensure Super Admin role exists; permissions are effectively unlimited via Gate::before.
        $superAdmin->syncPermissions([]);
    }

    private function upsertPermissions(Collection $permissions, string $guard): void
    {
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission, $guard);
        }
    }
}

