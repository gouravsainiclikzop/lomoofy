<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Example Controller showing various permission checking methods
 * 
 * This is a reference implementation showing best practices
 * for using the enhanced permission system.
 */
class ExamplePermissionUsageController extends Controller
{
    /**
     * Example 1: Using Gate facade
     */
    public function example1()
    {
        // Check capability using Gate
        if (Gate::allows('can', 'products', 'products', 'view')) {
            // User can view products
            $products = Product::all();
            return view('products.index', compact('products'));
        }

        abort(403, 'You do not have permission to view products');
    }

    /**
     * Example 2: Using user's canDo method
     */
    public function example2()
    {
        $user = auth()->user();

        // Check using canDo
        if ($user->canDo('products', 'products', 'create')) {
            return view('products.create');
        }

        return redirect()->back()->with('error', 'Permission denied');
    }

    /**
     * Example 3: Using hasPermission with slug
     */
    public function example3()
    {
        if (auth()->user()->hasPermission('products.view')) {
            // Permission granted
        }
    }

    /**
     * Example 4: Using hasPermission with identifier
     */
    public function example4()
    {
        if (auth()->user()->hasPermission('products.products.view')) {
            // Permission granted
        }
    }

    /**
     * Example 5: Using hasAnyPermission
     */
    public function example5()
    {
        $user = auth()->user();

        // User needs at least one of these permissions
        if ($user->hasAnyPermission(['products.create', 'products.update'])) {
            // Can create OR update
        }
    }

    /**
     * Example 6: Using hasAllPermissions
     */
    public function example6()
    {
        $user = auth()->user();

        // User needs ALL of these permissions
        if ($user->hasAllPermissions(['products.view', 'products.export'])) {
            // Can view AND export
        }
    }

    /**
     * Example 7: Using Policy authorization
     */
    public function example7(Product $product)
    {
        // This will check ProductPolicy::view method
        $this->authorize('view', $product);

        return view('products.show', compact('product'));
    }

    /**
     * Example 8: Multiple permission checks
     */
    public function example8()
    {
        $user = auth()->user();

        // Complex permission logic
        if ($user->canDo('products', 'products', 'view') && 
            $user->canDo('products', 'products', 'export')) {
            // Can view and export products
        }
    }

    /**
     * Example 9: Conditional logic based on permissions
     */
    public function example9()
    {
        $user = auth()->user();
        $canEdit = $user->canDo('products', 'products', 'update');
        $canDelete = $user->canDo('products', 'products', 'delete');

        return view('products.index', [
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }

    /**
     * Example 10: Using in API responses
     */
    public function example10()
    {
        $user = auth()->user();

        return response()->json([
            'permissions' => [
                'can_view' => $user->canDo('products', 'products', 'view'),
                'can_create' => $user->canDo('products', 'products', 'create'),
                'can_update' => $user->canDo('products', 'products', 'update'),
                'can_delete' => $user->canDo('products', 'products', 'delete'),
            ]
        ]);
    }
}

