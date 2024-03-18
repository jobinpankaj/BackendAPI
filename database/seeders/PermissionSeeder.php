<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $distributor_modules = [
        	'role-management'   => [
                                    "role-view",
                                    "role-edit",
                                ],
        	'order-management'  => [
                                    "order-view",
                                    "order-edit",
                                ],
        	'inventory-management'   => [
                                    "inventory-view",
                                    "inventory-edit",
                                ],
        	'user-management'   => [
                                    "user-view",
                                    "user-edit",
                                ],
        	'product-management' => [
                                    "product-view",
                                    "product-edit",
                                ],
        	'routes-management'=> [
                                    "routes-view",
                                    "routes-edit",
                                ],
            'shipment-management' => [
                                    "shipment-view",
                                    "shipment-edit",
                                ],
            'retailers-management' => [
                                    "retailers-view",
                                    "retailers-edit",
                                ],
            'reports-management' => [
                                    "reports-view",
                                    "reports-edit",
                                ],
            'dashboard-management' => [
                                    "dashboard-view",
                                    "dashboard-edit",
                                ],
        ];
        $supplier_modules = [
            'role-management'   => [
                                    "role-view",
                                    "role-edit",
                                ],
            'order-management'  => [
                                    "order-view",
                                    "order-edit",
                                ],
            'inventory-management'   => [
                                    "inventory-view",
                                    "inventory-edit",
                                ],
            'user-management'   => [
                                    "user-view",
                                    "user-edit",
                                ],
            'product-management' => [
                                    "product-view",
                                    "product-edit",
                                ],
            'groups-management'=> [
                                    "groups-view",
                                    "groups-edit",
                                ],
            'pricing-management' => [
                                    "pricing-view",
                                    "pricing-edit",
                                ],
            'retailers-management' => [
                                    "retailers-view",
                                    "retailers-edit",
                                ],
            'reports-management' => [
                                    "reports-view",
                                    "reports-edit",
                                ],
            'dashboard-management' => [
                                    "dashboard-view",
                                    "dashboard-edit",
                                ],
        ];
        $retailer_modules = [
            'role-management'   => [
                                    "role-view",
                                    "role-edit",
                                ],
            'order-management'  => [
                                    "order-view",
                                    "order-edit",
                                ],
            'marketplace-management'   => [
                                    "marketplace-view",
                                    "marketplace-edit",
                                ],
            'user-management'   => [
                                    "user-view",
                                    "user-edit",
                                ],
            'account-management' => [
                                    "account-view",
                                    "account-edit",
                                ],
            'suppliers-management' => [
                                    "suppliers-view",
                                    "suppliers-edit",
                                ],
            'reports-management' => [
                                    "reports-view",
                                    "reports-edit",
                                ],
            'dashboard-management' => [
                                    "dashboard-view",
                                    "dashboard-edit",
                                ],
        ];
        foreach ($distributor_modules as $module=>$permissions) {
            foreach($permissions as $permission)
            {
                Permission::create(["module_name"=>$module,'name' => $permission, 'guard_name' => 'distributor']);
            }
        }

        foreach ($supplier_modules as $module=>$permissions) {
            foreach($permissions as $permission)
            {
                Permission::create(["module_name"=>$module,'name' => $permission,'guard_name' => 'supplier']);
            }
        }

        foreach ($retailer_modules as $module=>$permissions) {
            foreach($permissions as $permission)
            {
                Permission::create(["module_name"=>$module,'name' => $permission,'guard_name' => 'retailer']);
            }
        }
        // $role = Role::create(['name' => 'Supplier Admin']);
        // $permissions = Permission::where("guard_name","=","supplier")->pluck('id','id');
        // $role->syncPermissions($permissions);


        // $role = Role::create(['name' => 'Retailer Admin']);
        // $permissions = Permission::where("guard_name","=","retailer")->pluck('id','id');
        // $role->syncPermissions($permissions);
    }
}
