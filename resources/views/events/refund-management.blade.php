@extends('layouts.club')

@section('title', 'Refund Management')

@section('content')
<div class="refund-management-page">
    <div class="page-header">
        <div class="header-content">
            <!-- Back Button -->
            @if(isset($event))
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary btn-back mb-3">
                <i class="bi bi-arrow-left me-2"></i>Back to Event
            </a>
            @endif
            
            <h1 class="page-title">
                <i class="bi bi-cash-coin me-2"></i>
                Refund Management
            </h1>
            <p class="page-subtitle">
                @if(isset($event))
                Managing refunds for: <strong>{{ $event->title }}</strong>
                @else
                Review and process refund requests from participants
                @endif
            </p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-pending">
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="pendingCount">-</div>
                    <div class="stat-label">Pending Review</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-processing">
                <div class="stat-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="processingCount">-</div>
                    <div class="stat-label">Processing</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-completed">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="completedCount">-</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-rejected">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="rejectedCount">-</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="controls-card">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select auto-apply-filter" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending" selected>Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" class="form-control auto-apply-filter" id="dateFromFilter" max="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" class="form-control auto-apply-filter" id="dateToFilter" max="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort By</label>
                <select class="form-select auto-apply-filter" id="sortFilter">
                    <option value="recent">Most Recent</option>
                    <option value="oldest">Oldest First</option>
                    <option value="amount_high">Amount: High to Low</option>
                    <option value="amount_low">Amount: Low to High</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Refund Requests Table -->
    <div class="table-card">
        <div class="table-header">
            <h3>
                <i class="bi bi-list-ul me-2"></i>
                Refund Requests
            </h3>
            <div class="table-actions">
                <button class="btn btn-sm btn-outline-primary" id="refreshBtn">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        <div class="table-container">
            <!-- Loading State -->
            <div id="loadingState" class="loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Loading refund requests...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state d-none">
                <i class="bi bi-inbox"></i>
                <h4>No Refund Requests Found</h4>
                <p>There are no refund requests matching your filters.</p>
            </div>

            <!-- Table -->
            <div id="tableContent" class="d-none">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Registration #</th>
                                <th>Event</th>
                                <th>Participant</th>
                                <th>Amount</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="refundsTableBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container" id="paginationContainer">
                    <!-- Populated via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals remain the same -->
<!-- Approve Refund Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Approve Refund Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Are you sure you want to approve this refund?</strong>
                    </div>

                    <div class="refund-details" id="approveDetails">
                        <!-- Populated dynamically -->
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>
                            <strong>This action will:</strong><br>
                            • Process the refund through the payment gateway<br>
                            • Send notification to the participant<br>
                            • Update the registration status<br>
                            • This action cannot be undone
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="confirmApproveBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Approve Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Refund Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle text-danger me-2"></i>
                    Reject Refund Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Are you sure you want to reject this refund?</strong>
                    </div>

                    <div class="refund-details" id="rejectDetails">
                        <!-- Populated dynamically -->
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control" 
                            id="rejectionReason" 
                            name="rejection_reason"
                            rows="4" 
                            required
                            placeholder="Please provide a clear reason for rejecting this refund request..."></textarea>
                        <div class="form-text">Minimum 10 characters. This will be sent to the participant.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="confirmRejectBtn">
                        <i class="bi bi-x-circle me-2"></i>
                        Reject Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Refund Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Populated dynamically -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
@vite('resources/css/events/refund-management.css')
@endpush

@push('scripts')
<script>
    window.RefundConfig = {
        fetchUrl: "{{ route('events.refunds.fetch') }}",
        approveUrl: "/events/refunds/:id/approve",
        rejectUrl: "/events/refunds/:id/reject",
        csrfToken: "{{ csrf_token() }}",
        @if(isset($event))
        eventId: {{ $event->id }}
        @endif
    };
</script>
@vite('resources/js/events/refund-management.js')
@endpush

@endsection