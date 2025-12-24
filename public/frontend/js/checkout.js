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

    validateForm() {
        let isValid = true;
        const errors = [];

        // Clear previous errors
        this.clearErrors();

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
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6>Please fix the following errors:</h6>
                <ul class="mb-0">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Insert error at the top of the container
        const container = document.querySelector('.container');
        const firstRow = container.querySelector('.row');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'row mb-4';
        errorDiv.innerHTML = `<div class="col-12">${errorHtml}</div>`;
        
        container.insertBefore(errorDiv, firstRow);

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    clearErrors() {
        document.querySelectorAll('.alert-danger').forEach(alert => {
            alert.remove();
        });
    }

    handleFormSubmit(e) {
        // Validate form
        if (!this.validateForm()) {
            e.preventDefault();
            return false;
        }

        // Show loading state
        this.setLoadingState(true);

        // Allow form to submit normally
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
                this.showCartValidationErrors(data.errors || ['Cart validation failed']);
            }
        } catch (error) {
            console.warn('Cart validation failed:', error);
        }
    }

    showCartValidationErrors(errors) {
        const errorHtml = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h6><i class="lni lni-warning me-2"></i>Cart Validation Issues:</h6>
                <ul class="mb-0">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
                <div class="mt-2">
                    <a href="${window.location.origin}/shoping-cart" class="btn btn-sm btn-outline-primary">
                        Review Cart
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Remove existing cart validation alerts
        document.querySelectorAll('.alert-warning').forEach(alert => {
            if (alert.textContent.includes('Cart Validation')) {
                alert.remove();
            }
        });

        // Insert new alert
        const container = document.querySelector('.container');
        const firstRow = container.querySelector('.row');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'row mb-4';
        errorDiv.innerHTML = `<div class="col-12">${errorHtml}</div>`;
        
        container.insertBefore(errorDiv, firstRow);
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
