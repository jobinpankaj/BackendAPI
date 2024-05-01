<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductStyle;
use App\Models\SubCategory;
use App\Models\RetailerSupplierRequest;
use App\Models\SupplierDistributor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\ProductTrait;
use Image;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ProductTrait;
    public $permisssion;

    public function __construct()
    {
        $headers = getallheaders();
        
        $this->permisssion = isset($headers['permission']) ? $headers['permission'] : "";
    }
    public function getSupplierAllProductList($id)
    {
        
   
        if($this->permisssion !== "marketplace-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
		
		  $productList = Product::has('pricing')->has('inventory')->has('availability')->with(['description', 'productFormat','userInformation','userProfile','pricing', 'availability','productStyle'])->where('status', "1");
		  $data = $productList->where('user_id', $id)->get();
		  $success  = $data;
               
		  
        $message  = Lang::get("messages.products_list");
        return sendResponse($success, $message);
	}
    public function productsList()
    {
        if($this->permisssion !== "product-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        $data = Product::with(['description', 'productFormat', 'pricing', 'inventory', 'availability'])->where('status', "1")->where('user_id', $user->id)->get();

        $success  = $data;
        $message  = Lang::get("messages.products_list");
        return sendResponse($success, $message);
    }


    
    public function productsHavingPricingList(Request $request)
    {
        if($this->permisssion !== "product-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        
        $user = auth()->user();
        if($request->has('distributor')) {
            $data = Product::has('pricing')->with(['description', 'productFormat', 'pricing', 'inventory', 'availability'])->where('status', "1")->where('user_id', $user->id)->whereHas('inventory', function($query) use ($request){
                $query->where('distributor_id', $request->input('distributor'));
            })->get();
        } else {
            $data = Product::has('pricing')->with(['description', 'productFormat', 'pricing', 'inventory', 'availability'])->where('status', "1")->where('user_id', $user->id)->get();
        }        

        $success  = $data;
        $message  = Lang::get("messages.products_list");
        return sendResponse($success, $message);
    }

    public function getProduct($id)
    {
        if($this->permisssion !== "product-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        return $this->getProductData($user->id,$id);
    }

    public function addProduct(Request $request)
    {
        if($this->permisssion !== "product-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string',
            'product_type' => 'required|string',
            'product_format' => 'required|numeric|exists:product_formats,id',
            'product_desc.*.language_id' => 'nullable|numeric',
            'product_desc.*.description' => 'nullable|string',
            'product_desc.*.public_description' => 'nullable|string',
            'style' => 'required|numeric',
            'other_style' => 'nullable|string',
            'sub_category' => 'required|numeric',
            'alcohol_percentage' => 'required|numeric|between:0,100',
            'organic' => 'nullable|numeric',
            // 'product_image' => 'required',
            // 'product_label' => 'required'
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();

        if($request->has('other_style'))
        {
            $otherStyle = ProductStyle::updateOrCreate([
                'name' => $request->input('other_style')
            ]);
            $validated['style'] = $otherStyle->id;
        }

        $product = Product::create([
            'product_name' => $validated['product_name'],
            'product_type' => $validated['product_type'],
            'product_format' => $validated['product_format'],
            'style' => $validated['style'],
            'sub_category' => $validated['sub_category'],
            'sap_lowbla' => $validated['sap_lowbla'] ?? null,
            'sap_metro' => $validated['sap_metro'] ?? null,
            'sap_showbay' => $validated['sap_showbay'] ?? null,
            'is_organic' => $validated['organic'] ?? 0,
            'alcohol_percentage' => $validated['alcohol_percentage'],
            'product_image' => '',
            'user_id' => $user->id,
        ]);

        $productDescs = [];

        foreach(json_decode($validated['product_desc']) as $productDesc)
        {
            $productDescription = ProductDescription::create([
                'product_id' => $product->id,
                'description' => $productDesc->description,
                'public_description' => $productDesc->public_description,
                'language_id' => $productDesc->language_id
            ]);

            array_push($productDescs, $productDescription);
        }

        // Add uploaded label on top of product image and generate new image
        if($request->input("product_image") && $request->input("product_label"))
        {            
            $base64DataProduct = $request->input('product_image');
            $base64DataLabel = $request->input('product_label');

            // Remove the extra URI part from the base64 data
            $imageDataProduct = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataProduct);
            $imageDataLabel = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataLabel);

            // Get the image extension from base64 data
            $extensionProduct = $this->getImageExtensionFromBase64($base64DataProduct);
            $extensionLabel = $this->getImageExtensionFromBase64($imageDataLabel);

            $fileNameProduct = uniqid() . '.' . $extensionProduct;
            $fileNameLabel = uniqid() . '.' . $extensionLabel;

            $imageProduct = Image::make(base64_decode($imageDataProduct));
            $imageLabel = Image::make(base64_decode($imageDataLabel));

            $imageProduct->save(public_path('storage/product_images/' . $fileNameProduct));
            $imageLabel->save(public_path('storage/product_images/' . $fileNameLabel));

            $finalProductImage = 'product_images/'.$fileNameProduct;
            $finalLabelImage = 'product_images/'.$fileNameLabel;
             
            // Get label
            //$productLabel = $request->file("product_label");
            //$resultProductLabel = $productLabel->store('product_labels', ['disk' => 'public']);
            
            // Get Image
            //$productImage = $request->file("product_image");
            //$resultProductImage = $productImage->store('product_images', ['disk' => 'public']);
             
            //$logoImage = Image::make(public_path('storage/'.$resultProductLabel))->resize(50, 50);
            $logoImage = Image::make(base64_decode($imageDataLabel))->resize(100, 100);

            //$incomingImage = Image::make(public_path('storage/'.$resultProductImage));
            $incomingImage = Image::make(base64_decode($imageDataProduct));
              
            $incomingImage->insert($logoImage, 'bottom');
            $newFileMimeType = $extensionProduct;
            $newFileName = Str::random(40).'.'.$newFileMimeType;
            $incomingImage->save(public_path('storage/product_images/'.$newFileName));
            $finalResult = 'product_images/'.$newFileName;
             
            // Update product
            $product["product_image"] = $finalProductImage;
            $product["label_image"] = $finalLabelImage;
            $product["combined_image"] = $finalResult;
        }
        else if($request->input("product_image") == null && $request->input("product_label"))
        {            
           
            $base64DataLabel = $request->input('product_label');

            // Remove the extra URI part from the base64 data
            $imageDataLabel = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataLabel);

            // Get the image extension from base64 data
            $extensionLabel = $this->getImageExtensionFromBase64($imageDataLabel);

            $fileNameLabel = uniqid() . '.' . $extensionLabel;

            $imageLabel = Image::make(base64_decode($imageDataLabel));


            $imageLabel->save(public_path('storage/product_images/' . $fileNameLabel));

            $finalLabelImage = 'product_images/'.$fileNameLabel;
             
            // Get label
            //$productLabel = $request->file("product_label");
            //$resultProductLabel = $productLabel->store('product_labels', ['disk' => 'public']);
            
            // Get Image
            //$productImage = $request->file("product_image");
            //$resultProductImage = $productImage->store('product_images', ['disk' => 'public']);
             
            // $logoImage = Image::make(public_path('storage/'.$resultProductLabel))->resize(50, 50);


            $logoImage = Image::make(base64_decode($imageDataLabel))->resize(100, 100);

            //$incomingImage = Image::make(public_path('storage/'.$resultProductImage));


            $incomingImage = Image::make(base64_decode($imageDataLabel));
              
            $incomingImage->insert($logoImage, 'bottom');
            $newFileMimeType = $extensionLabel;
            $newFileName = Str::random(40).'.'.$newFileMimeType;
            $incomingImage->save(public_path('storage/product_images/'.$newFileName));
            $finalResult = 'product_images/'.$newFileName;
             
            // Update product
            // $product["product_image"] = $finalLabelImage;
            $product["label_image"] = $finalLabelImage;
            $product["combined_image"] = $finalResult;
        }
        if($request->input("barcode_image")){
            $base64DataBarcode = $request->input('barcode_image');
            $imageDataBarcode = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataBarcode);
            $extensionBarcode = $this->getImageExtensionFromBase64($imageDataBarcode);

            $fileNameBarcode = uniqid() . '.' . $extensionBarcode;
            $imageBarcode = Image::make(base64_decode($imageDataBarcode));

            $imageBarcode->save(public_path('storage/product_images/' . $fileNameBarcode));
            $finalBarcodeImage = 'product_images/'.$fileNameBarcode;
            $product["barcode_image"] = $finalBarcodeImage;
        }


        $product->save();

        $data = collect($product);
        $data['description'] = collect($productDescs);

        // $data = collect($product['description'])->add(collect($productDescription));
        $success = $data;
        $message = Lang::get("messages.product_created_successfully");
        return sendResponse($success, $message);
    }

    public function updateProduct(Request $request, $id)
    {
        if($this->permisssion !== "product-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $product = Product::with('description')->where('user_id', $user->id)->find($id);

        if(!$product) {
            return sendError(Lang::get('messages.product_not_found'), Lang::get('messages.product_not_found'), 404);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string',
            'product_type' => 'required|string',
            'product_format' => 'required|numeric|exists:product_formats,id',
            'product_desc.*.language_id' => 'nullable|numeric',
            'product_desc.*.description' => 'nullable|string',
            'product_desc.*.public_description' => 'nullable|string',
            'style' => 'required|numeric',
            'sub_category' => 'required|numeric',
            'alcohol_percentage' => 'required|numeric|between:0,100',
            'organic' => 'nullable|numeric',
            // 'product_image' => 'nullable',
            // 'product_label' => 'nullable'
        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();
        
        // Update Product
        $product->product_name = $validated['product_name'];
        $product->product_type = $validated['product_type'];
        $product->product_format = $validated['product_format'];
        $product->style = $validated['style'];
        $product->sub_category = $validated['sub_category'];
        $product->sap_lowbla = $validated['sap_lowbla'];
        $product->sap_metro = $validated['sap_metro'];
        $product->sap_showbay = $validated['sap_showbay'];
        $product->is_organic = $validated['organic'];
        $product->alcohol_percentage = $validated['alcohol_percentage'];

        if($request->has("product_image") && $request->has('product_label')) {
            
            $base64DataProduct = $request->input('product_image');
            $base64DataLabel = $request->input('product_label');

            // Remove the extra URI part from the base64 data
            $imageDataProduct = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataProduct);
            $imageDataLabel = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataLabel);

            // Get the image extension from base64 data
            $extensionProduct = $this->getImageExtensionFromBase64($base64DataProduct);
            $extensionLabel = $this->getImageExtensionFromBase64($imageDataLabel);

            $fileNameProduct = uniqid() . '.' . $extensionProduct;
            $fileNameLabel = uniqid() . '.' . $extensionLabel;

            $imageProduct = Image::make(base64_decode($imageDataProduct));
            $imageLabel = Image::make(base64_decode($imageDataLabel));

            $imageProduct->save(public_path('storage/product_images/' . $fileNameProduct));
            $imageLabel->save(public_path('storage/product_images/' . $fileNameLabel));

            $finalProductImage = 'product_images/'.$fileNameProduct;
            $finalLabelImage = 'product_images/'.$fileNameLabel;
            
            $logoImage = Image::make(base64_decode($imageDataLabel))->resize(250, 250);
            $incomingImage = Image::make(base64_decode($imageDataProduct));
            
            $incomingImage->insert($logoImage, 'center');
            $newFileMimeType = $extensionProduct;
            $newFileName = Str::random(40).'.'.$newFileMimeType;
            $incomingImage->save(public_path('storage/product_images/'.$newFileName));
            $finalResult = 'product_images/'.$newFileName;
            
            // Update product
            $product["product_image"] = $finalProductImage;
            $product["label_image"] = $finalLabelImage;
            $product["combined_image"] = $finalResult;
        }
        else if($request->has("product_image")) {
            
            $base64DataProduct = $request->input('product_image');
            // $base64DataLabel = $request->input('product_label');

            // Remove the extra URI part from the base64 data
            $imageDataProduct = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataProduct);
            // $imageDataLabel = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataLabel);

            // Get the image extension from base64 data
            $extensionProduct = $this->getImageExtensionFromBase64($base64DataProduct);
            // $extensionLabel = $this->getImageExtensionFromBase64($imageDataLabel);

            $fileNameProduct = uniqid() . '.' . $extensionProduct;
            // $fileNameLabel = uniqid() . '.' . $extensionLabel;

            $imageProduct = Image::make(base64_decode($imageDataProduct));
            // $imageLabel = Image::make(base64_decode($imageDataLabel));

            $imageProduct->save(public_path('storage/product_images/' . $fileNameProduct));
            // $imageLabel->save(public_path('storage/product_images/' . $fileNameLabel));

            $finalProductImage = 'product_images/'.$fileNameProduct;
            // $finalLabelImage = 'product_images/'.$fileNameLabel;
            
            $logoImage = Image::make(base64_decode($imageDataProduct))->resize(250, 250);
            $incomingImage = Image::make(base64_decode($imageDataProduct));
            
            $incomingImage->insert($logoImage, 'center');
            $newFileMimeType = $extensionProduct;
            $newFileName = Str::random(40).'.'.$newFileMimeType;
            $incomingImage->save(public_path('storage/product_images/'.$newFileName));
            $finalResult = 'product_images/'.$newFileName;
            
            // Update product
            $product["product_image"] = $finalProductImage;
            // $product["label_image"] = $finalLabelImage;
            $product["combined_image"] = $finalResult;
        }

        if($request->has("barcode_image")){
            $base64DataBarcode = $request->input('barcode_image');
            $imageDataBarcode = preg_replace('/data:image\/(.*?);base64,/', '', $base64DataBarcode);
            $extensionBarcode = $this->getImageExtensionFromBase64($imageDataBarcode);

            $fileNameBarcode = uniqid() . '.' . $extensionBarcode;
            $imageBarcode = Image::make(base64_decode($imageDataBarcode));

            $imageBarcode->save(public_path('storage/product_images/' . $fileNameBarcode));
            $finalBarcodeImage = 'product_images/'.$fileNameBarcode;
            $product["barcode_image"] = $finalBarcodeImage;
        }

        // Updating description
        $productDescs = [];

        foreach(json_decode($validated['product_desc']) as $productDesc)
        {
            $productDescription = ProductDescription::updateOrCreate([
                'product_id' => $product->id,
                'language_id' => $productDesc->language_id
            ],[
                
                'description' => $productDesc->description,
                'public_description' => $productDesc->public_description,
            ]);

            array_push($productDescs, $productDescription);
        }

        $product->save();

        $data = collect($product);
        $data['description'] = collect($productDescs);
        
        $success = $data;
        $message = Lang::get("messages.product_updated_successfully");
        return sendResponse($success, $message);
    }

    public function getSubCategories()
    {
        $data = SubCategory::all();
        $success = $data;
        return sendResponse($success, Lang::get('messages.sub_categories_list'));
    }

    public function getProductStyles()
    {
        $data = ProductStyle::all();
        $success = $data;
        return sendResponse($success, Lang::get('messages.product_styles_list'));
    }

    public function createProductStyles(Request $request)
    {
        if($this->permisssion !== "product-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $validator = Validator::make($request->all(), [
            'style' => 'required|string|max:200',
           
        ]);
        
        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }

        $validated = $request->all();

        $user = auth()->user();
        $productStyle = ProductStyle::create([
            'name' => $validated['style'],
            
        ]);

        $success = $productStyle;
        $message = Lang::get("messages.product_style_created_successfully");
        return sendResponse($success, $message);
    }



    // Product listing as per approved supplier list in retailer user
    public function getSupplierAllProductList($id)
    {
        
   
        if($this->permisssion !== "marketplace-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
		
		  $productList = Product::has('pricing')->has('inventory')->has('availability')->with(['description', 'productFormat','userInformation','userProfile','pricing', 'availability','productStyle'])->where('status', "1");
		  $data = $productList->where('user_id', $id)->get();
		  $success  = $data;
		  
        $message  = Lang::get("messages.products_list");
        return sendResponse($success, $message);
	}

    public function getSupplierProductList(Request $request)
    {
        if($this->permisssion !== "marketplace-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();
        $search = $request->input("search");
        $supplier_ids = [];
        $requestQuery = RetailerSupplierRequest::query();
        $requestQuery->where(function($query){
            $query = $query->whereHas('supplierInformation',function($query1){
                $query1->where("status","1");
            });
        });
        $requestQuery->where("status","1");
        $requestQuery->where("retailer_id","=",$user->id);
        $requestQueryData = $requestQuery->get();

        if($requestQueryData->count() < 1){
            $message = Lang::get("request_not_approved");
            return sendResponse([],$message);
        }
        $supplier_ids = $requestQueryData->pluck("supplier_id");

        $productList = Product::has('pricing')->has('inventory')->has('availability')->with(['description', 'productFormat','userInformation','userProfile','pricing', 'availability'])->where('status', "1")->whereIn('user_id', $supplier_ids);
        if($search != "")
        {
            $productList->where("product_name","LIKE","%".$search."%");
        }
        $data = $productList->get();

        $success  = $data;
        $message  = Lang::get("messages.products_list");
        return sendResponse($success, $message);
    }


    public function getProductDetail(Request $request)
    {
        if($this->permisssion !== "marketplace-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }

        $user = auth()->user();
        $user_id = $user->id;
        $supplier_id = $request->input("supplier_id");
        $requestData = RetailerSupplierRequest::where("retailer_id","=",$user_id)->where("supplier_id","=",$supplier_id)->where("status","1")->first();
        if($requestData == null){
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $product_id = $request->input("product_id");
        return $this->getProductData($supplier_id,$product_id);
    }

    public function deactivateProduct(request $request)
    {
        if($this->permisssion !== "product-edit")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'status'    => 'required',

        ]);

        if($validator->fails()) {
            return sendError(Lang::get('validation_error'), $validator->errors(), 422);
        }
        $user = auth()->user();
        $product = Product::where('user_id', $user->id)->where('id',$request->product_id)->first();

        if(!$product) {
            return sendError(Lang::get('messages.product_not_found'), Lang::get('messages.product_not_found'), 404);
        }
        $product->status = $request->status;
        $product->save();

        $success = $product;
        $message = Lang::get("messages.product_deactivated_successfully");
        return sendResponse($success, $message);
    }

    // get product listing for distributor user
    public function productListForDistributor(Request $request)
    {
        if($this->permisssion !== "product-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $supplier_id = $request->input("supplier_id");
        $search = $request->input("search");
        
        $user = auth()->user();
        $suppliers = SupplierDistributor::where('distributor_id', $user->id);
        $supplierIds = [];
        if($suppliers->count() > 0 ) {
            $supplierIds = $suppliers->pluck('supplier_id')->toArray();
        }
        if(count($supplierIds) < 1) {
            $success  = [];
            $message  = Lang::get("messages.products_list");
            return sendResponse($success, $message);
        }
        $productQuery = Product::query();
        if(!empty($search)) {
            $productQuery->where(function($query)use($search){
                $query->where(function($query1)use($search){
                    $query1->where("product_name",'LIKE','%'.$search.'%');
                });
                $query->orWhereHas("userInformation",function($query1)use($search){
                    $query1->where("first_name",'LIKE',"%".$search."%")->orWhere("last_name",'LIKE',"%".$search."%")->orWhere("email",'LIKE',"%".$search."%")->orWhere("phone_number",'LIKE',"%".$search."%")->orWhereRaw("concat(first_name,' ',last_name) like '%".$search."%'");
                });
            });
        }
        if(!empty($supplier_id)){
            $productQuery->where(function($query)use($supplier_id){
                $query->where("user_id",'=',$supplier_id);
            });
        }
        else {
            $productQuery->where(function($query)use($supplierIds){
                $query->whereIn("user_id",$supplierIds);
            });
        }
        $data = $productQuery->with(['description', 'productFormat','userInformation','pricing','inventory','availability'])->where('status', "1")->get();

        $success  = $data;
        $message  = Lang::get("messages.products_list");
        return sendResponse($success, $message);
    }

    public function getProductInformation($id)
    {
        if($this->permisssion !== "product-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $product = Product::with(['description', 'productFormat','productStyle','productCategory'])->find($id);

        if(!$product) {
            return sendError(Lang::get('messages.product_not_found'), Lang::get('messages.product_not_found'), 404);
        }

        $success = $product;
        $message = Lang::get("messages.product_detail");
        return sendResponse($success, $message);
    }

    public function getSimilarProducts(Request $request)
    {
        if($this->permisssion !== "product-view")
        {
            return sendError('Access Denied', ['error' => Lang::get("messages.not_permitted")], 403);
        }
        $user = auth()->user();
        $suppliers = SupplierDistributor::where('distributor_id', $user->id);
        $supplierIds = [];
        $products = [];
        if($suppliers->count() > 0 ) {
            $supplierIds = $suppliers->pluck('supplier_id')->toArray();
            $products = Product::has('pricing')->has('inventory')->has('availability')->with(['description', 'productFormat','userInformation','pricing','inventory','availability'])->where('sub_category', $request->sub_category)->whereIn("user_id",$supplierIds)->limit(8)->get();
        }

        $success = $products;
        $message = Lang::get("messages.similar_products");
        return sendResponse($success, $message);
    }

    private function getImageExtensionFromBase64($base64Data)
    {
        preg_match('/data:image\/(.*?);base64,/', $base64Data, $matches);
        return $matches[1] ?? 'png'; // Default to 'png' if no match is found
    }
    
}
