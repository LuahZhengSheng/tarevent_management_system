/**
 * Payment Processing with Stripe and PayPal
 */

$(document).ready(function () {
    // Countdown Timer
    const timerElement = document.getElementById('expiryTimer');
    if (timerElement) {
        const expiresAt = new Date(timerElement.dataset.expiresAt);

        function updateTimer() {
            const now = new Date();
            const diff = expiresAt - now;

            if (diff <= 0) {
                // Time's up!
                document.getElementById('timerDisplay').innerHTML = '<span style="color: var(--error);">EXPIRED</span>';

                // Disable payment buttons
                document.querySelectorAll('.btn-pay, #paypal-button-container').forEach(el => {
                    el.style.pointerEvents = 'none';
                    el.style.opacity = '0.5';
                });

                // Show expiry message
                alert('Payment time has expired. Redirecting...');
                window.location.href = '{{ route("events.show", $event) }}';
                return;
            }

            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);

            document.querySelector('.timer-display .minutes').textContent = String(minutes).padStart(2, '0');
            document.querySelector('.timer-display .seconds').textContent = String(seconds).padStart(2, '0');

            // Warning color when < 5 minutes
            if (minutes < 5) {
                timerElement.classList.add('warning');
            }

            // Critical color when < 2 minutes
            if (minutes < 2) {
                timerElement.classList.add('critical');
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    const stripePublishableKey = $('meta[name="stripe-key"]').attr('content');
    const registrationId = $('input[name="registration_id"]').val();

    // Initialize Stripe
    let stripe = null;
    let cardElement = null;

    if (stripePublishableKey) {
        stripe = Stripe(stripePublishableKey);
        const elements = stripe.elements();

        // Create card element with custom styling
        const style = {
            base: {
                color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim(),
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                fontSize: '16px',
                '::placeholder': {
                    color: getComputedStyle(document.documentElement).getPropertyValue('--text-tertiary').trim(),
                }
            },
            invalid: {
                color: getComputedStyle(document.documentElement).getPropertyValue('--error').trim(),
                iconColor: getComputedStyle(document.documentElement).getPropertyValue('--error').trim(),
            }
        };

        cardElement = elements.create('card', {style: style});
        cardElement.mount('#card-element');

        // Handle real-time validation errors
        cardElement.on('change', function (event) {
            const displayError = $('#card-errors');
            if (event.error) {
                displayError.text(event.error.message).addClass('visible');
            } else {
                displayError.text('').removeClass('visible');
            }
        });
    }

    // Payment method switching
    $('input[name="payment_method"]').on('change', function () {
        const selectedMethod = $(this).val();

        $('.payment-section').removeClass('active');
        $(`#${selectedMethod}-payment-section`).addClass('active');

        // Update selected card styling
        $('.payment-method-card').removeClass('selected');
        $(this).closest('.payment-method-card').addClass('selected');
    });

    // Terms checkbox validation
    const termsCheckbox = $('#payment-terms');
    const payButtons = $('.btn-pay');
    const paypalOverlay = $('#paypal-overlay'); // 获取遮罩层

    // 处理遮罩层的点击事件 (模拟拦截)
    paypalOverlay.on('click', function () {
        // UI 效果：晃动并变红
        $('.payment-terms').addClass('shake-error');
        setTimeout(() => $('.payment-terms').removeClass('shake-error'), 500);

        showError('Please agree to the payment terms and refund policy.');

        $('html, body').animate({
            scrollTop: $(".payment-terms").offset().top - 200
        }, 500);
    });

    function updatePayButtonsState() {
        const isChecked = termsCheckbox.is(':checked');

        // Stripe 按钮
        payButtons.prop('disabled', !isChecked);

        // PayPal 遮罩层控制
        if (isChecked) {
            paypalOverlay.hide(); // 勾选了，隐藏遮罩，让用户能点到下面的 PayPal
        } else {
            paypalOverlay.show(); // 没勾选，显示遮罩，拦截点击
        }
    }

    termsCheckbox.on('change', updatePayButtonsState);
    updatePayButtonsState();

    // Stripe Payment Form Submission
    $('#stripe-payment-form').on('submit', async function (e) {
        e.preventDefault();

        // 检查 Checkbox (Frontend Check)
        if (!termsCheckbox.is(':checked')) {
            // UI 效果：晃动并变红
            $('.payment-terms').addClass('shake-error');
            setTimeout(() => $('.payment-terms').removeClass('shake-error'), 500);

            showError('Please agree to the payment terms and refund policy.');

            // 滚动到 Terms 区域
            $('html, body').animate({
                scrollTop: $(".payment-terms").offset().top - 200
            }, 500);

            return;
        }

        const submitBtn = $('#stripe-submit-btn');
        const cardHolderName = $('#card-holder-name').val().trim();

        if (!cardHolderName) {
            showError('Please enter the cardholder name.');
            return;
        }

        // Disable submit button and show loading
        submitBtn.addClass('loading').prop('disabled', true);
        showLoadingOverlay();

        try {
            // Create payment intent
            const intentResponse = await $.ajax({
                url: '/payments/create-intent',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    registration_id: registrationId,
                    payment_method: 'stripe',
                    terms_accepted: true
                }
            });

            if (!intentResponse.success) {
                throw new Error(intentResponse.message || 'Failed to create payment intent');
            }

            // Confirm card payment
            const {error, paymentIntent} = await stripe.confirmCardPayment(
                    intentResponse.client_secret,
                    {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: cardHolderName
                            }
                        }
                    }
            );

            if (error) {
                throw new Error(error.message);
            }

            // Payment successful, confirm on server
            const confirmResponse = await $.ajax({
                url: '/payments/confirm',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    registration_id: registrationId,
                    payment_intent_id: paymentIntent.id,
                    payment_method: 'stripe'
                }
            });

            if (confirmResponse.success) {
                // Show success message and redirect
                showSuccess('Payment successful! 🎉');
                setTimeout(() => {
                    window.location.href = confirmResponse.redirect || '/events/my';
                }, 1500);
            } else {
                throw new Error(confirmResponse.message || 'Payment confirmation failed');
            }

        } catch (error) {
            console.error('Stripe payment error:', error);

            // 如果后端返回的是 Validation Error (422)
            if (error.responseJSON && error.responseJSON.message) {
                showError(error.responseJSON.message);
            } else {
                showError(error.message || 'Payment failed. Please try again.');
            }

            submitBtn.removeClass('loading').prop('disabled', false);
            hideLoadingOverlay();
        }
    });

    // Initialize PayPal Buttons
    if (typeof paypal !== 'undefined') {
        paypal.Buttons({
            style: {
                layout: 'vertical',
                color: 'blue',
                shape: 'rect',
                label: 'pay'
            },

            // 点击按钮瞬间：检查 Checkbox
            onClick: function (data, actions) {
                const termsCheckbox = document.getElementById('payment-terms');

                // 必须检查 Checkbox 是否存在
                if (!termsCheckbox) {
                    console.error('Terms checkbox not found!');
                    return actions.reject();
                }

                if (!termsCheckbox.checked) {
                    // 1. UI 效果：晃动并变红 (确保类名和你 CSS 里的一致)
                    $('.payment-terms').addClass('shake-error');

                    // 500ms 后移除 shake 类，这样下次点还能再晃
                    setTimeout(() => $('.payment-terms').removeClass('shake-error'), 500);

                    // 2. 显示 Toast 错误 (复用你的 showError 函数)
                    showError('Please agree to the payment terms and refund policy.');

                    // 3. 滚动到 Terms 区域 (可选)
                    $('html, body').animate({
                        scrollTop: $(".payment-terms").offset().top - 200
                    }, 500);

                    // 4. 【关键】强制阻止 PayPal 弹窗
                    return actions.reject();
                }

                // 如果勾选了，允许继续
                return actions.resolve();
            },

            // Create order on PayPal
            createOrder: async function () {
                if (!termsCheckbox.is(':checked')) {
                    showError('Please accept the payment terms to continue.');
                    throw new Error('Terms not accepted');
                }

                showLoadingOverlay();

                try {
                    const response = await $.ajax({
                        url: '/payments/paypal/create-order',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            registration_id: registrationId,
                            terms_accepted: true
                        }
                    });

                    if (!response.success) {
                        throw new Error(response.message || 'Failed to create PayPal order');
                    }

                    // 使用 await 确保遮罩完全消失后再返回，或者直接同步隐藏
                    $('#payment-loading').hide(); // 直接 hide() 不要 fadeOut()，追求速度

                    return response.order_id;
                } catch (error) {
                    console.error('PayPal create order error:', error);
                    showError(error.message || 'Failed to initiate PayPal payment');
                    hideLoadingOverlay();
                    throw error;
                }
            },

            // Capture payment on approval
            onApprove: async function (data) {
                showLoadingOverlay();

                try {
                    const response = await $.ajax({
                        url: '/payments/paypal/capture-order',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            registration_id: registrationId,
                            order_id: data.orderID
                        }
                    });

                    hideLoadingOverlay();

                    if (response.success) {
                        showSuccess('Payment successful! 🎉');
                        setTimeout(() => {
                            window.location.href = response.redirect || '/events/my';
                        }, 1500);
                    } else {
                        throw new Error(response.message || 'Payment capture failed');
                    }
                } catch (error) {
                    console.error('PayPal capture error:', error);
                    showError(error.message || 'Payment processing failed');
                }
            },

            onCancel: function () {
                hideLoadingOverlay();
                showError('Payment was cancelled. You can try again when ready.');
            },

            onError: function (err) {
                console.error('PayPal error:', err);
                hideLoadingOverlay();
                showError('An error occurred with PayPal. Please try again or use a different payment method.');
            }

        }).render('#paypal-button-container');
    }

    // Helper Functions
    function showLoadingOverlay() {
        $('#payment-loading').fadeIn(200);
    }

    function hideLoadingOverlay() {
        $('#payment-loading').fadeOut(200);
    }

    function showError(message) {
        // Create toast notification
        const toast = $(`
            <div class="payment-toast error">
                <i class="bi bi-x-circle"></i>
                <span>${message}</span>
            </div>
        `);

        $('body').append(toast);

        setTimeout(() => {
            toast.addClass('show');
        }, 100);

        setTimeout(() => {
            toast.removeClass('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function showSuccess(message) {
        const toast = $(`
            <div class="payment-toast success">
                <i class="bi bi-check-circle"></i>
                <span>${message}</span>
            </div>
        `);

        $('body').append(toast);

        setTimeout(() => {
            toast.addClass('show');
        }, 100);
    }

    // Prevent accidental navigation during payment
    let paymentInProgress = false;

    $('form').on('submit', function () {
        paymentInProgress = true;
    });

    $(window).on('beforeunload', function (e) {
        if (paymentInProgress) {
            const message = 'Payment is in progress. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    });

    // Auto-focus on cardholder name
    setTimeout(() => {
        $('#card-holder-name').focus();
    }, 500);

    console.log('Payment system initialized');
});

// Toast notification styles (inject into head)
const toastStyles = `
<style>
.payment-toast {
    position: fixed;
    top: 100px;
    right: 2rem;
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 10000;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
    max-width: 400px;
}

.payment-toast.show {
    opacity: 1;
    transform: translateX(0);
}

.payment-toast.error {
    border-left: 4px solid var(--error);
}

.payment-toast.error i {
    color: var(--error);
    font-size: 1.5rem;
}

.payment-toast.success {
    border-left: 4px solid var(--success);
}

.payment-toast.success i {
    color: var(--success);
    font-size: 1.5rem;
}

.payment-toast span {
    color: var(--text-primary);
    font-weight: 500;
    line-height: 1.4;
}

[data-theme="dark"] .payment-toast {
    background: var(--bg-secondary);
}

@media (max-width: 768px) {
    .payment-toast {
        right: 1rem;
        left: 1rem;
        max-width: none;
    }
}
</style>
`;

$('head').append(toastStyles);