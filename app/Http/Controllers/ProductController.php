<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

use Laravel\Sanctum\PersonalAccessToken;

use Carbon\Carbon;

class ProductController extends Controller
{

    private function cek_token($action_token)
    {
        $data_return = [1, "OK"];

        // cek token action
        $token = PersonalAccessToken::findToken($action_token);

        // Send failed response if cek token is not valid
        if (!$token) {
            $data_return = [0, "These credentials do not match our records"];
            return $data_return;
            // return response()->json(['error' => "These credentials do not match our records"], 200);
        }

        if (!Carbon::parse($token->expired_at)->isFuture()) {
            $data_return = [0, "Token action expired"];
            return $data_return;
            // return response()->json(['error' => "Token action expired"], 200);
        }

        // delete token already used
        $token->delete();

        return $data_return;
    }

    public function index(Request $request)
    {
        // return print_r($request->all());die;
        // Validate data
        $data = $request->all();
        $validator = Validator::make($data, [
            'token_action'  => 'required'
        ]);

        // Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        // cek token action
        $cek_token_func = $this->cek_token($request->token_action);
        if ($cek_token_func[0] == 0) {
            return response()->json(['error' => $cek_token_func[1]], 200);
        }

        return Product::latest()->get();
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'token_action'  => 'required',
            'name'          => 'required',
            'detail'        => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $cek_token_func = $this->cek_token($request->token_action);
        if ($cek_token_func[0] == 0) {
            return response()->json(['error' => $cek_token_func[1]], 200);
        }

        unset($data['token_action']);
        $product = Product::create($data);

        return response()->json([
            'success'   => true,
            'message'   => 'Product created successfully',
            'data'      => $product
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product not found.'
            ], 400);
        }
    
        return $product;
    }

    public function edit($id, Request $request)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product not found.'
            ], 400);
        }
    
        return $product;
    }
    
    public function update(Request $request, Product $product)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'token_action'  => 'required',
            'name'          => 'required',
            'detail'        => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $cek_token_func = $this->cek_token($request->token_action);
        if ($cek_token_func[0] == 0) {
            return response()->json(['error' => $cek_token_func[1]], 200);
        }

        $product->name = $data['name'];
        $product->detail = $data['detail'];
        $product->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Product updated successfully',
            'data'      => $product
        ], Response::HTTP_OK);
    }

    public function destroy(Product $product, Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'token_action'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $cek_token_func = $this->cek_token($request->token_action);
        if ($cek_token_func[0] == 0) {
            return response()->json(['error' => $cek_token_func[1]], 200);
        }

        $product->delete();
        
        return response()->json([
            'success'   => true,
            'message'   => 'Product deleted successfully'
        ], Response::HTTP_OK);
    }
}
