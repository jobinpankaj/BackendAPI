<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pricing;
use App\Models\Product;
use App\Models\ProductFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;

class PricingController extends Controller
{
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }

    public function getPricingList()
    {
        if($this->permisssion !== "pricing-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $productsWithPricing = Product::has('pricing')->with(['userInformation','userProfile', 'pricing','productFormat'])->where('user_id', $user->id)->get();

        $success = $productsWithPricing;
        $message = Lang::get("messages.pricing_list");
        return sendResponse($success, $message);
    }

    public function getPricing($id)
    {
        if($this->permisssion !== "pricing-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        // $user = auth()->user();
        // $pricing = Pricing::with(['product','productformat'])->get();
        $pricing = Pricing::find($id);
        if(!empty($pricing))
        {
            $product = Product::where('id',$pricing->product_id)->first();
            // dd($product);
            $product_format = ProductFormat::where('id',$product->product_format)->first();
            

            $pricing->product_name = $product->product_name;
            $pricing->product_type = $product->product_type;
            $pricing->product_format = $product_format;
        }

        if(!$pricing) {
            return sendError(Lang::get('messages.pricing_not_found'), Lang::get('messages.pricing_not_found'), 404);
        }
        
        $success = $pricing;
        $message = Lang::get("messages.pricing_detail");
        return sendResponse($success, $message);
    }

    public function addPricing(Request $request)
    {
        if($this->permisssion !== "pricing-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'price' => 'required|numeric',
            'tax_id' => 'required|numeric|exists:taxes,id',
            'unit_price' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'suggest_retail_price' => 'required|boolean',
            'retail_unit_price' => 'nullable|numeric',
            'discount_percent' => 'nullable|numeric',
            'discount_name' => 'nullable|string',
            'discount_type' => 'nullable|string',
            'purchase_qty' => 'nullable|numeric',
            'is_minimum' => 'nullable|boolean',
            'discount_as_of' => 'nullable|string',
            'specific_audience' => 'nullable|boolean',
            'group_id' => 'nullable|numeric|exists:groups,id',
            'company_id' => 'nullable|numeric'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $pricing = Pricing::create([
            'product_id' => $validated['product_id'],
            'price' => $validated['price'],
            'tax_id' => $validated['tax_id'],
            'unit_price' => $validated['unit_price'] ?? null,
            'tax_amount' => $validated['tax_amount'] ?? null,
            'suggest_retail_price' => $validated['suggest_retail_price'],
            'retail_unit_price' => $validated['retail_unit_price'] ?? null,
            'total_price' => $validated['total_price'] ?? null,
            'total_unit_price' => $validated['total_unit_price'] ?? null,
            'total_retail_price' => $validated['total_retail_price'] ?? null,
            'discount_percent' => $validated['discount_percent'] ?? null,
            'discount_name' => $validated['discount_name'] ?? null,
            'discount_type' => $validated['discount_type'] ?? null,
            'purchase_qty' => $validated['purchase_qty'] ?? null,
            'is_minimum' => $validated['is_minimum'] ?? false,
            'discount_as_of' => $validated['discount_as_of'] ?? null,
            'specific_audience' => $validated['specific_audience'] ?? false,
            'group_id' => $validated['group_id'] ?? null,
            'company_id' => $validated['company_id'] ?? null,
        ]);

        $success = $pricing;
        $message = Lang::get("messages.pricing_created_successfully");
        return sendResponse($success, $message);
    }

    // public function editPricing(Request $request ,$id)
    // {
    //     if($this->permisssion !== "pricing-edit")
    //     {
    //         return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
    //     }
    //     dd(auth()->user()->id);
    // }

    public function updatePricing(Request $request, $id)
    {
        if($this->permisssion !== "pricing-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'price' => 'required|numeric',
            'tax_id' => 'required|numeric|exists:taxes,id',
            'unit_price' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'suggest_retail_price' => 'required|boolean',
            'retail_unit_price' => 'nullable|numeric',
            'discount_percent' => 'nullable|numeric',
            'discount_name' => 'nullable|string',
            'discount_type' => 'nullable|string',
            'purchase_qty' => 'nullable|numeric',
            'is_minimum' => 'nullable|boolean',
            'discount_as_of' => 'nullable|string',
            'specific_audience' => 'nullable|boolean',
            'group_id' => 'nullable|numeric|exists:groups,id',
            'company_id' => 'nullable|numeric'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        
        $validated = $request->all();

        $pricing = Pricing::find($id);

        if(!$pricing) {
            return sendError(Lang::get('messages.pricing_not_found'), Lang::get('messages.pricing_not_found'), 404);
        }
        
        // Update Pricing
        $pricing->product_id = $validated['product_id'];
        $pricing->price = $validated['price'];
        $pricing->tax_id = $validated['tax_id'];
        $pricing->unit_price = $validated['unit_price'] ?? $pricing->unit_price;
        $pricing->tax_amount = $validated['tax_amount'] ?? $pricing->tax_amount;
        $pricing->suggest_retail_price = $validated['suggest_retail_price'] ?? $pricing->suggest_retail_price;
        $pricing->retail_unit_price = $validated['retail_unit_price'] ?? $pricing->retail_unit_price;
        $pricing->discount_percent = $validated['discount_percent'] ?? $pricing->discount_percent;
        $pricing->discount_name = $validated['discount_name'] ?? $pricing->discount_name;
        $pricing->discount_type = $validated['discount_type'] ?? $pricing->discount_type;
        $pricing->purchase_qty = $validated['purchase_qty'] ?? $pricing->purchase_qty;
        $pricing->is_minimum = $validated['is_minimum'] ?? $pricing->is_minimum;
        $pricing->discount_as_of = $validated['discount_as_of'] ?? $pricing->discount_as_of;
        $pricing->specific_audience = $validated['specific_audience'] ?? $pricing->specific_audience;
        $pricing->group_id = $validated['group_id'] ?? $pricing->group_id;
        $pricing->company_id = $validated['company_id'] ?? $pricing->company_id;
        
        $pricing->save();

        $success = $pricing;
        $message = Lang::get("messages.pricing_updated_successfully");
        return sendResponse($success, $message);
    }
}
