<?php
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\ContactUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductFormatController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RetailerController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\VisibilityController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventoryTransferController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\RoutesController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\NewsletterSubscriptionController;
use App\Http\Controllers\Api\ShipmentTransportController;
use App\Http\Controllers\Api\PDFController;
use App\Http\Controllers\Api\RolesAndPermissionController;
use App\Http\Controllers\Api\RetailerPermsissionController;
use App\Http\Controllers\Api\InvoiceDetailController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->middleware('setAppLanguage')->group(function () {

            Route::post('login', [LoginController::class, 'login']);
            Route::get('addOrder', [CartController::class, 'addOrder']);
            Route::get('addOrderFromSupplier', [CartController::class, 'addOrderFromSupplier']);
            Route::post('loginWithOtherUser', [LoginController::class, 'loginWithOtherUser']);
            Route::post('checkHash', [LoginController::class, 'checkHash']);

            Route::post('registerRetailer', [LoginController::class, 'registerRetailer']);
            Route::get('emailVerified', [LoginController::class, 'emailVerification']);
            Route::post('forgotPassword', [LoginController::class, 'forgotPassword']);
            Route::post('resetPassword', [LoginController::class, 'resetPassword']);

            Route::get('getBusinessCategories', [LoginController::class, 'getBusinessCategories']);
            Route::get('getSiteLanguages', [LoginController::class, 'getSiteLanguages']);

            Route::get('getSubCategories', [ProductController::class, 'getSubCategories']);
            Route::get('getProductStyles', [ProductController::class, 'getProductStyles']);
            Route::get('getProductFormats', [ProductFormatController::class, 'getProductFormats']);
            Route::get('getTaxes', [TaxController::class, 'getTaxes']);
            Route::get('getVisibilities', [VisibilityController::class, 'getVisibilities']);
            Route::post('contactUs', [ContactUsController::class, 'contactUs']);
            Route::post('newsletter-subscription', [NewsletterSubscriptionController::class, 'store']);
            Route::get('generatePickupAndDeliveryTicket', [PDFController::class, 'generatePickupAndDeliveryTicket']);
            Route::get("downloadProductBarcode/{id}",[PDFController::class,'downloadProductBarcode']);

            //Delete User
            Route::get('deleteUser',[UsersController::class,'deleteUser']);

            // Post reports
            Route::post('PostReportProductList',[RolesAndPermissionController::class,'PostReportProductList']);
            Route::get('TotalCustomersDashboard',[RolesAndPermissionController::class,'TotalCustomers']);
            // form Data
            Route::get('Distributor_Pop_Data',[RolesAndPermissionController::class,'Distributor_formData']);

            //Dashboard
            Route::get('GetFullData',[RolesAndPermissionController::class,'GetFullData_Dashboard']);
            //Reports
            Route::get('GetFullDist_model',[RolesAndPermissionController::class,'GetFullDist_model']);
            Route::get('GetFullInventory_model',[RolesAndPermissionController::class,'GetFullInventory_model']);
            Route::get('GetFullSales_model',[RolesAndPermissionController::class,'GetFullSales_model']);
            //Post data
            Route::post('PostDist_ModelRequest',[RolesAndPermissionController::class,'PostDist_ModelRequest']);
            Route::post('PostProducts_Request',[RolesAndPermissionController::class,'PostProducts_Request']);
            Route::post('PostInventory_model', [RolesAndPermissionController::class,'PostInventory_model']);
            Route::post('PostFullSales_model', [RolesAndPermissionController::class,'PostFullSales_model']);
            Route::post('PostFullCustomMade_model', [RolesAndPermissionController::class,'PostFullCustomMade_model']);

Route::middleware(['auth:api'])->group(function(){
            Route::post('uploadData',[ProductFormatController::class,'uploaddata']);
            Route::get('getQrCodeImage', [LoginController::class, 'getQrCodeImage']);
            Route::get('validateOtp',[LoginController::class, 'validateOtp']);
            Route::post('logout', [LoginController::class, 'logout']);
            Route::post('changePassword', [LoginController::class, 'changePassword']);
            Route::post('getSimilarProducts', [ProductController::class, 'getSimilarProducts']);

  //Dashboard
            Route::get('GetSupplier_Dashboard',[RolesAndPermissionController::class,'GetSupplier_Dashboard']);
            Route::get('GetRetailers_Dashboard',[RolesAndPermissionController::class,'GetRetailers_Dashboard']);
            Route::get('GetDistributor_Dashboard',[RolesAndPermissionController::class,'GetDistributor_Dashboard']);
        // Retailer Section
Route::prefix('retailer')->group(function() {
            Route::get("getRetailerData",[ProfileController::class,'getRetailerData']);
            Route::post('editRetailerInfo', [ProfileController::class, 'editRetailerInfo']);
            Route::post('createRetailerProfile', [ProfileController::class, 'createRetailerProfile']);
            Route::post('saveRetailerMainAddress', [ProfileController::class, 'saveRetailerMainAddress']);
            Route::post('saveRetailerBillingAddress', [ProfileController::class, 'saveRetailerBillingAddress']);
            // //roles and Permissins
            // Route::get('getPermission',[RetailerPermsissionController::class,'getretailerPermission']);
            // Route::post('addRetailerRole', [RetailerPermsissionController::class, 'addretailerRole']);
            //     Route::get('RetailerRoleList', [RetailerPermsissionController::class, 'retailerRoleList']);
            //     Route::post('addRetailerUser', [RetailerPermsissionController::class, 'addretailerUser']);

            //     Route::get('viewRetailerRole/{id}', [RetailerPermsissionController::class, 'viewretailer']);
            //     Route::post('editRetailerRole/{id}/update', [RetailerPermsissionController::class, 'storeretailerPermissions']);
            //     Route::post('deleteUserRole', [RetailerPermsissionController::class, 'deleteretailerRole']);
            //     Route::get('getUserList',[RetailerPermsissionController::class,'getretailerUserList']);

        });

        // Distributor Section
Route::prefix('distributor')->group(function() {
            Route::get("getDistributorInfo",[ProfileController::class,'getDistributorInfo']);
            Route::post('editDistributorInfo', [ProfileController::class, 'editDistributorInfo']);

            Route::post('saveDistributorProfile', [ProfileController::class, 'saveDistributorProfile']);
            Route::post('saveDistributorAddress', [ProfileController::class, 'saveDistributorAddress']);
        });

Route::prefix('supplier')->group(function() {
            Route::get('topRetailerList',[RolesAndPermissionController::class,'topRetailerList']);
            Route::get('topProductList',[RolesAndPermissionController::class,'topProductList']);
            Route::get('getsalesReport',[RolesAndPermissionController::class,'getsalesReport']);
            Route::get('getinventoryReport',[RolesAndPermissionController::class,'getInventoryReport']);
            Route::get('getCustomReports',[RolesAndPermissionController::class,'getCustomReports']);
            Route::get('getSuperSalesReports',[RolesAndPermissionController::class,'getSuperSalesReports']);

            Route::get("getAllSupplierData",[ProfileController::class,'getAllSupplierData']);
            Route::get("getSupplierData",[ProfileController::class,'getSupplierData']);
            Route::post('editSupplierInfo', [ProfileController::class, 'editSupplierInfo']);
            Route::post('saveSupplierProfile', [ProfileController::class, 'saveSupplierProfile']);
            Route::post('saveSupplierAddress', [ProfileController::class, 'saveSupplierAddress']);
            Route::get('getProductFormatsDeposit', [ProductFormatController::class, 'getProductFormatsDeposit']);

            Route::post('depositUpdate',[ProductFormatController::class,'depositUpdate']);


            Route::post('link/distributors', [SupplierController::class, 'linkDistributors']);
            Route::get('getLinkedDistributors', [SupplierController::class, 'getLinkedDistributors']);
            Route::get('getLinkedDistributorsCompany', [SupplierController::class, 'getLinkedDistributorsCompany']);
            Route::get('getLinkedRetailers', [SupplierController::class, 'getLinkedRetailers']);
            //reports
            Route::get('reportSupplierCity',[RolesAndPermissionController::class,'GetRetailersCity']);
            Route::get('reportSupplierProduct',[RolesAndPermissionController::class,'GetSuppliersProductsName']);
            Route::get('reportFormdataProducttype',[RolesAndPermissionController::class,'GetSuppliersProductsType']);
            Route::get('reportFormdataProductstyle',[RolesAndPermissionController::class,'GetSuppliersProductStyle']);
            Route::get('reportFormdataProductformat',[RolesAndPermissionController::class,'GetSuppliersProductFormat']);
            Route::get('reportFormdataProductgroup',[RolesAndPermissionController::class,'GetSuppliergroup']);
            Route::get('reportFormdataSuppliersList',[RolesAndPermissionController::class,'GetSuppliersList']);
            Route::get('reportFormdataRetailersList',[RolesAndPermissionController::class,'GetRetailersList']);
            Route::get('reportFormdataDistributorList',[RolesAndPermissionController::class,'GetDistributorsList']);
            Route::get('reportSuppliername',[RolesAndPermissionController::class,'GetCompanyName']);
            Route::get('reportFormdataWarehousesList',[RolesAndPermissionController::class,'GetWarehousesList']);
            Route::get('reportFormdataUsersList',[RolesAndPermissionController::class,'GetUsersList']);
            Route::get('reportFormdataGetCadCsp',[RolesAndPermissionController::class,'GetCadCsp']);
            Route::get('reportFormdataRetailer', [RolesAndPermissionController::class,'GetRetailers']);

            //Post report data
            //Route::post('PostReportProductList',[RolesAndPermissionController::class,'PostReportProductList']);
            Route::post('PostReportInventoryList',[RolesAndPermissionController::class,'PostReportInventoryList']);
            Route::post('PostReportCustomList',[RolesAndPermissionController::class,'PostCustomReports']);
            Route::post('PostSuperSalesReports',[RolesAndPermissionController::class,'PostSuperSalesReports']);
            //invoices
            Route::get('getInvoiceList',[PDFController::class,'getinvoiceList']);
        });

            Route::get('getAllDistributors', [SupplierController::class, 'getAllDistributors']);

Route::middleware(['checkMyPermission'])->group(function() {
            Route::get('permissionList', [RolesController::class, 'permissionList']);
            Route::get('roleList', [RolesController::class, 'roleList']);
            Route::post('addRole', [RolesController::class, 'addRole']);
            Route::get("getRoleData/{id}",[RolesController::class, 'getRoleData']);
            Route::post('editRole', [RolesController::class, 'editRole']);
            Route::post('deleteRole', [RolesController::class, 'deleteRole']);
            // common api for user information

            Route::get("getUserData/{id}",[UsersController::class, 'getUserData']);
            Route::post('updateUserStatus', [UsersController::class, 'updateUserStatus']);

            // common api for user information

            Route::get('deliveryUserList', [UsersController::class, 'deliveryUserList']);
            Route::post('addDeliveryUser', [UsersController::class, 'addDeliveryUser']);

            Route::get('retailersList', [UsersController::class, 'retailersList']);
            Route::post('addRetailerUser', [UsersController::class, 'addRetailerUser']);
            Route::post('addRetailerProfile', [UsersController::class, 'addRetailerProfile']);

            // supplier-view, supplier-edit
            Route::get('suppliersList', [UsersController::class, 'suppliersList']);
            Route::get('supplierFilterList', [UsersController::class, 'supplierFilterList']);
            Route::post('addSupplierUser', [UsersController::class, 'addSupplierUser']);
            Route::post('updateSupplierUser/{id}', [UsersController::class, 'addSupplierUser']);
            Route::post('addSupplierProfile', [UsersController::class, 'addSupplierProfile']);
            Route::post('addSupplierAddress', [UsersController::class, 'addSupplierAddress']);
            Route::get('getSupplierDefaultPermissions', [UsersController::class, 'getSupplierDefaultPermissions']);
            Route::post('storeSupplierPermissions', [UsersController::class, 'storeSupplierPermissions']);
            Route::get("getSupplierUserData/{id}",[UsersController::class, 'getSupplierUserData']);

            // distributor-view, distributor-edit
            Route::get('distributorsList', [UsersController::class, 'distributorsList']);
            Route::get('distributorFilterList', [UsersController::class, 'distributorFilterList']);
            Route::post('addDistributorUser', [UsersController::class, 'addDistributorUser']);
            Route::post('updateDistributorUser/{id}', [UsersController::class, 'addDistributorUser']);
            Route::post('addDistributorProfile', [UsersController::class, 'addDistributorProfile']);
            Route::post('addDistributorAddress', [UsersController::class, 'addDistributorAddress']);
            Route::get('getDistributorDefaultPermissions', [UsersController::class, 'getDistributorDefaultPermissions']);
            Route::post('storeDistributorPermissions', [UsersController::class, 'storeDistributorPermissions']);
            Route::get("getDistributorUserData/{id}",[UsersController::class, 'getDistributorUserData']);

            // retailers-view, retailers-edit
            Route::get('retailersList', [UsersController::class, 'retailersList']);
            Route::get('retailerFilterList', [UsersController::class, 'retailerFilterList']);
            Route::post('addRetailerUser', [UsersController::class, 'addRetailerUser']);
            Route::post('updateRetailerUser/{id}', [UsersController::class, 'addRetailerUser']);
            Route::post('addRetailerProfile', [UsersController::class, 'addRetailerProfile']);
            Route::get("getRetailerUserData/{id}",[UsersController::class, 'getRetailerUserData']);


            // Retailer Section
Route::prefix('retailer')->group(function() {
                //roles and permission
                Route::get('getPermission',[RolesAndPermissionController::class,'getPermission']);
                Route::post('addRetailerRole', [RolesAndPermissionController::class, 'addRole']);
                Route::get('RetailerRoleList', [RolesAndPermissionController::class, 'SupplierRoleList']);
                Route::post('addRetailerUser', [RolesAndPermissionController::class, 'addSupplierUser']);

                Route::get('viewRetailerRole/{id}', [RolesAndPermissionController::class, 'viewSupplier']);
                Route::post('editRetailerRole/{id}/update', [RolesAndPermissionController::class, 'storeSupplierPermissions']);
                Route::post('deleteUserRole', [RolesAndPermissionController::class, 'deleteRole']);
                Route::get('getUserList',[RolesAndPermissionController::class,'getUserList']);

                Route::get('getSupplierAllProduct/{id}',[ProductController::class,'getSupplierAllProductList']);
                Route::get('getLocalSuppliers',[RetailerController::class,'getLocalSuppliers']);
                Route::get('getSupplierListOnDashboard',[RetailerController::class,'getSupplierListOnDashboard']);
                Route::get('getOrderListOnDashboard',[RetailerController::class,'getOrderListOnDashboard']);
                Route::post('sendRequestToSupplier',[RetailerController::class,'sendRequestToSupplier']);
                Route::post('getSupplierProductList',[ProductController::class,'getSupplierProductList']);
                Route::get('getProductDetail',[ProductController::class,'getProductDetail']);

                Route::get('suppliersList',[RetailerController::class,'suppliersList']);
                Route::get('suppliersAllList',[RetailerController::class,'suppliersAllList']);


                Route::post('addToCart',[CartController::class,'addToCart']);
                Route::get('getCartContent',[CartController::class,'cartListing']);
                Route::post('removeItemFromCart',[CartController::class,'removeItemFromCart']);
                Route::get('clearCart',[CartController::class,'clearCart']);

                Route::get('orderListing',[OrderController::class,'retailerOrderList']);

                //retailer Orders status Count
                Route::get('orderListingStatusCount', [OrderController::class, 'retailerOrderListCount']);
                Route::get('orderDetail/{id}', [OrderController::class, 'orderDetail']);
                Route::post('createOrderByRetailer',[CartController::class,'createOrderByRetailer']);

            });

            // Supplier - Products/Warehouse/Inventory/Stock/Pricing/Availability/Inventory Transfer/Order
Route::prefix('supplier')->group(function() {
                //supplier Dashboard

                //Route::get('Dashboard',[RolesAndPermissionController::class,'topRetailerList']);
                //roles and permission

                Route::get('getPermission',[RolesAndPermissionController::class,'getPermission']);
                Route::post('addSupplierRole', [RolesAndPermissionController::class, 'addRole']);
                Route::get('SupplierRoleList', [RolesAndPermissionController::class, 'SupplierRoleList']);
                Route::post('addSupplierUser', [RolesAndPermissionController::class, 'addSupplierUser']);

                Route::get('viewSupplierRole/{id}', [RolesAndPermissionController::class, 'viewSupplier']);
                Route::post('editSupplierRole/{id}/update', [RolesAndPermissionController::class, 'storeSupplierPermissions']);
                Route::post('deleteUserRole', [RolesAndPermissionController::class, 'deleteRole']);
                Route::get('getUserList',[RolesAndPermissionController::class,'getUserList']);

                Route::get('products', [ProductController::class, 'productsList']);
                Route::get('product/{id}', [ProductController::class, 'getProduct']);
                Route::post('product/add', [ProductController::class, 'addProduct']);
                Route::post('product/{id}/update', [ProductController::class, 'updateProduct']);
                // Route::get('product/{id}/deactivate', [ProductController::class, 'deactivateProduct']);
                Route::post('product/statusUpdate', [ProductController::class, 'deactivateProduct']);
                Route::get('productsHavingPricing', [ProductController::class, 'productsHavingPricingList']);

                Route::get('warehouses', [WarehouseController::class, 'warehousesList']);
                Route::get('warehouse/{id}', [WarehouseController::class, 'getWarehouse']);
                Route::post('warehouse/add', [WarehouseController::class, 'addWarehouse']);
                Route::post('warehouse/{id}/update', [WarehouseController::class, 'updateWarehouse']);

                Route::get('inventories', [InventoryController::class, 'inventoryList']);
                Route::get('inventory/{id}', [InventoryController::class, 'getInventory']);
                Route::post('inventory/add', [InventoryController::class, 'addInventory']);
                Route::post('inventory/{id}/update', [InventoryController::class, 'updateInventory']);
                Route::get('getInventoryProductList', [InventoryController::class, 'getInventoryProductList']);
                Route::get('inventory/getBatchNumberList/{product_id}', [InventoryController::class, 'getBatchNumberList']);
                Route::post('inventory/updateInventoryStock', [StockController::class, 'updateInventoryStock']);

                Route::get('transferwarehouse/{id}', [InventoryController::class, 'getTransferWarehouse']);

                Route::get('stocks', [StockController::class, 'stockList']);
                Route::get('stock/{id}', [StockController::class, 'getStock']);
                Route::post('stock/add', [StockController::class, 'addStock']);
                Route::post('stock/{id}/update', [StockController::class, 'updateStock']);

                Route::get('pricings', [PricingController::class, 'getPricingList']);
                Route::get('pricing/{id}', [PricingController::class, 'getPricing']);
                Route::post('pricing/add', [PricingController::class, 'addPricing']);

                Route::post('pricing/{id}/update', [PricingController::class, 'updatePricing']);

                Route::get('availabilities', [AvailabilityController::class, 'availabilityList']);
                Route::get('availability/{id}', [AvailabilityController::class, 'getAvailability']);
                Route::post('availability/add', [AvailabilityController::class, 'addAvailability']);
                Route::post('availability/{id}/update', [AvailabilityController::class, 'updateAvailability']);

                // SendRequestToRetailer
                Route::post("sendRequestToRetailer",[RetailerController::class,'sendRequestToRetailer']);
                Route::get('retailerRequests', [SupplierController::class, 'getRetailerRequests']);
                Route::post('retailerRequests/{id}/action', [SupplierController::class, 'actionOnRetailerRequest']);

                Route::get('groups', [GroupController::class, 'groupList']);
                Route::get('group/{id}', [GroupController::class, 'getGroup']);
                Route::post('group/add', [GroupController::class, 'addGroup']);
                Route::post('group/{id}/update', [GroupController::class, 'updateGroup']);

                Route::get('inventoryTransfers', [InventoryTransferController::class, 'getInventoryTransferList']);
                Route::get('inventoryTransfer/{id}', [InventoryTransferController::class, 'getInventoryTransfer']);
                Route::post('inventoryTransfer/add', [InventoryTransferController::class, 'addInventoryTransfer']);
                Route::post('inventoryTransfer/{id}/update', [InventoryTransferController::class, 'updateInventoryTransfer']);
                Route::get('ordersStatusCount', [OrderController::class, 'supplierOrderListCount']);
                Route::get('orders', [OrderController::class, 'supplierOrderList']);
                Route::get('orderDetail/{id}', [OrderController::class, 'orderDetail']);
                Route::post('order/add', [OrderController::class, 'addSupplierOrder']);
                Route::post('order/status/update', [OrderController::class, 'supplierOrderStatusUpdate']);
                Route::post('order/{id}/update', [OrderController::class, 'updateSupplierOrder']);
                Route::get('retailerList',[RetailerController::class,'retailerList']);
                Route::get('retailerList/{id}',[RetailerController::class,'retailerListDetail']);

                //invoices
                Route::get("orderInvoice/{id}",[InvoiceDetailController::class,'createOrderInvoice']);
                Route::get("creatOrderInvoice/{id}",[PDFController::class,'creatOrderInvoice']);


               //Product Style Post API
              Route::post('createProductStyles', [ProductController::class, 'createProductStyles']);
              //Product Formats Post API
              Route::post('createProductFormats', [ProductFormatController::class, 'createProductFormats']);
            });

            // distributor Api's product
Route::prefix('distributor')->group(function() {

                //roles and permission
                Route::get('getPermission',[RolesAndPermissionController::class,'getPermission']);
                Route::post('addDistributorRole', [RolesAndPermissionController::class, 'addRole']);
                Route::get('DistributorrRoleList', [RolesAndPermissionController::class, 'SupplierRoleList']);
                Route::post('addDistributorUser', [RolesAndPermissionController::class, 'addSupplierUser']);

                Route::get('viewDistributorRole/{id}', [RolesAndPermissionController::class, 'viewSupplier']);
                Route::post('editDistributorRole/{id}/update', [RolesAndPermissionController::class, 'storeSupplierPermissions']);
                Route::post('deleteUserRole', [RolesAndPermissionController::class, 'deleteRole']);
                Route::get('getUserList',[RolesAndPermissionController::class,'getUserList']);

                Route::get('products', [ProductController::class, 'productListForDistributor']);
                Route::get('product/{id}', [ProductController::class, 'getProductInformation']);
                // Routes
                Route::get('routes', [RoutesController::class, 'routesList']);
                Route::get('routes/{id}', [RoutesController::class, 'getRoutes']);
                Route::post('routes/add', [RoutesController::class, 'addRoutes']);
                Route::post('routes/{id}/update', [RoutesController::class, 'updateRoutes']);
                // Shipment
                Route::get('shipments', [ShipmentController::class, 'shipmentListing']);
                Route::get('shipments/{id}', [ShipmentController::class, 'getShipment']);
                Route::get('getPickupAndDeliveryTicket', [ShipmentController::class, 'getPickupAndDeliveryTicket']);
                Route::post('shipments/add', [ShipmentController::class, 'addShipment']);
                Route::get('getExistingShipments', [ShipmentController::class, 'getExistingShipments']);
                Route::post('shipments/updateStatus', [ShipmentController::class, 'updateShipmentStatus']);
                Route::post('shipments/updateShipmentDeliveryDate', [ShipmentController::class, 'updateShipmentDeliveryDate']);
                Route::post('shipments/getOrderListItems', [ShipmentController::class, 'shipmentOrderItemList']);
                // user can't update shipment date/route and number
                // Route::post('shipments/{id}/update', [ShipmentController::class, 'updateShipment']);

                Route::get('inventoryRecieve', [InventoryTransferController::class, 'getInventoryRecieveList']);
                Route::get('inventoryRecieve/{id}', [InventoryTransferController::class, 'getInventoryRecieve']);
                // Route::post('inventoryRecieve/add', [InventoryTransferController::class, 'addInventoryTransfer']);
                Route::post('inventoryRecieve/{id}/update', [InventoryTransferController::class, 'updateInventoryRecieve']);

                // warehouse
                Route::get('warehouses', [WarehouseController::class, 'warehousesList']);
                Route::get('warehouse/{id}', [WarehouseController::class, 'getWarehouse']);
                Route::post('warehouse/add', [WarehouseController::class, 'addWarehouse']);

                // inventories
                Route::get('getLinkedSuppliers', [DistributorController::class, 'getLinkedSuppliers']);

                Route::get('inventories', [InventoryController::class, 'inventoryList']);
                Route::get('inventory/{id}', [InventoryController::class, 'getInventory']);
                Route::get('inventory/getBatchNumberList/{product_id}', [InventoryController::class, 'getBatchNumberList']);
                Route::post('inventory/add', [InventoryController::class, 'addInventoryByDistributor']);
                Route::post('inventory/{id}/update', [InventoryController::class, 'updateInventoryByDistributor']);
                Route::post('inventories/hide', [InventoryController::class, 'hideInventory']);
                Route::post('inventory/updateInventoryStock', [StockController::class, 'updateInventoryStock']);

                // retailers section
                Route::get('retailersList',[DistributorController::class, 'retailersList']);
                Route::get("getRetailerUserData/{id}",[UsersController::class, 'getRetailerUserData']);
                Route::post('addRouteToRetailer',[DistributorController::class, 'addRouteToRetailer']);
                Route::post('removeRetailerFromRoutes',[DistributorController::class, 'removeRetailerFromRoutes']);

                // suppliers section
                Route::get('supplierList', [DistributorController::class, 'supplierList']);
                Route::get("getSupplierUserData/{id}",[UsersController::class, 'getSupplierUserData']);

                // orders section
                Route::get('orderListing', [OrderController::class, 'distributorOrderList']);
                Route::get('orderDetail/{id}', [OrderController::class, 'orderDetail']);
                Route::post("assignShipmentToOrder",[OrderController::class, 'assignShipmentToOrder']);
                // create transport
                Route::get('transport/list/{id}', [ShipmentTransportController::class, 'list']);
                Route::post("transport/add",[ShipmentTransportController::class, 'create']);
                Route::post("transport/update",[ShipmentTransportController::class, 'update']);
                Route::post("transport/updateShipmentOrderPosition",[ShipmentTransportController::class, 'updateShipmentOrderPosition']);
                Route::post("transport/remove",[ShipmentTransportController::class, 'remove']);
                Route::post('routeUpdate',[ShipmentTransportController::class,'routeUpdate']);
            });

        });
    });
});
