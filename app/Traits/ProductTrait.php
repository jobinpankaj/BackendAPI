<?php 

namespace App\Traits;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductStyle;
use App\Models\SubCategory;
use App\Models\RetailerSupplierRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

trait ProductTrait
{
    public function getProductData($user_id,$product_id)
    {
        $product = Product::with(['description', 'productFormat','productStyle','productCategory','pricing','userInformation','userProfile','availability'])->where('user_id',$user_id)->find($product_id);
        
        if(!$product) {
            return sendError(Lang::get('messages.product_not_found'), Lang::get('messages.product_not_found'), 404);
        }

        $success = $product;
        $message = Lang::get("messages.product_detail");
        return sendResponse($success, $message);
    }
}