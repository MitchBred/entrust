<?php

declare(strict_types=1);

namespace Trebol\Entrust\Traits;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Trebol\Entrust
 */

use Illuminate\Support\Facades\Config;

trait EntrustPermissionTrait
{
    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('entrust.role'), Config::get('entrust.permission_role_table'), Config::get('entrust.permission_foreign_key'), Config::get('entrust.role_foreign_key'));
    }

    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     */
    public static function boot(): void
    {
        parent::boot();

        static::deleting(function($permission): true {
            if (!method_exists(Config::get('entrust.permission'), 'bootSoftDeletes')) {
                $permission->roles()->sync([]);
            }

            return true;
        });
    }
}
