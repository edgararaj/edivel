<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show(string $id)
    {
        return Inertia::render("Products/Show", [
            'product' => Product::with('categories')->find($id)
        ]);
    }
}
