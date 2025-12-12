<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

/**
 * Product Policy
 * 
 * Example of how to use BasePolicy for resource authorization.
 */
class ProductPolicy extends BasePolicy
{
    protected function getResource(): string
    {
        return 'products';
    }

    /**
     * Additional custom permission check
     */
    public function export(User $user): bool
    {
        return $user->canDo('products', 'products', 'export');
    }

    /**
     * Additional custom permission check
     */
    public function import(User $user): bool
    {
        return $user->canDo('products', 'products', 'import');
    }
}

