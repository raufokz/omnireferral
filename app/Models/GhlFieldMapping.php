<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GhlFieldMapping extends Model
{
    protected $table = 'ghl_field_mappings';

    protected $fillable = [
        'ghl_field',
        'db_table',
        'db_column',
        'label',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('db_table')->orderBy('ghl_field');
    }

    /**
     * All supported db_table values.
     */
    public static function supportedTables(): array
    {
        return ['users', 'realtor_profiles', 'buyer_profiles'];
    }

    public function tableLabel(): string
    {
        return match ($this->db_table) {
            'users'            => 'Users',
            'realtor_profiles' => 'Realtor Profile',
            'buyer_profiles'   => 'Buyer Profile',
            default            => ucfirst(str_replace('_', ' ', $this->db_table)),
        };
    }
}
