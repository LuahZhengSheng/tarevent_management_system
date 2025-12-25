@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="payment-history-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header-custom">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('events.my') }}">My Events</a></li>
                    <li class="breadcrumb-item active">Payment History</li>
                </ol>
            </nav>

            <div class="header-content">
                <h1 class="page-title">
                    <i class="bi bi-receipt me-2"></i>
                    Payment History
                </h1>
                <p class="page-subtitle">View all your payment transactions and refunds</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Successful Payments</div>
                        <div class="stat-value" id="successCount">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-pending">
                    <div class="stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value" id="pendingCount">-</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-refund">
                    <div class="stat-icon">
                        <i class="bi bi-arrow-return-left"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Refunded</div>
                        <div class="stat-value" id="refundTotal">RM 0.00</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Spent</div>
                        <div class="stat-value" id="totalSpent">RM 0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="controls-section">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" 
                               class="form-control" 
                               id="searchInput" 
                               placeholder="Search by event or transaction ID...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="payment">Payment</option>
                        <option value="refund">Refund</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Method</label>
                    <select class="form-select" id="methodFilter">
                        <option value="">All Methods</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select class="form-select" id="sortFilter">
                        <option value="recent">Most Recent</option>
                        <option value="oldest">Oldest First</option>
                        <option value="amount_high">Amount: High to Low</option>
                        <option value="amount_low">Amount: Low to High</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-primary w-100" id="applyFiltersBtn">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment List -->
        <div class="payments-container">
            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading payment history...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state d-none">
                <i class="bi bi-receipt"></i>
                <h4>No Payments Found</h4>
                <p>You haven't made any payments yet.</p>
            </div>

            <!-- Results -->
            <div id="resultsContainer" class="d-none">
                <div class="results-header">
                    <span id="resultsCount">0 payments found</span>
                </div>
                <div id="paymentsList" class="payments-list">
                    <!-- Populated via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Detail Modal -->
<div class="modal fade" id="paymentDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-receipt me-2"></i>
                    Payment Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetailContent">
                <!-- Populated via AJAX -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary d-none" id="downloadReceiptBtn">
                    <i class="bi bi-download me-2"></i>
                    Download Receipt
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/events/payment-history.css')
@endpush

@push('scripts')
<script>
    window.PaymentHistoryConfig = {
        fetchUrl: "{{ route('payments.fetchHistory') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
@vite('resources/js/events/payment-history.js')
@endpush

@endsection