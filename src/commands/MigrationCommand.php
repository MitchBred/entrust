<?php

declare(strict_types=1);

namespace Trebol\Entrust;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Trebol\Entrust
 */

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'entrust:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Entrust specifications.';

    /**
     * Execute the console command for Laravel 5.5+.
     */
    public function handle(): void
    {
        $this->laravel->view->addNamespace('entrust', substr(__DIR__, 0, -8).'views');

        $rolesTable          = Config::get('entrust.roles_table');
        $roleUserTable       = Config::get('entrust.role_user_table');
        $permissionsTable    = Config::get('entrust.permissions_table');
        $permissionRoleTable = Config::get('entrust.permission_role_table');

        $this->line('');
        $this->info( sprintf('Tables: %s, %s, %s, %s', $rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable) );

        $message = sprintf("A migration that creates '%s', '%s', '%s', '%s'", $rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable).
        " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');

        if ($this->confirm("Proceed with the migration creation? [Yes|no]", "Yes")) {

            $this->line('');

            $this->info("Creating migration...");
            if ($this->createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)) {

                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }

            $this->line('');

        }
    }

    /**
     * Create the migration.
     *
     * @param string $name
     */
    protected function createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable): bool
    {
        $migrationFile = base_path("/database/migrations")."/".date('Y_m_d_His')."_entrust_setup_tables.php";

        $userModelName = Config::get('auth.providers.users.model');
        $userModel = new $userModelName();
        $usersTable = $userModel->getTable();
        $userKeyName = $userModel->getKeyName();

        $data = ['rolesTable' => $rolesTable, 'roleUserTable' => $roleUserTable, 'permissionsTable' => $permissionsTable, 'permissionRoleTable' => $permissionRoleTable, 'usersTable' => $usersTable, 'userKeyName' => $userKeyName];

        $output = $this->laravel->view->make('entrust::generators.migration')->with($data)->render();

        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, (string) $output);
            fclose($fs);
            return true;
        }

        return false;
    }
}
