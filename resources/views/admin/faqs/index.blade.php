@extends('layouts.admin')

@section('title', 'FAQ Management')

@push('styles')
<style>
    .image-preview {
        max-width: 400px;
        max-height: 300px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        margin-top: 10px;
    }
    
    /* Fix modal scrolling */
    .modal-dialog-scrollable {
        max-height: calc(100vh - 3.5rem);
    }
    
    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 210px);
        overflow-y: auto !important;
    }
    
    .modal-dialog-scrollable .modal-content {
        max-height: calc(100vh - 3.5rem);
        overflow: hidden;
    }
    
    /* Q&A Pair Styles */
    .qa-pair {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
        position: relative;
    }
    
    .qa-pair-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .qa-pair-header strong {
        color: #495057;
        font-size: 0.95rem;
    }
    
    .remove-qa-btn {
        margin-left: auto;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #e7f1ff;
        color: #0c63e4;
    }
</style>
@endpush

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">FAQs</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">FAQ Management</h1>
            </div>
            <div class="col-auto d-flex">
                <button type="button" class="btn btn-primary" id="addFaqBtn">
                    <i class="fas fa-plus"></i> Add FAQ Category
                </button>
            </div>
        </div>

        <div id="alertContainer"></div>

        <!-- FAQ Categories List -->
        <div class="row" id="faqsList">
        @forelse($faqs as $faq)
        <div class="col-md-12 mb-4" data-faq-id="{{ $faq->id }}">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-folder-open text-primary me-2"></i>{{ $faq->category }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="form-check form-switch mb-0 me-2">
                            <input class="form-check-input toggle-status" type="checkbox" 
                                   data-id="{{ $faq->id }}" 
                                   {{ $faq->is_active ? 'checked' : '' }}
                                   style="cursor: pointer;">
                            <label class="form-check-label small">
                                {{ $faq->is_active ? 'Active' : 'Inactive' }}
                            </label>
                        </div>
                        <button class="btn btn-sm btn-outline-primary edit-faq" data-id="{{ $faq->id }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-faq" data-id="{{ $faq->id }}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="accordion{{ $faq->id }}">
                        @foreach($faq->questions_answers as $index => $qa)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $faq->id }}_{{ $index }}">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $faq->id }}_{{ $index }}" 
                                        aria-expanded="false" 
                                        aria-controls="collapse{{ $faq->id }}_{{ $index }}">
                                    <i class="fas fa-question-circle text-primary me-2"></i>
                                    <strong>Q{{ $index + 1 }}:</strong>&nbsp;{{ $qa['question'] }}
                                </button>
                            </h2>
                            <div id="collapse{{ $faq->id }}_{{ $index }}" 
                                 class="accordion-collapse collapse" 
                                 aria-labelledby="heading{{ $faq->id }}_{{ $index }}" 
                                 data-bs-parent="#accordion{{ $faq->id }}">
                                <div class="accordion-body bg-light">
                                    <div class="text-muted mb-1"><strong>Answer:</strong></div>
                                    <div>{{ $qa['answer'] }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="fas fa-info-circle fa-3x"></i>
                    </div>
                    <h5>No FAQ categories found</h5>
                    <p class="text-muted">Click "Add FAQ Category" to create your first FAQ category.</p>
                </div>
            </div>
        </div>
        @endforelse
        </div>
    </div>
</div>

<!-- Add/Edit FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faqModalLabel">Add FAQ Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="faqForm">
                <div class="modal-body">
                    <input type="hidden" id="faqId" name="faq_id">
                    
                    <div class="mb-4">
                        <label for="category" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category" name="category" 
                               placeholder="e.g., Orders, Shipping, Returns" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0">Questions & Answers <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-primary" id="addQaBtn">
                                <i class="fas fa-plus"></i> Add Question & Answer
                            </button>
                        </div>
                        <div id="qaContainer">
                            <!-- Q&A pairs will be added here -->
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveFaqBtn">
                        <span class="btn-text">Save FAQ</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let qaCounter = 0;
let editingFaqId = null;

$(document).ready(function() {
    // Add FAQ Category Button
    $('#addFaqBtn').on('click', function() {
        editingFaqId = null;
        $('#faqModalLabel').text('Add FAQ Category');
        $('#faqForm')[0].reset();
        $('#faqId').val('');
        $('#qaContainer').empty();
        qaCounter = 0;
        addQaPair(); // Add one Q&A pair by default
        $('#faqModal').modal('show');
    });

    // Add Q&A Pair
    $('#addQaBtn').on('click', function() {
        addQaPair();
    });

    // Remove Q&A Pair
    $(document).on('click', '.remove-qa-btn', function() {
        if ($('.qa-pair').length > 1) {
            $(this).closest('.qa-pair').remove();
        } else {
            showAlert('warning', 'At least one question & answer is required');
        }
    });

    // Edit FAQ
    $(document).on('click', '.edit-faq', function() {
        const faqId = $(this).data('id');
        editingFaqId = faqId;
        
        // Find the FAQ data from the page
        const faqCard = $(this).closest('.card');
        const category = faqCard.find('h5').first().text().trim();
        
        // Get all Q&A pairs from the accordion
        const qaPairs = [];
        faqCard.find('.accordion-item').each(function() {
            const question = $(this).find('.accordion-button').text().trim().replace(/^Q\d+:\s*/, '');
            const answer = $(this).find('.accordion-body').text().trim().replace('Answer: ', '');
            qaPairs.push({ question, answer });
        });

        // Populate modal
        $('#faqModalLabel').text('Edit FAQ Category');
        $('#faqId').val(faqId);
        $('#category').val(category);
        $('#qaContainer').empty();
        qaCounter = 0;

        // Add Q&A pairs
        qaPairs.forEach(function(qa) {
            addQaPair(qa.question, qa.answer);
        });

        $('#faqModal').modal('show');
    });

    // Save FAQ (Create or Update)
    $('#faqForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Collect Q&A data
        const questionsAnswers = [];
        $('.qa-pair').each(function() {
            const question = $(this).find('.qa-question').val().trim();
            const answer = $(this).find('.qa-answer').val().trim();
            if (question && answer) {
                questionsAnswers.push({ question, answer });
            }
        });

        if (questionsAnswers.length === 0) {
            showAlert('danger', 'Please add at least one question and answer');
            return;
        }

        const formData = {
            category: $('#category').val(),
            questions_answers: questionsAnswers,
            sort_order: $('#sort_order').val() || 0,
            is_active: $('#is_active').is(':checked'),
        };

        const faqId = $('#faqId').val();
        const url = faqId ? '{{ route("faqs.update", ":id") }}'.replace(':id', faqId) : '{{ route("faqs.store") }}';
        const method = faqId ? 'PUT' : 'POST';

        // Show loading
        const $btn = $('#saveFaqBtn');
        const $btnText = $btn.find('.btn-text');
        const $spinner = $btn.find('.spinner-border');
        $btn.prop('disabled', true);
        $btnText.addClass('d-none');
        $spinner.removeClass('d-none');

        $.ajax({
            url: url,
            type: method,
            data: JSON.stringify(formData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#faqModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayErrors(errors);
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btnText.removeClass('d-none');
                $spinner.addClass('d-none');
            }
        });
    });

    // Delete FAQ
    $(document).on('click', '.delete-faq', function() {
        const faqId = $(this).data('id');
        const category = $(this).closest('.card').find('h5').first().text().trim();
        
        if (confirm(`Are you sure you want to delete the FAQ category "${category}"?`)) {
            $.ajax({
                url: '{{ route("faqs.destroy", ":id") }}'.replace(':id', faqId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $(`[data-faq-id="${faqId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            if ($('.card').length === 0) {
                                location.reload();
                            }
                        });
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'Failed to delete FAQ');
                }
            });
        }
    });

    // Toggle Status
    $(document).on('change', '.toggle-status', function() {
        const faqId = $(this).data('id');
        const $checkbox = $(this);
        const $label = $(this).next('label');
        
        $.ajax({
            url: '{{ route("faqs.toggle-status", ":id") }}'.replace(':id', faqId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $label.text(response.is_active ? 'Active' : 'Inactive');
                    showAlert('success', response.message);
                }
            },
            error: function(xhr) {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
                showAlert('danger', 'Failed to update status');
            }
        });
    });
});

// Add Q&A Pair Function
function addQaPair(question = '', answer = '') {
    qaCounter++;
    const escapedQuestion = $('<div>').text(question).html();
    const escapedAnswer = $('<div>').text(answer).html();
    
    const html = `
        <div class="qa-pair">
            <div class="qa-pair-header">
                <strong><i class="fas fa-question-circle text-primary"></i> Q&A Pair #${qaCounter}</strong>
                <button type="button" class="btn btn-sm btn-danger remove-qa-btn">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Question <span class="text-danger">*</span></label>
                <input type="text" class="form-control qa-question" 
                       placeholder="Enter your question here..." value="${escapedQuestion}" required>
            </div>
            <div>
                <label class="form-label fw-medium">Answer <span class="text-danger">*</span></label>
                <textarea class="form-control qa-answer" rows="4" 
                          placeholder="Enter the answer here..." required>${escapedAnswer}</textarea>
            </div>
        </div>
    `;
    $('#qaContainer').append(html);
}

// Display validation errors
function displayErrors(errors) {
    $.each(errors, function(field, messages) {
        const $field = $(`[name="${field}"]`);
        $field.addClass('is-invalid');
        $field.siblings('.invalid-feedback').text(messages[0]);
    });
}

// Show alert
function showAlert(type, message) {
    const iconMap = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${iconMap[type]}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Clear existing alerts
    $('#alertContainer').html('');
    
    // Add new alert
    $('#alertContainer').html(alertHtml);
    
    // Scroll to top
    $('html, body').animate({ scrollTop: 0 }, 300);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        $('#alertContainer .alert').fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}
</script>
@endpush
