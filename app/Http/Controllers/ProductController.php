<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use Inertia\Inertia;
use PDO;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::query()->with('variants')->paginate(15)
        ]);
    }

    public function show(string $slug)
    {
        $variant = ProductVariant::where('slug', $slug)->first();
        $product = $variant->product;
        $variants = $product->variants();
        $allColors = $product->variants()->select('color')->distinct()->pluck('color')->toArray();
        $allSizes = $product->variants()->select('size')->distinct()->pluck('size')->toArray();
        $colors = $allColors;
        $sizes = $product->variants()->where('color', $allColors[0])->select('size')->distinct()->pluck('size')->toArray();

        if (request('color'))
        {
            $variants->where('color', request('color'));
            $sizes = $product->variants()->where('color', request('color'))->select('size')->distinct()->pluck('size')->toArray();
        }
        else if (request('size'))
        {
            $variants->where('size', request('size'));
            $sizes = $allSizes;
            $colors = $product->variants()->where('size', request('size'))->select('color')->distinct()->pluck('color')->toArray();
        }
        if ((request('color') || request('size')) && request('get'))
        {
            $newVariants = clone $variants;
            if (request('currentColor'))
            {
                $newVariants->where('color', request('currentColor'));
            }
            else if (request('currentSize'))
            {
                $newVariants->where('size', request('currentSize'));
            }
            $newVariant = $newVariants->first() ?? $variants->first();
            return to_route('products.show', array_merge([ 'slug' => $newVariant->slug ], request()->only(['color', 'size'])));
        }
        return Inertia::render("Products/Show", [
            'colors' => $colors,
            'allColors' => $allColors,
            'sizes' => $sizes,
            'allSizes' => $allSizes,
            'product' => $product,
            'variant' => $variant,
            // 'price' => $product->find($id)->variants()pluck('price')->toArray(),
            'options' => [
                'color' => $variant->color,
                'size' => $variant->size
            ]
        ]);
    }
}
