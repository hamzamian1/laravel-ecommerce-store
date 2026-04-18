<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return response()->json($cart);
    }

    public function add(Request $request, Product $product)
    {
        $result = $this->addToCart($request, $product);

        if ($result !== true) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result], 422);
            }
            return back()->with('error', $result);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $cart = session()->get('cart', []);
            return response()->json(['success' => true, 'cart' => $cart, 'total_count' => count($cart)]);
        }

        return back()->with('success', 'Product added to bag!');
    }

    public function buyNow(Request $request, Product $product)
    {
        $result = $this->addToCart($request, $product);

        if ($result !== true) {
            return back()->with('error', $result);
        }

        return redirect()->route('checkout');
    }

    private function addToCart(Request $request, Product $product)
    {
        // Validate input
        $request->validate([
            'size' => 'nullable|string|max:10',
            'type' => 'nullable|string|max:50',
            'quantity' => 'nullable|integer|min:1|max:10',
        ]);

        $type = $request->input('type', null);
        $size = $request->input('size', null);
        $quantity = max(1, min(10, intval($request->input('quantity', 1))));

        // Validate type is available on product
        if ($type) {
            $availableTypes = $product->types ? array_map('trim', explode(',', $product->types)) : [];
            if (!empty($availableTypes) && !in_array($type, $availableTypes)) {
                return 'Selected type is not available for this product.';
            }
        }

        // Require size for stitched items
        if ($type && strtolower(trim($type)) === 'stitched' && !$size) {
            return 'Please select a size for stitched items.';
        }

        // Validate stitched_price exists when stitched is selected
        if ($type && strtolower(trim($type)) === 'stitched' && !$product->stitched_price) {
            return 'Stitched option is not available for this product.';
        }

        // Default size for unstitched
        if (!$size) {
            $size = ($type && strtolower(trim($type)) === 'unstitched') ? 'Unstitched' : 'M';
        }

        // Get correct price from backend (never trust frontend price)
        $actualPrice = $product->getEffectivePrice($type);

        $cart = session()->get('cart', []);

        $id = $product->id;
        $uniqueId = $id . '_' . $size . ($type ? '_' . $type : '');

        if (isset($cart[$uniqueId])) {
            $cart[$uniqueId]['quantity'] += $quantity;
            $cart[$uniqueId]['price'] = $actualPrice; // Always refresh price from DB
        } else {
            $cart[$uniqueId] = [
                "id" => $product->id,
                "name" => $product->name,
                "quantity" => $quantity,
                "price" => $actualPrice,
                "image" => $product->image_path ? asset($product->image_path) : ($product->images->first() ? asset($product->images->first()->image_path) : null),
                "size" => $size,
                "type" => $type,
                "color" => $product->color
            ];
        }

        session()->put('cart', $cart);
        return true;
    }

    public function remove($uniqueId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$uniqueId])) {
            unset($cart[$uniqueId]);
            session()->put('cart', $cart);
        }

        return response()->json(['success' => true, 'cart' => $cart, 'total_count' => count($cart)]);
    }

    public function update(Request $request, $uniqueId)
    {
        // Validate quantity
        $request->validate([
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$uniqueId])) {
            // Re-verify price from DB to prevent manipulation
            $product = Product::find($cart[$uniqueId]['id']);
            if ($product) {
                $cart[$uniqueId]['price'] = $product->getEffectivePrice($cart[$uniqueId]['type'] ?? null);
            }
            $cart[$uniqueId]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }

        return response()->json(['success' => true, 'cart' => $cart]);
    }
}
