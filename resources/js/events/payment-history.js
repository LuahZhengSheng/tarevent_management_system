// Payment History Page JavaScript
$(function() {
    const fetchUrl = window.PaymentHistoryConfig.fetchUrl;
    const csrfToken = window.PaymentHistoryConfig.csrfToken;

    // Fetch payments
    function fetchPayments() {
        const filters = {
            search: $('#searchInput').val(),
            type: $('#typeFilter').val(),
            status: $('#statusFilter').val(),
            method: $('#methodFilter').val(),
            sort: $('#sortFilter').val()
        };

        $('#loadingState').removeClass('d-none');
        $('#emptyState').addClass('d-none');
        $('#resultsContainer').addClass('d-none');

        $.ajax({
            url: fetchUrl,
            method: 'GET',
            data: filters,
            success: function(response) {
                $('#loadingState').addClass('d-none');

                if (response.success && response.payments.length > 0) {
                    renderPayments(response.payments);
                    updateStatistics(response.statistics);
                    $('#resultsCount').text(`${response.payments.length} payment${response.payments.length > 1 ? 's' : ''} found`);
                    $('#resultsContainer').removeClass('d-none');
                } else {
                    $('#emptyState').removeClass('d-none');
                    updateStatistics({
                        success_count: 0,
                        pending_count: 0,
                        refund_total: 0,
                        total_spent: 0
                    });
                }
            },
            error: function(xhr) {
                $('#loadingState').addClass('d-none');
                showToast('error', 'Failed to load payment history.');
            }
        });
    }

    // Update statistics
    function updateStatistics(stats) {
        $('#successCount').text(stats.success_count || 0);
        $('#pendingCount').text(stats.pending_count || 0);
        $('#refundTotal').text('RM ' + parseFloat(stats.refund_total || 0).toFixed(2));
        $('#totalSpent').text('RM ' + parseFloat(stats.total_spent || 0).toFixed(2));
    }

    // Render payments list
    function renderPayments(payments) {
        const container = $('#paymentsList');
        container.empty();

        payments.forEach(payment => {
            const isRefund = payment.type === 'refund';
            const statusClass = `status-${payment.status}`;
            const typeClass = `type-${payment.type}`;
            const amountClass = isRefund ? 'amount-refund' : 'amount-payment';
            
            const item = $(`
                <div class="payment-item payment-type-${payment.type}" data-id="${payment.id}">
                    <div class="payment-header">
                        <div class="payment-event-info">
                            <div class="payment-event-title">${payment.event_title}</div>
                            <div class="payment-transaction-id">
                                ${isRefund ? 'Refund' : 'Payment'} ID: ${payment.transaction_id || 'N/A'}
                            </div>
                        </div>
                        <div class="payment-amount-section">
                            <div class="payment-amount ${amountClass}">
                                RM ${parseFloat(payment.amount).toFixed(2)}
                            </div>
                            <span class="payment-type-badge ${typeClass}">
                                ${isRefund ? 'Refund' : 'Payment'}
                            </span>
                        </div>
                    </div>
                    <div class="payment-info">
                        <div class="info-item">
                            <i class="bi bi-calendar3"></i>
                            <span>${formatDate(payment.created_at)}</span>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-credit-card ${payment.method === 'stripe' ? 'method-stripe' : 'method-paypal'}"></i>
                            <span>${payment.method.charAt(0).toUpperCase() + payment.method.slice(1)}</span>
                        </div>
                        <div class="info-item">
                            <span class="status-badge ${statusClass}">
                                ${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
            `);

            item.on('click', function() {
                showPaymentDetail(payment);
            });

            container.append(item);
        });
    }

    // Show payment detail modal
    function showPaymentDetail(payment) {
        const detailContent = $('#paymentDetailContent');
        detailContent.html(generatePaymentDetailHTML(payment));
        
        // Show/hide download button
        if (payment.status === 'success' && payment.type === 'payment') {
            $('#downloadReceiptBtn').removeClass('d-none').off('click').on('click', function() {
                window.location.href = `/payments/${payment.id}/receipt/download`;
            });
        } else if (payment.type === 'refund' && payment.refund_status === 'completed') {
            $('#downloadReceiptBtn').removeClass('d-none').off('click').on('click', function() {
                window.location.href = `/payments/${payment.id}/refund-receipt/download`;
            });
        } else {
            $('#downloadReceiptBtn').addClass('d-none');
        }

        $('#paymentDetailModal').modal('show');
    }

    // Generate payment detail HTML
    function generatePaymentDetailHTML(payment) {
        const isRefund = payment.type === 'refund';
        
        let html = `
            <div class="detail-section">
                <h6>
                    <i class="bi bi-${isRefund ? 'arrow-return-left' : 'credit-card'} me-2"></i>
                    ${isRefund ? 'Refund' : 'Payment'} Information
                </h6>
                <div class="detail-row">
                    <span class="detail-label">Type</span>
                    <span class="detail-value">
                        <span class="payment-type-badge type-${payment.type}">
                            ${isRefund ? 'Refund' : 'Payment'}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount</span>
                    <span class="detail-value">
                        <span class="${isRefund ? 'amount-refund-highlight' : 'amount-highlight'}">
                            ${isRefund ? '-' : ''}RM ${parseFloat(payment.amount).toFixed(2)}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-badge status-${payment.status}">
                            ${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value">
                        <span class="payment-method-icon">
                            <i class="bi bi-${payment.method === 'stripe' ? 'credit-card' : 'paypal'} ${payment.method === 'stripe' ? 'method-stripe' : 'method-paypal'}"></i>
                            ${payment.method.charAt(0).toUpperCase() + payment.method.slice(1)}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">${formatDateTime(payment.created_at)}</span>
                </div>
                ${payment.paid_at ? `
                <div class="detail-row">
                    <span class="detail-label">${isRefund ? 'Refunded' : 'Paid'} On</span>
                    <span class="detail-value">${formatDateTime(payment.paid_at)}</span>
                </div>
                ` : ''}
                ${payment.transaction_id ? `
                <div class="detail-row">
                    <span class="detail-label">Transaction ID</span>
                    <span class="detail-value"><code>${payment.transaction_id}</code></span>
                </div>
                ` : ''}
            </div>

            <div class="detail-section">
                <h6><i class="bi bi-calendar-event me-2"></i>Event Information</h6>
                <div class="detail-row">
                    <span class="detail-label">Event Title</span>
                    <span class="detail-value">${payment.event_title}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Registration Number</span>
                    <span class="detail-value"><code>#${payment.registration_number}</code></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Event Date</span>
                    <span class="detail-value">${formatDateTime(payment.event_date)}</span>
                </div>
            </div>

            <div class="detail-section">
                <h6><i class="bi bi-person me-2"></i>Payer Information</h6>
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">${payment.payer_name || 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">${payment.payer_email || 'N/A'}</span>
                </div>
            </div>
        `;

        // Refund-specific information
        if (isRefund && payment.refund_reason) {
            html += `
                <div class="detail-section">
                    <h6><i class="bi bi-chat-square-text me-2"></i>Refund Details</h6>
                    ${payment.refund_reason ? `
                    <div class="detail-row">
                        <span class="detail-label">Reason</span>
                        <span class="detail-value">${payment.refund_reason}</span>
                    </div>
                    ` : ''}
                    ${payment.refund_requested_at ? `
                    <div class="detail-row">
                        <span class="detail-label">Requested On</span>
                        <span class="detail-value">${formatDateTime(payment.refund_requested_at)}</span>
                    </div>
                    ` : ''}
                    ${payment.refund_processed_at ? `
                    <div class="detail-row">
                        <span class="detail-label">Processed On</span>
                        <span class="detail-value">${formatDateTime(payment.refund_processed_at)}</span>
                    </div>
                    ` : ''}
                    ${payment.refund_rejection_reason ? `
                    <div class="detail-row">
                        <span class="detail-label">Rejection Reason</span>
                        <span class="detail-value text-danger">${payment.refund_rejection_reason}</span>
                    </div>
                    ` : ''}
                </div>
            `;
        }

        // Card information for Stripe
        if (payment.method === 'stripe' && payment.card_info) {
            html += `
                <div class="detail-section">
                    <h6><i class="bi bi-credit-card-2-front me-2"></i>Card Information</h6>
                    <div class="detail-row">
                        <span class="detail-label">Card Brand</span>
                        <span class="detail-value">${payment.card_info.brand || 'N/A'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last 4 Digits</span>
                        <span class="detail-value">•••• ${payment.card_info.last4 || 'N/A'}</span>
                    </div>
                </div>
            `;
        }

        // Receipt section
        if (payment.status === 'success') {
            html += `
                <div class="receipt-section">
                    <div class="receipt-header">
                        <h5>Official Receipt</h5>
                        <div class="receipt-number">Receipt #: ${payment.id.toString().padStart(8, '0')}</div>
                    </div>
                    <div class="text-center mt-3">
                        <p class="text-muted small mb-0">
                            This is an official ${isRefund ? 'refund' : 'payment'} receipt from TAREvent Management System
                        </p>
                    </div>
                </div>
            `;
        }

        return html;
    }

    // Helper functions
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-MY', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-MY', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showToast(type, message) {
        const toast = $(`
            <div class="toast-notification toast-${type}">
                <i class="bi bi-${type === 'error' ? 'x-circle' : 'check-circle'} me-2"></i>
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.addClass('show'), 100);
        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Event listeners
    $('#applyFiltersBtn').on('click', fetchPayments);
    
    $('#searchInput').on('keyup', function(e) {
        if (e.key === 'Enter') {
            fetchPayments();
        }
    });

    // Initial load
    fetchPayments();
});