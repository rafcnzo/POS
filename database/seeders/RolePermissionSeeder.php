<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Admin
            'manage users', 'manage roles',

            // HR/Payroll
            'manage employees', 'manage payroll',

            // Kitchen Master
            'manage categories', 'manage ingredients', 'manage menus',

            // Kitchen Ops
            'create store requests', 'approve store requests', 'view store requests',
            'manage energy cost', 'view ffne',

            // Purchasing
            'view purchase orders', 'create purchase orders', 'delete purchase orders', 'receive goods',

            // Accounting
            'manage suppliers', 'manage supplier payments', 'view credit monitoring',

            // Reports
            'view sales reports', 'view profit loss report', 'view inventory mutation report',

            // Cashier
            'access cashier terminal', 'view transaction history',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        $this->command->info('Semua permissions telah dibuat.');

        // 1. Super Admin (Mendapat semua permission)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // 2. HeadBar
        $headBarRole = Role::firstOrCreate(['name' => 'HeadBar']);
        $headBarRole->givePermissionTo([
            'manage categories', 'manage ingredients', 'manage menus',
            'create store requests', 'approve store requests', 'view store requests',
            'manage energy cost', 'view ffne',
        ]);

        // 3. HeadKitchen
        $headKitchenRole = Role::firstOrCreate(['name' => 'HeadKitchen']);
        $headKitchenRole->givePermissionTo([
            'manage categories', 'manage ingredients', 'manage menus',
            'create store requests', 'approve store requests', 'view store requests',
            'manage energy cost', 'view ffne',
        ]);

        // 4. Accounting
        $accountingRole = Role::firstOrCreate(['name' => 'Accounting']);
        $accountingRole->givePermissionTo([
            'manage payroll', // Sesuai logika blade, payroll juga bisa diakses accounting
            'manage suppliers', 'manage supplier payments', 'view credit monitoring',
            'view sales reports', 'view profit loss report', 'view inventory mutation report',
        ]);

        // 5. Purchasing
        $purchasingRole = Role::firstOrCreate(['name' => 'Purchasing']);
        $purchasingRole->givePermissionTo([
            'view purchase orders', 'create purchase orders', 'delete purchase orders',
            'receive goods',
        ]);

        // 6. Cashier
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);
        $cashierRole->givePermissionTo([
            'access cashier terminal', 'view transaction history',
        ]);

        $this->command->info('Semua role telah dibuat dan permission telah diberikan.');

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'], // Ganti dengan email admin Anda
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // GANTI DENGAN PASSWORD YANG AMAN
            ]
        );

        $adminUser->assignRole($superAdminRole);

        $this->command->info('User Super Admin default telah dibuat.');
    }
}
