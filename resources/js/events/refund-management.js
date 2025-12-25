$(function() {
    let currentPage = 1;
    let currentPaymentId = null;

    // Fetch refunds
    function fetchRefunds(page = 1) {
        $('#loadingState').removeClass('d-none');
        $('#emptyState').addClass('d-none');
        $('#tableContent').addClass('d-none');

        const filters = {
            status: $('#statusFilter').val(),
            date_from: $('#dateFromFilter').val(),
            date_to: $('#dateToFilter').val(),
            sort: $('#sortFilter').val(),
            page: page,
            per_page: 15
        };

        $.ajax({
            url: window.RefundConfig.fetchUrl,
            method: 'GET',
            data: filters,
            success: function(response) {
                $('#loadingState').addClass('d-none');

                if (response.success && response.refunds.length > 0) {
                    renderTable(response.refunds);
                    renderPagination(response.pagination);
                    updateStats(filters.status);
                    $('#tableContent').removeClass('d-none');
                } else {
                    $('#emptyState').removeClass('d-none');
                }
            },
            error: function(xhr) {
                $('#loadingState').addClass('d-none');
                showToast('error', 'Failed to load refund requests.');
            }
        });
    }

    // Render table
    function renderTable(refunds) {
        const tbody = $('#refundsTableBody');
        tbody.empty();

        refunds.forEach(refund => {
            const daysRemaining = parseInt(refund.days_remaining); 
            let deadlineBadge = '';
            
            if (refund.refund_status === 'pending' && !isNaN(daysRemaining)) {
                if (daysRemaining <= 1) {
                    deadlineBadge = `<span class="deadline-badge deadline-urgent">${daysRemaining} day(s) left</span>`;
                } else if (daysRemaining <= 3) {
                    deadlineBadge = `<span class="deadline-badge deadline-warning">${daysRemaining} days left</span>`;
                } else {
                    deadlineBadge = `<span class="deadline-badge deadline-normal">${daysRemaining} days left</span>`;
                }
            } else {
                deadlineBadge = '<span class="text-muted">-</span>';
            }

            const statusBadge = `<span class="status-badge status-${refund.refund_status}">${refund.refund_status.charAt(0).toUpperCase() + refund.refund_status.slice(1)}</span>`;

            const requestedDate = new Date(refund.refund_requested_at).toLocaleDateString('en-MY', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            let actions = '';
            if (refund.refund_status === 'pending') {
                actions = `
                    <button class="btn btn-sm btn-success action-btn approve-btn me-1" data-id="${refund.id}" title="Approve">
                        <i class="bi bi-check-circle"></i>
                    </button>
                    <button class="btn btn-sm btn-danger action-btn reject-btn me-1" data-id="${refund.id}" title="Reject">
                        <i class="bi bi-x-circle"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary action-btn details-btn" data-id="${refund.id}" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                `;
            } else {
                actions = `
                    <button class="btn btn-sm btn-outline-secondary action-btn details-btn" data-id="${refund.id}" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                `;
            }

            const row = `
                <tr data-refund='${JSON.stringify(refund)}'>
                    <td><strong>${refund.registration_number}</strong></td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${refund.event_title}">
                        ${refund.event_title}
                    </td>
                    <td>
                        <div>${refund.user_name}</div>
                        <small class="text-muted">${refund.user_email}</small>
                    </td>
                    <td><strong>RM ${parseFloat(refund.refund_amount).toFixed(2)}</strong></td>
                    <td>${requestedDate}</td>
                    <td>${statusBadge}</td>
                    <td>${deadlineBadge}</td>
                    <td>${actions}</td>
                </tr>
            `;

            tbody.append(row);
        });
    }

    // Render pagination
    function renderPagination(pagination) {
        const container = $('#paginationContainer');
        container.empty();

        const info = `<div class="pagination-info">Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total} results</div>`;
        
        let paginationHtml = '<nav><ul class="pagination mb-0">';
        
        // Previous
        paginationHtml += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
        </li>`;
        
        // Pages
        for (let i = 1; i <= pagination.last_page; i++) {
            paginationHtml += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        
        // Next
        paginationHtml += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
        </li>`;
        
        paginationHtml += '</ul></nav>';

        container.append(info);
        container.append(paginationHtml);

        currentPage = pagination.current_page;
    }

    // Update stats
    function updateStats(statusFilter) {
        // Simplified - you can make separate API calls for accurate counts
        $('#pendingCount').text(statusFilter === 'pending' ? currentPage : '-');
        $('#processingCount').text(statusFilter === 'processing' ? currentPage : '-');
        $('#completedCount').text(statusFilter === 'completed' ? currentPage : '-');
        $('#rejectedCount').text(statusFilter === 'rejected' ? currentPage : '-');
    }

    // Show toast notification
    function showToast(type, message) {
        // Implement your toast notification here
        alert(message);
    }

    // Event handlers
    $('#applyFilters, #refreshBtn').on('click', function() {
        fetchRefunds(1);
    });

    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('pending');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        $('#sortFilter').val('recent');
        fetchRefunds(1);
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && page !== currentPage) {
            fetchRefunds(page);
        }
    });

    // Approve button
    $(document).on('click', '.approve-btn', function() {
        const refundData = $(this).closest('tr').data('refund');
        currentPaymentId = refundData.id;

        const detailsHtml = `
            <div class="detail-row">
                <span class="detail-label">Event:</span>
                <span class="detail-value">${refundData.event_title}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Participant:</span>
                <span class="detail-value">${refundData.user_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value"><strong>RM ${parseFloat(refundData.refund_amount).toFixed(2)}</strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Reason:</span>
                <span class="detail-value">${refundData.refund_reason}</span>
            </div>
        `;

        $('#approveDetails').html(detailsHtml);
        $('#approveModal').modal('show');
    });

    // Reject button
    $(document).on('click', '.reject-btn', function() {
        const refundData = $(this).closest('tr').data('refund');
        currentPaymentId = refundData.id;

        const detailsHtml = `
            <div class="detail-row">
                <span class="detail-label">Event:</span>
                <span class="detail-value">${refundData.event_title}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Participant:</span>
                <span class="detail-value">${refundData.user_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value"><strong>RM ${parseFloat(refundData.refund_amount).toFixed(2)}</strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Reason:</span>
                <span class="detail-value">${refundData.refund_reason}</span>
            </div>
        `;

        $('#rejectDetails').html(detailsHtml);
        $('#rejectionReason').val('');
        $('#rejectModal').modal('show');
    });

    // Submit approve
    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#confirmApproveBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        const url = window.RefundConfig.approveUrl.replace(':id', currentPaymentId); 

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.RefundConfig.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    $('#approveModal').modal('hide');
                    showToast('success', response.message);
                    fetchRefunds(currentPage);
                }
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Failed to approve refund.');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Approve Refund');
            }
        });
    });

    // Submit reject
    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        const reason = $('#rejectionReason').val().trim();

        if (reason.length < 10) {
            $('#rejectionReason').addClass('is-invalid');
            $('#rejectionReason').siblings('.invalid-feedback').text('Reason must be at least 10 characters.');
            return;
        }

        const btn = $('#confirmRejectBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        const url = window.RefundConfig.rejectUrl.replace(':id', currentPaymentId);

        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.RefundConfig.csrfToken
            },
            data: { rejection_reason: reason },
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    showToast('success', response.message);
                    fetchRefunds(currentPage);
                }
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Failed to reject refund.');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-x-circle me-2"></i>Reject Refund');
            }
        });
    });

    // View details
    $(document).on('click', '.details-btn', function() {
        const refundData = $(this).closest('tr').data('refund');

        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-3">Event Information</h6>
                    <div class="refund-details">
                        <div class="detail-row">
                            <span class="detail-label">Event:</span>
                            <span class="detail-value">${refundData.event_title}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Registration #:</span>
                            <span class="detail-value">${refundData.registration_number}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Participant Information</h6>
                    <div class="refund-details">
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value">${refundData.user_name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${refundData.user_email}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-3">Refund Details</h6>
                    <div class="refund-details">
                        <div class="detail-row">
                            <span class="detail-label">Original Amount:</span>
                            <span class="detail-value">RM ${parseFloat(refundData.amount).toFixed(2)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Refund Amount:</span>
                            <span class="detail-value"><strong>RM ${parseFloat(refundData.refund_amount).toFixed(2)}</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value"><span class="status-badge status-${refundData.refund_status}">${refundData.refund_status.charAt(0).toUpperCase() + refundData.refund_status.slice(1)}</span></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Requested On:</span>
                            <span class="detail-value">${new Date(refundData.refund_requested_at).toLocaleString('en-MY')}</span>
                        </div>
                        ${refundData.refund_processed_at ? `
                        <div class="detail-row">
                            <span class="detail-label">Processed On:</span>
                            <span class="detail-value">${new Date(refundData.refund_processed_at).toLocaleString('en-MY')}</span>
                        </div>
                        ` : ''}
                        ${refundData.auto_reject_at && refundData.refund_status === 'pending' ? `
                        <div class="detail-row">
                            <span class="detail-label">Auto-reject Deadline:</span>
                            <span class="detail-value">${new Date(refundData.auto_reject_at).toLocaleString('en-MY')}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-3">Refund Reason</h6>
                    <div class="alert alert-light">
                        ${refundData.refund_reason}
                    </div>
                </div>
            </div>
        `;

        $('#detailsContent').html(detailsHtml);
        $('#detailsModal').modal('show');
    });

    // Initial load
    fetchRefunds(1);
});