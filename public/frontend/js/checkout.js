/**
 * Enhanced Checkout Functionality
 * Handles address selection, validation, and order processing
 */

class CheckoutManager {
    constructor() {
        this.form = document.getElementById('checkoutForm');
        this.submitBtn = document.getElementById('placeOrderBtn');
        this.billingSameCheckbox = document.getElementById('billingSameAsShipping');
        this.billingSection = document.getElementById('billingAddressSelection');
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeState();
        this.setupValidation();
    }

    bindEvents() {
        // Billing same as shipping toggle
        if (this.billingSameCheckbox) {
            this.billingSameCheckbox.addEventListener('change', (e) => {
                this.toggleBillingSection(e.target.checked);
            });
        }

        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e);
            });
        }

        // Address selection feedback
        document.querySelectorAll('input[name="shipping_address_id"], input[name="billing_address_id"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.updateAddressSelection(e.target);
            });
        });

        // Real-time cart validation
        this.setupCartValidation();
    }

    initializeState() {
        // Initialize billing section visibility
        if (this.billingSameCheckbox) {
            this.toggleBillingSection(this.billingSameCheckbox.checked);
        }

        // Initialize address selections
        this.updateAllAddressSelections();
    }

    toggleBillingSection(isSame) {
        if (!this.billingSection) return;

        const billingRadios = document.querySelectorAll('input[name="billing_address_id"]');
        
        if (isSame) {
            this.billingSection.style.display = 'none';
            billingRadios.forEach(radio => {
                radio.required = false;
            });
        } else {
            this.billingSection.style.display = 'block';
            billingRadios.forEach(radio => {
                radio.required = true;
            });
        }
    }

    updateAddressSelection(radio) {
        const addressOptions = radio.closest('.address-options');
        if (!addressOptions) return;

        // Remove border from all address cards in this section
        addressOptions.querySelectorAll('.address-card').forEach(card => {
            card.classList.remove('border-primary');
        });

        // Add border to selected address card
        const selectedCard = radio.closest('.address-card');
        if (selectedCard) {
            selectedCard.classList.add('border-primary');
        }
    }

    updateAllAddressSelections() {
        document.querySelectorAll('input[name="shipping_address_id"]:checked, input[name="billing_address_id"]:checked').forEach(radio => {
            this.updateAddressSelection(radio);
        });
    }

    setupValidation() {
        // Add real-time validation feedback
        const requiredFields = this.form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });

            field.addEventListener('change', () => {
                this.validateField(field);
            });
        });
    }

    validateField(field) {
        const fieldGroup = field.closest('.form-group') || field.closest('.form-check') || field.closest('.address-option');
        
        if (!fieldGroup) return;

        // Remove existing validation classes
        fieldGroup.classList.remove('is-valid', 'is-invalid');
        
        // Remove existing feedback
        const existingFeedback = fieldGroup.querySelector('.invalid-feedback, .valid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        // Validate field
        if (field.checkValidity()) {
            fieldGroup.classList.add('is-valid');
        } else {
            fieldGroup.classList.add('is-invalid');
            
            // Add error message
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = field.validationMessage || 'This field is required';
            fieldGroup.appendChild(feedback);
        }
    }

    async validateForm() {
        let isValid = true;
        const errors = [];

        // Clear previous errors
        this.clearErrors();

        // Validate cart stock availability first
        try {
            const response = await fetch('/api/orders/validate-cart', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (!data.success && data.errors) {
                errors.push(...data.errors);
                isValid = false;
            }
        } catch (error) {
            console.error('Error validating cart:', error);
            errors.push('Unable to validate cart. Please try again.');
            isValid = false;
        }

        // Validate shipping address
        const shippingAddress = document.querySelector('input[name="shipping_address_id"]:checked');
        if (!shippingAddress) {
            errors.push('Please select a shipping address');
            isValid = false;
        }

        // Validate billing address (if not same as shipping)
        if (!this.billingSameCheckbox.checked) {
            const billingAddress = document.querySelector('input[name="billing_address_id"]:checked');
            if (!billingAddress) {
                errors.push('Please select a billing address');
                isValid = false;
            }
        }

        // Validate payment method
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethod) {
            errors.push('Please select a payment method');
            isValid = false;
        }
  
        // Show errors if any
        if (!isValid) {
            this.showErrors(errors);
        }

        return isValid;
    }

    showErrors(errors) { 
        // Format errors to be customer-friendly
        const formatError = (error) => {
            // Convert technical messages to customer-friendly ones
            let friendlyError = error;
            
            // Stock-related errors
            if (error.includes('Insufficient stock') || error.includes('out of stock')) {
                friendlyError = error.replace(/Insufficient stock for '([^']+)'\. Available: (\d+), Requested: (\d+)/, 
                    'Sorry, "$1" is not available in the quantity you requested. Only $2 items are available.');
                friendlyError = friendlyError.replace(/Product '([^']+)' is out of stock/, 
                    'Sorry, "$1" is currently out of stock. Please remove it from your cart to continue.');
                friendlyError = friendlyError.replace(/is no longer available/, 
                    'is no longer available. Please remove it from your cart to continue.');
            }
            
            // Address-related errors
            if (error.includes('shipping address')) {
                friendlyError = 'Please select a delivery address for your order.';
            }
            if (error.includes('billing address')) {
                friendlyError = 'Please select a billing address for your order.';
            }
            
            // Payment-related errors
            if (error.includes('payment method')) {
                friendlyError = 'Please select a payment method to complete your order.';
            }
            
            // Cart-related errors
            if (error.includes('Cart is empty')) {
                friendlyError = 'Your cart is empty. Please add items to your cart before checkout.';
            }
            if (error.includes('Cart has expired')) {
                friendlyError = 'Your cart session has expired. Please add items to your cart again.';
            }
            if (error.includes('Unable to validate cart')) {
                friendlyError = 'We couldn\'t verify your cart. Please refresh the page and try again.';
            }
            
            return friendlyError;
        };
        
        const friendlyErrors = errors.map(formatError);
        
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6><i class="lni lni-warning me-2"></i>Please complete the following to proceed:</h6>
                <ul class="mb-0">
                    ${friendlyErrors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Find the cart validation alert container
        let alertContainer = document.getElementById('cartValidationAlertContainer');
        
        // If container doesn't exist, create it
        if (!alertContainer) {
            const checkoutContainer = document.getElementById('checkoutContainer');
            if (checkoutContainer) {
                // Find the row after the checkout title
                const titleRow = checkoutContainer.querySelector('.row');
                if (titleRow && titleRow.nextElementSibling) {
                    // Use the existing row structure
                    const alertRow = titleRow.nextElementSibling;
                    alertContainer = alertRow.querySelector('#cartValidationAlertContainer');
                    if (!alertContainer) {
                        alertContainer = document.createElement('div');
                        alertContainer.id = 'cartValidationAlertContainer';
                        alertRow.querySelector('.col-12').appendChild(alertContainer);
                    }
                } else {
                    // Create new row structure
                    const alertRow = document.createElement('div');
                    alertRow.className = 'row mb-4';
                    alertRow.innerHTML = '<div class="col-12"><div id="cartValidationAlertContainer"></div></div>';
                    alertContainer = alertRow.querySelector('#cartValidationAlertContainer');
                    
                    // Insert after title row
                    if (titleRow) {
                        titleRow.parentNode.insertBefore(alertRow, titleRow.nextSibling);
                    } else {
                        checkoutContainer.insertBefore(alertRow, checkoutContainer.firstChild);
                    }
                }
            }
        }
        
        // Insert the error HTML
        if (alertContainer) {
            // Clear any existing content
            alertContainer.innerHTML = '';
            // Add the error alert
            alertContainer.innerHTML = errorHtml;
            
            // Scroll to alert smoothly
            setTimeout(() => {
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            // Fallback: insert at top of form
            const form = document.getElementById('checkoutForm');
            if (form) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'row mb-4';
                errorDiv.innerHTML = `<div class="col-12">${errorHtml}</div>`;
                form.insertBefore(errorDiv, form.firstChild);
            }
        }
    }

    clearErrors() {
        document.querySelectorAll('.alert-danger').forEach(alert => {
            alert.remove();
        });
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        // Show loading state
        this.setLoadingState(true);

        // Validate form (includes stock validation)
        const isValid = await this.validateForm();
       
        if (!isValid) {
            // Re-enable submit button if validation fails
            this.setLoadingState(false);
            return false;
        }

        // If validation passes, submit the form
        this.form.submit();
        return true;
    }

    setLoadingState(isLoading) {
        if (!this.submitBtn) return;

        const btnText = this.submitBtn.querySelector('.btn-text');
        const btnLoading = this.submitBtn.querySelector('.btn-loading');

        if (isLoading) {
            this.submitBtn.disabled = true;
            if (btnText) btnText.classList.add('d-none');
            if (btnLoading) btnLoading.classList.remove('d-none');
        } else {
            this.submitBtn.disabled = false;
            if (btnText) btnText.classList.remove('d-none');
            if (btnLoading) btnLoading.classList.add('d-none');
        }
    }

    setupCartValidation() {
        // Validate cart on page load
        this.validateCart();

        // Set up periodic validation (every 30 seconds)
        setInterval(() => {
            this.validateCart();
        }, 30000);
    }

    async validateCart() {
        try {
            const response = await fetch('/api/orders/validate-cart', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.success) {
                // temporarily ignore unavailable product message
                const filtered = (data.errors || []).filter(
                    e => !e.includes('is no longer available')
                );
            
               
                if (filtered.length) {
                    this.showCartValidationErrors(filtered);
                }
            }
            
        } catch (error) {
            console.warn('Cart validation failed:', error);
        }
    }

    showCartValidationErrors(errors) {
        // Format errors to be customer-friendly
        const formatError = (error) => {
            // Convert technical messages to customer-friendly ones
            let friendlyError = error;
            
            // Stock-related errors
            if (error.includes('Insufficient stock') || error.includes('out of stock')) {
                friendlyError = error.replace(/Insufficient stock for '([^']+)'\. Available: (\d+), Requested: (\d+)/, 
                    'Sorry, "$1" is not available in the quantity you requested. Only $2 items are available.');
                friendlyError = friendlyError.replace(/Product '([^']+)' is out of stock/, 
                    'Sorry, "$1" is currently out of stock. Please remove it from your cart to continue.');
                friendlyError = friendlyError.replace(/Product '([^']+)' is no longer available/, 
                    'Sorry, "$1" is no longer available. Please remove it from your cart to continue.');
                friendlyError = friendlyError.replace(/is no longer available/, 
                    'is no longer available. Please remove it from your cart to continue.');
            }
            
            // Cart-related errors
            if (error.includes('Cart is empty')) {
                friendlyError = 'Your cart is empty. Please add items to your cart before checkout.';
            }
            if (error.includes('Cart has expired')) {
                friendlyError = 'Your cart session has expired. Please add items to your cart again.';
            }
            if (error.includes('Cart validation failed')) {
                friendlyError = 'We couldn\'t verify your cart. Please refresh the page and try again.';
            }
            
            // Price-related errors
            if (error.includes('Price has changed')) {
                friendlyError = error.replace(/Price has changed for '([^']+)'\. Please review your cart/, 
                    'The price for "$1" has changed. Please review your cart and update if needed.');
            }
            
            return friendlyError;
        };
        
        const friendlyErrors = errors.map(formatError);
        
        const errorHtml = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6><i class="lni lni-warning me-2"></i>Please review your cart:</h6>
                <ul class="mb-0">
                    ${friendlyErrors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <div class="mt-2">
                    <a href="${window.location.origin}/shoping-cart" class="btn btn-sm btn-outline-primary">
                        <i class="lni lni-shopping-basket me-1"></i>Review Cart
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Find the dedicated cart validation alert container
        const alertContainer = document.getElementById('cartValidationAlertContainer');

        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h6>
                        <i class="lni lni-warning me-2"></i>
                        Please review your cart:
                    </h6>
                    <ul class="mb-0">
                        ${friendlyErrors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
        
                    <div class="mt-2">
                        <a href="${window.location.origin}/shoping-cart" class="btn btn-sm btn-outline-primary">
                            <i class="lni lni-shopping-basket me-1"></i>Review Cart
                        </a>
                    </div>
        
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        
            setTimeout(() => {
                alertContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        }
        
        
        // Insert the alert HTML
        if (alertContainer) {
            // Clear any existing content
            alertContainer.innerHTML = '';
            // Add the alert directly
            alertContainer.innerHTML = errorHtml;
            // Ensure it's positioned relative and doesn't overflow
            alertContainer.style.position = 'relative';
            alertContainer.style.zIndex = 'auto';
            alertContainer.style.overflow = 'visible';
            
            // Scroll to alert smoothly
            setTimeout(() => {
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            // Last resort: show as fixed alert at top of page (below header)
            const header = document.querySelector('.header');
            const headerHeight = header ? header.offsetHeight : 120;
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `position: fixed; top: ${headerHeight}px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 800px; z-index: 1000;`;
            errorDiv.innerHTML = `<div class="container"><div class="row"><div class="col-12">${errorHtml}</div></div></div>`;
            document.body.insertBefore(errorDiv, document.body.firstChild);
        }
    }

    // Public method to refresh addresses (useful for AJAX updates)
    async refreshAddresses() {
        try {
            const response = await fetch('/api/orders/addresses', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // You could update the address list dynamically here
                console.log('Addresses refreshed:', data.data);
            }
        } catch (error) {
            console.warn('Failed to refresh addresses:', error);
        }
    }
}

// Initialize checkout manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('checkoutForm')) {
        window.checkoutManager = new CheckoutManager();
    }
});

// Export for potential use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CheckoutManager;
}
