@extends('layouts.admin')

@section('title', 'Manage Variants - ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="py-3">
        <div class="row g-3 align-items-center mb-3">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('products.edit', $product) }}">{{ $product->name }}</a></li>
                        <li class="breadcrumb-item active">Manage Variants</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h4 m-0">Manage Variants</h1>
                        <p class="text-muted mb-0">Product: <strong>{{ $product->name }}</strong></p>
                    </div>
                    <div>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Product
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="variantsForm" method="POST" action="{{ route('products.variants.update', $product) }}" enctype="multipart/form-data" onsubmit="return false;">
            @csrf
            <div class="row">
                <div class="col-12">
                    @include('admin.products.partials.variants')
                </div>
            </div>
            <div class="card mt-4 sticky-bottom">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="saveVariantsBtn">
                            <i class="fas fa-save me-1"></i> Save Variants
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>




<script>
(function() {
    let isSubmitting = false;
    let handlerAttached = false;
    
    function attachHandler() {
        if (handlerAttached) return;
        
        const saveBtn = document.getElementById('saveVariantsBtn');
        const variantsForm = document.getElementById('variantsForm');
        
        if (saveBtn && variantsForm) {
            handlerAttached = true;
            console.log('Attaching click handler to save button');
            
            // Prevent default form submission completely
            variantsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Form submit event prevented');
                return false;
            }, true); // Use capture phase
            
            // Handle button click - directly call the submission function
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (isSubmitting) {
                    console.log('Already submitting, ignoring click');
                    return false;
                }
                
                console.log('Save button clicked!');
                
                // Wait a bit for handlers to attach, then call function directly (avoid deprecated event dispatch)
                setTimeout(function() {
                    if (window.submitVariantsFormData) {
                        console.log('Calling submitVariantsFormData function directly');
                        window.submitVariantsFormData();
                    } else if (window.formSubmissionHandlerAttached) {
                        // Fallback: try to trigger setup if not done yet
                        if (window.setupVariantsFormSubmission) {
                            window.setupVariantsFormSubmission();
                            setTimeout(function() {
                                if (window.submitVariantsFormData) {
                                    window.submitVariantsFormData();
                                }
                            }, 100);
                        } else {
                            console.error('Form submission handler not ready');
                            alert('Form handler not ready. Please refresh the page and try again.');
                            isSubmitting = false;
                        }
                    } else {
                        console.error('Form submission handler not attached yet!');
                        // Wait a bit more and try again
                        setTimeout(function() {
                            if (window.submitVariantsFormData) {
                                window.submitVariantsFormData();
                            } else {
                                alert('Form handler not ready. Please refresh the page and try again.');
                                isSubmitting = false;
                            }
                        }, 500);
                    }
                }, 200);
                
                return false;
            });
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachHandler);
    } else {
        attachHandler();
    }
})();
</script>
@endsection

