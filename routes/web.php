<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Models\Post;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Emargareten\InertiaModal\Modal;
use App\Events\PlaygroundEvent;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PayPalController;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::loginUsingId(1);

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Products/Index', [
        'products' => Product::paginate(15)
    ]);
    /*
    return Inertia::render('Dashboard', [
        'posts' => [[
            'id' => 1,
            'user' => [
                'email' => 'edgararaj@gmail.com',
                'name' => 'Edgar Araujo',
                'imageUrl' => 'https://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50'
            ]
        ],
    [
        'id' => 1,
        'user' => [
            'email' => 'edgararaj@gmail.com',
            'name' => 'Edgar Araujo',
            'imageUrl' => 'https://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50'
        ]
    ]]
]);
    */
}
)->middleware(['auth', 'verified'])->name('dashboard');

Route::get('create-transaction', [PayPalController::class, 'createTransaction'])->name('createTransaction');
Route::get('process-transaction', [PayPalController::class, 'processTransaction'])->name('processTransaction');
Route::get('success-transaction', [PayPalController::class, 'successTransaction'])->name('successTransaction');
Route::get('cancel-transaction', [PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/summary', function() {
        return Inertia::render('Order/Summary');
    })->name("summary");

    Route::get('/checkout', function() {
        return Inertia::render('Order/Checkout');
    })->name("checkout");

    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

    Route::post('/purchase', function (Request $request) {
        $user = User::where('email', Auth::user()->email)->first();

        try {
            $user->createOrGetStripeCustomer();

            $payment = $user->charge(
                $request->input('amount'),
                $request->input('payment_method_id')
            );

            $payment = $payment->asStripePaymentIntent();

            $order = $user->orders()
                ->create([
                    'transaction_id' => $payment->charges->data[0]->id,
                    'total' => $payment->charges->data[0]->amount
                ]);

            foreach (json_decode($request->input('cart'), true) as $item) {
                $order->products()
                    ->attach($item['id'], ['quantity' => $item['quantity']]);
            }

            $order->load('products');
            return $order;

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });
});

require __DIR__.'/auth.php';
