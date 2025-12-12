{{-- 
    Example Blade Template showing permission checking
    Copy these examples to your actual blade files
--}}

{{-- Example 1: Using @can directive with capability pattern --}}
@can('can', 'products', 'products', 'view')
    <a href="{{ route('products.index') }}" class="btn btn-primary">
        <i class="bx bx-list-ul"></i> View Products
    </a>
@endcan

{{-- Example 2: Using Gate facade --}}
@if(Gate::allows('can', 'products', 'products', 'create'))
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createProductModal">
        <i class="bx bx-plus"></i> Create Product
    </button>
@endif

{{-- Example 3: Using user's canDo method --}}
@if(auth()->user()->canDo('products', 'products', 'update'))
    <button type="button" class="btn btn-warning" onclick="editProduct({{ $product->id }})">
        <i class="bx bx-edit"></i> Edit
    </button>
@endif

{{-- Example 4: Using hasPermission with slug --}}
@if(auth()->user()->hasPermission('products.delete'))
    <button type="button" class="btn btn-danger" onclick="deleteProduct({{ $product->id }})">
        <i class="bx bx-trash"></i> Delete
    </button>
@endif

{{-- Example 5: Multiple permissions (OR) --}}
@if(auth()->user()->hasAnyPermission(['products.create', 'products.update']))
    <div class="alert alert-info">
        You can create or update products
    </div>
@endif

{{-- Example 6: Multiple permissions (AND) --}}
@if(auth()->user()->hasAllPermissions(['products.view', 'products.export']))
    <button type="button" class="btn btn-info" onclick="exportProducts()">
        <i class="bx bx-export"></i> Export Products
    </button>
@endif

{{-- Example 7: Conditional rendering based on permissions --}}
<div class="card">
    <div class="card-header">
        <h5>Products</h5>
        @if(auth()->user()->canDo('products', 'products', 'create'))
            <button class="btn btn-sm btn-primary float-end">Add New</button>
        @endif
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->price }}</td>
                        <td>
                            @if(auth()->user()->canDo('products', 'products', 'view'))
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show"></i> View
                                </a>
                            @endif
                            
                            @if(auth()->user()->canDo('products', 'products', 'update'))
                                <button class="btn btn-sm btn-warning" onclick="editProduct({{ $product->id }})">
                                    <i class="bx bx-edit"></i> Edit
                                </button>
                            @endif
                            
                            @if(auth()->user()->canDo('products', 'products', 'delete'))
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct({{ $product->id }})">
                                    <i class="bx bx-trash"></i> Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Example 8: Menu items based on permissions --}}
<ul class="nav nav-pills">
    @if(auth()->user()->canDo('dashboard', 'dashboard', 'view'))
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
        </li>
    @endif
    
    @if(auth()->user()->canDo('products', 'products', 'view'))
        <li class="nav-item">
            <a class="nav-link" href="{{ route('products.index') }}">Products</a>
        </li>
    @endif
    
    @if(auth()->user()->canDo('orders', 'orders', 'view'))
        <li class="nav-item">
            <a class="nav-link" href="{{ route('orders.index') }}">Orders</a>
        </li>
    @endif
    
    @if(auth()->user()->hasAnyPermission(['users.view', 'roles.view', 'permissions.view']))
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">User Management</a>
            <ul class="dropdown-menu">
                @if(auth()->user()->canDo('users', 'users', 'view'))
                    <li><a class="dropdown-item" href="{{ route('users.index') }}">Users</a></li>
                @endif
                @if(auth()->user()->canDo('roles', 'roles', 'view'))
                    <li><a class="dropdown-item" href="{{ route('roles.index') }}">Roles</a></li>
                @endif
                @if(auth()->user()->canDo('permissions', 'permissions', 'view'))
                    <li><a class="dropdown-item" href="{{ route('permissions.index') }}">Permissions</a></li>
                @endif
            </ul>
        </li>
    @endif
</ul>

{{-- Example 9: Form fields based on permissions --}}
<form method="POST" action="{{ route('products.store') }}">
    @csrf
    
    <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label>Price</label>
        <input type="number" name="price" class="form-control" required>
    </div>
    
    {{-- Only show this field if user can manage pricing --}}
    @if(auth()->user()->canDo('products', 'pricing', 'manage'))
        <div class="form-group">
            <label>Special Price</label>
            <input type="number" name="special_price" class="form-control">
        </div>
    @endif
    
    <div class="form-group">
        @if(auth()->user()->canDo('products', 'products', 'create'))
            <button type="submit" class="btn btn-primary">Create Product</button>
        @endif
    </div>
</form>

{{-- Example 10: JavaScript permission checks (for AJAX) --}}
<script>
    // Check permissions in JavaScript
    const userPermissions = @json(auth()->user()->getAllPermissions()->pluck('slug')->toArray());
    
    function canDo(module, resource, action) {
        const identifier = `${module}.${resource}.${action}`;
        const slug = `${module}.${action}`;
        return userPermissions.includes(identifier) || userPermissions.includes(slug);
    }
    
    // Usage
    if (canDo('products', 'products', 'delete')) {
        // Show delete button
        console.log('User can delete products');
    }
</script>

