<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\OrderDistributor;
use App\Models\Cart;
use App\Models\OrderHistory;
use Ramsey\Uuid\Uuid;
use DB;
use App\Models\Pricing;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CartController extends Controller
{
    public $permission;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permission = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function addToCart(Request $request)
    {
        if($this->permission !== "marketplace-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $rules = [
            'product_id' => 'required|exists:App\Models\Product,id',
            'product_name' => 'required|exists:App\Models\Product,product_name',
            'price' => 'required',
            'quantity' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        // dd($request->all(),var_dump($request->input("attributes")));
        $user = auth()->user();
        $user_id = $user->id;
        $product_id = $request->input("product_id");
        $product_name = $request->input("product_name");
        $price = $request->input("price");
        $quantity = $request->input("quantity");
        $attributes = $request->input("attributes");
        $row_id = uniqid();

        $cartData = array(
                            'row_id' => $row_id,
                            'user_id' => $user_id,
                            'product_id' => $product_id,
                            'product_name' => $product_name,
                            'price' => $price,
                            'quantity' => $quantity,
                            'attributes' => $attributes
                        );
        $userData = Cart::updateOrCreate(["user_id" => $user_id,"product_id" => $product_id],$cartData);

        $success  = [];
        $message  = Lang::get("messages.add_to_cart_successfully");
        return sendResponse($success, $message);
    }

    public function cartListing(Request $request)
    {
        if($this->permission !== "marketplace-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();
        $cartListing = Cart::where('user_id',$user->id)->with('productInfo','userProfile')->get();
        foreach($cartListing as $key => $value)
        {
            // dd($value->productInfo->product_format);
           
            $price = Pricing::where('product_id',$value->product_id)->first();
            // dd($price);
            if(!empty($price))
            {
                $value->unit_price = $price->price;
            }
        }
        $success  = $cartListing;
        $message  = Lang::get("messages.cart_listing");
        return sendResponse($success, $message);
    }

    public function removeItemFromCart(Request $request)
    {
        if($this->permission !== "marketplace-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $rules = [
            'product_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $user = auth()->user();
        Cart::where(["product_id" => $request->input("product_id"),"user_id"=>$user->id])->delete();
        $success  = [];
        $message  = Lang::get("messages.remove_item_successfully");
        return sendResponse($success, $message);
    }

    public function clearCart(Request $request)
    {
        if($this->permission !== "marketplace-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        Cart::where(["user_id"=>$user->id])->delete();

        $success  = [];
        $message  = Lang::get("messages.clear_cart_successfully");
        return sendResponse($success, $message);
    }

    public function createOrderByRetailer(Request $request)
    {
        if($this->permission !== "marketplace-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();

        $cartItems = Cart::where("user_id",$user->id)->get();
        if($cartItems->count() < 1){
            return sendError(Lang::get('item_not_found'), Lang::get("no_item_cart"), 404);
        }

        try{
            $productArray = $request->input("cart_items");

            $order_date = date("Y-m-d");
            $createdOn = date("Y-m-d H:i:s");
            $parent_id = uniqid();
            foreach($productArray as $key=> $cartInfo){
                $orderInfo = Order::where(["retailer_id" => $user->id, "supplier_id" => $cartInfo['supplier_id'], "created_at" => $createdOn])->first();
                if($orderInfo == null)
                {
                    $orderInsertData = [
                                        'supplier_id' => $cartInfo['supplier_id'],
                                        'retailer_id' => $user->id,
                                        'order_reference' => hexdec(uniqid()),
                                        'added_by' => $user->id,
                                        'order_date' => $order_date,
                                        'created_at' => $createdOn,
                                        'added_by_user_type' => 'retailer',
                                        'parent_id' => $parent_id,
                                        ];
                    $orderInfo = Order::create($orderInsertData);
                    // Add Order History
                    OrderHistory::create([
                        'order_id' => $orderInfo->id,
                        'user_id' => $user->id,
                        'content' => 'order_placed',
                        'datetime' => Carbon::now()
                    ]);
                }
                $order_id = $orderInfo->id;
                $productInfo = Product::with(['pricing','inventory'])->where('id',$cartInfo['product_id'])->first();
                $orderItemInsertData = [
                                        'order_id' => $order_id,
                                        'product_id' => $cartInfo['product_id'],
                                        'product_style_id' => $productInfo->style,
                                        'product_format_id'  => $productInfo->product_format,
                                        'quantity' => $cartInfo['quantity'],
                                        'price' => $productInfo->pricing->unit_price,
                                        // 'tax' => '2',
                                        // 'sub_total' => ($productInfo->pricing->unit_price * $cartInfo['quantity']) + 2,
                                        'sub_total' => ($productInfo->pricing->unit_price * $cartInfo['quantity']) ,

                                        'created_at' => $createdOn,
                                        ];
                $orderItemInfo = OrderItem::create($orderItemInsertData);

                $orderDistributorInsertData = [
                                                "order_id" => $order_id,
                                                "order_item_id" => $orderItemInfo->id,
                                                "distributor_id" => $productInfo->inventory->first()->distributor_id,
                                                'created_at' => $createdOn,
                                                ];
                OrderDistributor::create($orderDistributorInsertData);
            }
            $success  = [];
            $message  = Lang::get("messages.order_added_successfully");
            return sendResponse($success, $message);
        }
        catch(Exception $e){
            return sendError(Lang::get('something_went_wrong'), Lang::get('messages.something_went_wrong'), 500);
        }
    }

    // dummy retailer product add in orders with new concept
    public function addOrder(Request $request)
    {
        $productArray = [
        ['product_id'=>5,'supplier_id'=>9,'retailer_id'=>68, 'quantity'=>2],
        ['product_id' => 12,'supplier_id' => 9,'retailer_id' => 68,'quantity' => 2 ],
        ['product_id' => 15,'supplier_id' => 9,'retailer_id' => 68,'quantity' => 2],
        ['product_id' => 3,'supplier_id' => 138,'retailer_id' => 68,'quantity' => 2]
                        ];
        $user_id = 68;
        $order_date = date("Y-m-d");
        $createdOn = date("Y-m-d H:i:s");
        // $totalQuantity = array_sum($productArray['quantity']);
        // $productArray = (object) $productArray;
        // dd($productArray);
        $parent_id = uniqid();
        // dd($parent_id);
        foreach($productArray as $key=> $cartInfo){

            $orderInfo = Order::where(["retailer_id" => $cartInfo['retailer_id'], "supplier_id" => $cartInfo['supplier_id'], "created_at" => $createdOn])->first();
            if($orderInfo == null)
            {
                $orderInsertData = [
                                    'supplier_id' => $cartInfo['supplier_id'],
                                    'retailer_id' => $cartInfo['retailer_id'],
                                    'order_reference' => hexdec(uniqid()),
                                    'added_by' => $user_id,
                                    'order_date' => $order_date,
                                    'created_at' => $createdOn,
                                    'added_by_user_type' => 'retailer',
                                    'note' => "test",
                                    'total_quantity' => '8',
                                    'total_amount' => '32',
                                    'parent_id' => $parent_id,
                                    ];
                $orderInfo = Order::create($orderInsertData);
            }
            // dd($orderInfo);
            $order_id = $orderInfo->id;
            // dd($order_id);
            $productInfo = Product::with(['pricing','inventory'])->where('id',$cartInfo['product_id'])->first();
            // dd($productInfo);
            // $orderItemInfo = OrderItem::where(['order_id' => $order_id, "created_at" => $createdOn, 'product_id' => $cartInfo['product_id']])->first();
            // if($orderItemInfo == null)
            // {

                $orderItemInsertData = [
                                        'order_id' => $order_id,
                                        'product_id' => $cartInfo['product_id'],
                                        'product_style_id' => $productInfo->style,
                                        'product_format_id'  => $productInfo->product_format,
                                        'quantity' => $cartInfo['quantity'],
                                        'price' => $productInfo->pricing->unit_price,
                                        'tax' => '2',
                                        'sub_total' => ($productInfo->pricing->unit_price * $cartInfo['quantity']) + 2,
                                        'created_at' => $createdOn,
                                        ];
                // dd($orderItemInsertData);
                $orderItemInfo = OrderItem::create($orderItemInsertData);
            // }

            $orderDistributorInsertData = [
                                            "order_id" => $order_id,
                                            "order_item_id" => $orderItemInfo->id,
                                            "distributor_id" => $productInfo->inventory->distributor_id,
                                            'created_at' => $createdOn,
                                            ];
            OrderDistributor::create($orderDistributorInsertData);
        }
    }

    public function addOrderFromSupplier(Request $request)
    {
        $productArray = [
        [   'product_id' => 12,'supplier_id' => 9,'retailer_id' => 68,'quantity' => 2,'distributor_id' =>46 ],
        [   'product_id' => 15,'supplier_id' => 9,'retailer_id' => 68,'quantity' => 2,'distributor_id' =>46 ]
                        ];
        $user_id = 9;
        $order_date = date("Y-m-d");
        $createdOn = date("Y-m-d H:i:s");
        $parent_id = uniqid();
        // hexdec is using to convert hexadecimal to numeric
        // dechex is using to convert numeric to hexadecimal
        foreach($productArray as $key=> $cartInfo){

            $orderInfo = Order::where(["retailer_id" => $cartInfo['retailer_id'], "supplier_id" => $cartInfo['supplier_id'], "created_at" => $createdOn])->first();
            if($orderInfo == null)
            {
                $orderInsertData = [
                                    'supplier_id' => $cartInfo['supplier_id'],
                                    'retailer_id' => $cartInfo['retailer_id'],
                                    'order_reference' => hexdec(uniqid()),
                                    'added_by' => $user_id,
                                    'order_date' => $order_date,
                                    'created_at' => $createdOn,
                                    'added_by_user_type' => 'supplier',
                                    'note' => "test",
                                    'total_quantity' => '8',
                                    'total_amount' => '32',
                                    'parent_id' => $parent_id,
                                    ];
                $orderInfo = Order::create($orderInsertData);
            }
            // dd($orderInfo);
            $order_id = $orderInfo->id;
            // dd($order_id);
            $productInfo = Product::with(['pricing'])->where('id',$cartInfo['product_id'])->first();
            $orderItemInsertData = [
                                    'order_id' => $order_id,
                                    'product_id' => $cartInfo['product_id'],
                                    'product_style_id' => $productInfo->style,
                                    'product_format_id'  => $productInfo->product_format,
                                    'quantity' => $cartInfo['quantity'],
                                    'price' => $productInfo->pricing->unit_price,
                                    'tax' => '2',
                                    'sub_total' => ($productInfo->pricing->unit_price * $cartInfo['quantity']) + 2,
                                    'created_at' => $createdOn,
                                    ];
            $orderItemInfo = OrderItem::create($orderItemInsertData);
            
            $orderDistributorInsertData = [
                                            "order_id" => $order_id,
                                            "order_item_id" => $orderItemInfo->id,
                                            "distributor_id" => $cartInfo['distributor_id'],
                                            'created_at' => $createdOn,
                                            ];
            OrderDistributor::create($orderDistributorInsertData);
        }
    }

}
