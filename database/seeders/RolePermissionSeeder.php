<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'manage-menus']);
        Permission::create(['name' => 'manage-stocks']);
        Permission::create(['name' => 'process-orders']);
        Permission::create(['name' => 'view-sales-reports']);
        Permission::create(['name' => 'manage-users']);

        $roleHeadBar = Role::create(['name' => 'HeadBar']);
        $roleHeadBar->givePermissionTo(['manage-menus', 'manage-stocks']);

        $roleHeadKitchen = Role::create(['name' => 'HeadKitchen']);
        $roleHeadKitchen->givePermissionTo(['manage-menus', 'manage-stocks']);

        $roleCashier = Role::create(['name' => 'Cashier']);
        $roleCashier->givePermissionTo('process-orders');

        $roleAccounting = Role::create(['name' => 'Accounting']);
        $roleAccounting->givePermissionTo('view-sales-reports');

        $roleSuperAdmin = Role::create(['name' => 'Super Admin']);

        // Create Super Admin user
        $userSuperAdmin = User::factory()->create([
            'name'     => 'Super Admin',
            'email'    => 'admin433@restoku.com',
            'password' => bcrypt('admin1234'), // Ganti dengan password yang aman
        ]);
        $userSuperAdmin->assignRole($roleSuperAdmin);

        // Create HeadBar user
        $userHeadBar = User::factory()->create([
            'name'     => 'Head Bar',
            'email'    => 'headbar@restoku.com',
            'password' => bcrypt('headbar123'),
        ]);
        $userHeadBar->assignRole($roleHeadBar);

        // Create HeadKitchen user
        $userHeadKitchen = User::factory()->create([
            'name'     => 'Head Kitchen',
            'email'    => 'headkitchen@restoku.com',
            'password' => bcrypt('headkitchen123'),
        ]);
        $userHeadKitchen->assignRole($roleHeadKitchen);

        // Create Cashier user
        $userCashier = User::factory()->create([
            'name'     => 'Cashier',
            'email'    => 'cashier@restoku.com',
            'password' => bcrypt('cashier123'),
        ]);
        $userCashier->assignRole($roleCashier);

        // Create Accounting user
        $userAccounting = User::factory()->create([
            'name'     => 'Accounting',
            'email'    => 'accounting@restoku.com',
            'password' => bcrypt('accounting123'),
        ]);
        $userAccounting->assignRole($roleAccounting);
    }
}
