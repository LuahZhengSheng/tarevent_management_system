// Registration History Page JavaScript
$(function () {
    const eventId = window.RegistrationHistoryConfig.eventId;
    const fetchUrl = window.RegistrationHistoryConfig.fetchUrl;
    const csrfToken = window.RegistrationHistoryConfig.csrfToken;

    // Fetch registrations
    function fetchRegistrations() {
        const filters = {
            search: $('#searchInput').val(),
            status: $('#statusFilter').val(),
            sort: $('#sortFilter').val()
        };

        $('#loadingState').removeClass('d-none');
        $('#emptyState').addClass('d-none');
        $('#resultsContainer').addClass('d-none');

        $.ajax({
            url: fetchUrl,
            method: 'GET',
            data: filters,
            success: function (response) {
                $('#loadingState').addClass('d-none');

                if (response.success && response.registrations.length > 0) {
                    renderRegistrations(response.registrations);
                    $('#resultsCount').text(`${response.registrations.length} registration${response.registrations.length > 1 ? 's' : ''} found`);
                    $('#resultsContainer').removeClass('d-none');
                } else {
                    $('#emptyState').removeClass('d-none');
                }
            },
            error: function (xhr) {
                $('#loadingState').addClass('d-none');
                showToast('error', 'Failed to load registration history.');
            }
        });
    }

    // Render registrations list
    function renderRegistrations(registrations) {
        const container = $('#registrationsList');
        container.empty();

        registrations.forEach(reg => {
            const statusClass = `status-${reg.status.replace('_', '')}`;
            const statusLabel = reg.status.replace('_', ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

            const item = $(`
                <div class="registration-item" data-id="${reg.id}">
                    <div class="registration-header">
                        <div>
                            <div class="registration-number">#${reg.registration_number}</div>
                        </div>
                        <span class="status-badge ${statusClass}">${statusLabel}</span>
                    </div>
                    <div class="registration-info">
                        <div class="info-item">
                            <i class="bi bi-calendar3"></i>
                            <span>Registered: ${formatDate(reg.created_at)}</span>
                        </div>
                        ${reg.payment_status ? `
                        <div class="info-item">
                            <i class="bi bi-credit-card"></i>
                            <span>Payment: ${reg.payment_status.charAt(0).toUpperCase() + reg.payment_status.slice(1)}</span>
                        </div>
                        ` : ''}
                        ${reg.refund_status ? `
                        <div class="info-item">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Refund: ${reg.refund_status.charAt(0).toUpperCase() + reg.refund_status.slice(1)}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `);

            item.on('click', function () {
                showRegistrationDetail(reg);
            });

            container.append(item);
        });
    }

    // Show registration detail modal
    function showRegistrationDetail(reg) {
        const detailContent = $('#detailContent');
        detailContent.html(generateDetailHTML(reg));

        // ===============================================
        // 1. Payment Receipt Button Logic
        // ===============================================
        const btnPayment = $('#downloadReceiptBtn');

        // Show/hide download button
        if (reg.payment && reg.payment.status === 'success') {
            btnPayment.removeClass('d-none').off('click').on('click', function () {
                window.location.href = `/payments/${reg.payment.id}/download-receipt`;
            });
        } else {
            btnPayment.addClass('d-none');
        }

        // ===============================================
        // 2. Refund Receipt Button Logic
        // ===============================================
        const btnRefund = $('#downloadRefundReceiptBtn');

        // 只有当退款状态是 COMPLETED 时才显示
        if (reg.payment && reg.payment.refund_status === 'completed') {
            btnRefund.removeClass('d-none')
                    .off('click').on('click', function () {
                // 对应后端路由: /payments/{payment}/download-refund-receipt
                window.location.href = `/payments/${reg.payment.id}/download-refund-receipt`;
            });
        } else {
            btnRefund.addClass('d-none');
        }

        $('#detailModal').modal('show');
    }

    // Generate detail HTML
    function generateDetailHTML(reg) {
        let html = `
            <div class="detail-section">
                <h6><i class="bi bi-info-circle me-2"></i>Registration Information</h6>
                <div class="detail-row">
                    <span class="detail-label">Registration Number</span>
                    <span class="detail-value"><strong>#${reg.registration_number}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-badge status-${reg.status.replace('_', '')}">${formatStatus(reg.status)}</span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Registered On</span>
                    <span class="detail-value">${formatDateTime(reg.created_at)}</span>
                </div>
                ${reg.cancelled_at ? `
                <div class="detail-row">
                    <span class="detail-label">Cancelled On</span>
                    <span class="detail-value">${formatDateTime(reg.cancelled_at)}</span>
                </div>
                ` : ''}
            </div>

            <div class="detail-section">
                <h6><i class="bi bi-person me-2"></i>Participant Information</h6>
                <div class="detail-row">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-value">${reg.full_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">${reg.email}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">${reg.phone}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Student ID</span>
                    <span class="detail-value">${reg.student_id}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Program</span>
                    <span class="detail-value">${reg.program}</span>
                </div>
            </div>
        `;

        // Payment section
        if (reg.payment) {
            html += `
                <div class="detail-section">
                    <h6><i class="bi bi-credit-card me-2"></i>Payment Information</h6>
                    <div class="detail-row">
                        <span class="detail-label">Amount</span>
                        <span class="detail-value"><strong>RM ${parseFloat(reg.payment.amount).toFixed(2)}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value">${reg.payment.method.charAt(0).toUpperCase() + reg.payment.method.slice(1)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Status</span>
                        <span class="detail-value">
                            <span class="status-badge status-${reg.payment.status}">${reg.payment.status.charAt(0).toUpperCase() + reg.payment.status.slice(1)}</span>
                        </span>
                    </div>
                    ${reg.payment.paid_at ? `
                    <div class="detail-row">
                        <span class="detail-label">Paid On</span>
                        <span class="detail-value">${formatDateTime(reg.payment.paid_at)}</span>
                    </div>
                    ` : ''}
                    ${reg.payment.transaction_id ? `
                    <div class="detail-row">
                        <span class="detail-label">Transaction ID</span>
                        <span class="detail-value"><code class="small">${reg.payment.transaction_id}</code></span>
                    </div>
                    ` : ''}
                </div>
            `;
        }

        // Refund section
        if (reg.refund_status && reg.refund_status !== null) {
            const refundClass = reg.refund_status === 'completed' ? 'refund-completed' :
                    reg.refund_status === 'rejected' ? 'refund-rejected' : 'refund-pending';

            html += `
                <div class="detail-section">
                    <div class="refund-status-section ${refundClass}">
                        <div class="refund-status-header">
                            <i class="bi bi-arrow-return-left"></i>
                            <div>
                                <h6 class="mb-0">Refund Status: ${formatStatus(reg.refund_status)}</h6>
                            </div>
                        </div>
                        
                        ${reg.payment && reg.payment.refund_amount ? `
                        <div class="detail-row">
                            <span class="detail-label">Refund Amount</span>
                            <span class="detail-value"><strong>RM ${parseFloat(reg.payment.refund_amount).toFixed(2)}</strong></span>
                        </div>
                        ` : ''}
                        
                        ${reg.payment && reg.payment.refund_reason ? `
                        <div class="detail-row">
                            <span class="detail-label">Refund Reason</span>
                            <span class="detail-value">${reg.payment.refund_reason}</span>
                        </div>
                        ` : ''}
                        
                        ${reg.payment && reg.payment.refund_requested_at ? `
                        <div class="detail-row">
                            <span class="detail-label">Requested On</span>
                            <span class="detail-value">${formatDateTime(reg.payment.refund_requested_at)}</span>
                        </div>
                        ` : ''}
                        
                        ${reg.payment && reg.payment.refund_processed_at ? `
                        <div class="detail-row">
                            <span class="detail-label">Processed On</span>
                            <span class="detail-value">${formatDateTime(reg.payment.refund_processed_at)}</span>
                        </div>
                        ` : ''}
                        
                        ${reg.payment && reg.payment.refund_rejection_reason ? `
                        <div class="detail-row">
                            <span class="detail-label">Rejection Reason</span>
                            <span class="detail-value text-danger">${reg.payment.refund_rejection_reason}</span>
                        </div>
                        ` : ''}
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

    function formatStatus(status) {
        return status.replace('_', ' ').split(' ').map(w =>
            w.charAt(0).toUpperCase() + w.slice(1)
        ).join(' ');
    }

    function showToast(type, message) {
        // Simple toast implementation
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
    // 定义一个定时器变量
    let debounceTimer;

    // 监听 'input' 事件 
    $('#searchInput').on('input', function () {
        // 每次输入都先清除上一次的定时器
        clearTimeout(debounceTimer);

        // 重新设置一个 500ms (0.5秒) 的定时器
        debounceTimer = setTimeout(function () {
            fetchRegistrations();
        }, 500);
    });

    // 让下拉菜单 (Dropdown) 改变时也自动刷新，不用点 Apply
    $('#statusFilter, #sortFilter').on('change', function () {
        fetchRegistrations();
    });
    
//    $('#applyFiltersBtn').on('click', fetchRegistrations);
    
    $('#applyFiltersBtn').on('click', function() {
        clearTimeout(debounceTimer);
        fetchRegistrations();
    });

    // Initial load
    fetchRegistrations();
});