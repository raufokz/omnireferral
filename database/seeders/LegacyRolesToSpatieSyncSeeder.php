<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class LegacyRolesToSpatieSyncSeeder extends Seeder
{
    /**
     * Attach Spatie roles from the legacy `users.role` / `users.is_super_admin` columns so
     * `can:` and permission checks remain consistent until registration flows assign roles.
     */
    public function run(): void
    {
        if (! class_exists(PermissionRegistrar::class)) {
            return;
        }

        $legacyToSpatie = [
            'admin' => 'Admin',
            'staff' => 'Staff',
            'agent' => 'Agent',
            'seller' => 'Seller',
            'buyer' => 'Buyer',
        ];

        foreach (User::query()->lazy() as $user) {
            if ($user->isSuperAdmin()) {
                $user->syncRoles(['Super Admin']);
                continue;
            }

            $slug = strtolower(trim((string) ($user->role ?? '')));
            $roleName = $legacyToSpatie[$slug] ?? null;
            if ($roleName !== null) {
                $user->syncRoles([$roleName]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
