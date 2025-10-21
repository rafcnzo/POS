<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 1. Buat Permissions ---
        $permissions = [
            // Admin
            'manage users', 'manage roles', 'manage system settings', 'manage backups',
            // Man Power
            'manage employees', 'manage payroll',
            // Kitchen/Bar Master
            'manage categories', 'manage ingredients', 'manage menus',
            // Kitchen/Bar Ops
            'create store requests', 'approve store requests', 'view store requests', 'manage energy cost',
            // Purchasing
            'manage suppliers', 'view purchase orders', 'create purchase orders', 'delete purchase orders', 'receive goods',
            // Accounting
            'manage supplier payments', 'view credit monitoring', 'view sales reports',
            // Cashier
            'access cashier terminal', 'view transaction history', 'process complimentary',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        $this->command->info('Permissions created.');

        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());
        $this->command->info('Super Admin role created and all permissions granted.');

        // HeadKitchen
        $roleHeadKitchen = Role::firstOrCreate(['name' => 'HeadKitchen']);
        $roleHeadKitchen->givePermissionTo([
            'manage categories', 'manage ingredients', 'manage menus',
            'create store requests', 'approve store requests', 'view store requests',
            'manage energy cost', 'view purchase orders', // Opsional bisa lihat PO
        ]);
        $this->command->info('HeadKitchen role created.');

        // HeadBar
        $roleHeadBar = Role::firstOrCreate(['name' => 'HeadBar']);
        $roleHeadBar->givePermissionTo([
            'manage categories', 'manage ingredients', 'manage menus',
            'create store requests', 'approve store requests', 'view store requests',
        ]);
        $this->command->info('HeadBar role created.');

        // Purchasing
        $rolePurchasing = Role::firstOrCreate(['name' => 'Purchasing']);
        $rolePurchasing->givePermissionTo([
            'manage suppliers',
            'view purchase orders', 'create purchase orders', 'delete purchase orders',
            'receive goods',
            'view store requests', // Untuk melihat SR yg perlu di-PO
        ]);
        $this->command->info('Purchasing role created.');

        // Accounting
        $roleAccounting = Role::firstOrCreate(['name' => 'Accounting']);
        $roleAccounting->givePermissionTo([
            'manage supplier payments', 'view credit monitoring', 'view sales reports',
            'manage payroll',
            'view purchase orders', // <-- Sesuai permintaanmu
        ]);
        $this->command->info('Accounting role created.');

        // Cashier
        $roleCashier = Role::firstOrCreate(['name' => 'Cashier']);
        $roleCashier->givePermissionTo([
            'access cashier terminal', 'view transaction history', 'process complimentary',
        ]);
        $this->command->info('Cashier role created.');

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]// Ganti password default
        );
        if ($adminUser) {
            $adminUser->assignRole($roleSuperAdmin);
            $this->command->info('Super Admin user created/assigned.');
        }
    }
}
