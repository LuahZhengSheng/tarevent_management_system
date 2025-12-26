<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\EventRegistration;
use App\Support\PdfHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller {

    /**
     * 统一的授权检查方法
     */
    private function authorizePayment(EventRegistration $registration) {
        // 1. 检查是否是自己的注册
        if ($registration->user_id !== auth()->id()) {
            return [
                'authorized' => false,
                'message' => 'Unauthorized access to this registration.',
                'code' => 403
            ];
        }

        // 2. 检查注册状态是否需要支付
        if ($registration->status !== 'pending_payment') {
            return [
                'authorized' => false,
                'message' => 'This registration does not require payment.',
                'code' => 422
            ];
        }

        // 3. 检查是否已过期
        if ($registration->is_expired) {
            return [
                'authorized' => false,
                'message' => 'Payment time has expired. Please register again.',
                'code' => 422,
                'expired' => true, // 标记为过期
            ];
        }

        // 4. 检查活动是否是付费活动
        if (!$registration->event || !$registration->event->is_paid) {
            return [
                'authorized' => false,
                'message' => 'This event does not require payment.',
                'code' => 422
            ];
        }

        // 5. 检查活动是否已取消
        if ($registration->event->status === 'cancelled') {
            return [
                'authorized' => false,
                'message' => 'This event has been cancelled.',
                'code' => 422
            ];
        }

        // 6. 检查是否已经有成功的支付记录
        $existingPayment = Payment::where('event_registration_id', $registration->id)
                ->where('status', 'success')
                ->first();

        if ($existingPayment) {
            return [
                'authorized' => false,
                'message' => 'Payment has already been completed for this registration.',
                'code' => 422
            ];
        }

        return ['authorized' => true];
    }

    /**
     * Show payment page
     */
    public function payment(EventRegistration $registration) {
        // 统一授权检查
        $authCheck = $this->authorizePayment($registration);

        if (!$authCheck['authorized']) {
            // 如果是过期，重定向回活动详情页
            if (isset($authCheck['expired']) && $authCheck['expired']) {
                return redirect()
                                ->route('events.show', $registration->event)
                                ->with('error', 'Your registration has expired. Please register again if spots are still available.');
            }

            if ($authCheck['code'] === 403) {
                abort(403, $authCheck['message']);
            }

            return redirect()
                            ->route('events.my')
                            ->with('error', $authCheck['message']);
        }

        $event = $registration->event;

        return view('events.payment', compact('event', 'registration'));
    }

    /**
     * Create Stripe PaymentIntent（内嵌表单用）
     */
    public function createIntent(Request $request) {
        $request->validate([
            'registration_id' => 'required|exists:event_registrations,id',
            'payment_method' => 'required|in:stripe',
            'terms_accepted' => 'required|accepted',
                ], [
            'terms_accepted.required' => 'You must agree to the terms and conditions.',
            'terms_accepted.accepted' => 'You must agree to the terms and conditions.',
        ]);

        try {
            $registration = EventRegistration::with('event')->findOrFail($request->registration_id);

            // 授权检查
            $authCheck = $this->authorizePayment($registration);
            if (!$authCheck['authorized']) {
                return response()->json([
                            'success' => false,
                            'message' => $authCheck['message'],
                                ], $authCheck['code']);
            }

            // 过期检查：保持和之前一致的 30 分钟窗口规则
            $remainingSeconds = now()->diffInSeconds($registration->expires_at, false);
            if ($remainingSeconds <= 0) {
                return response()->json([
                            'success' => false,
                            'message' => 'Payment time has expired. Please register again.',
                                ], 422);
            }

            // 复用 pending payment (if any)
            $existingPayment = Payment::where('event_registration_id', $registration->id)
                    ->where('method', 'stripe')
                    ->where('status', 'pending') // 只复用 pending，不复用 failed
                    ->latest('id')
                    ->first();

            // 初始化 Stripe
            Stripe::setApiKey(config('services.stripe.secret'));
            $amount = $registration->event->fee_amount;

            // 创建 PaymentIntent（metadata 用于 Webhook 反查）
            $paymentIntent = PaymentIntent::create([
                        'amount' => $amount * 100,
                        'currency' => 'myr',
                        'description' => "Event: {$registration->event->title}",
                        'metadata' => [
                            'registration_id' => $registration->id,
                            'registration_number' => $registration->registration_number,
                            'event_id' => $registration->event_id,
                            'event_title' => $registration->event->title,
                            'user_id' => $registration->user_id,
                            'user_name' => $registration->full_name,
                        ],
                        'receipt_email' => $registration->email,
            ]);

            if ($existingPayment) {
                // 复用已有 pending 记录（更新成最新的 PaymentIntent）
                $existingPayment->update([
                    'transaction_id' => $paymentIntent->id,
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $amount,
                    'status' => 'pending',
                    'payer_email' => $registration->email,
                    'payer_name' => $registration->full_name,
                    'metadata' => [
                        'client_secret' => $paymentIntent->client_secret,
                        'created_via' => 'web',
                    ],
                    'error_message' => null, // 清空旧错误
                ]);

                $payment = $existingPayment;
            } else {
                // 第一次创建
                $payment = Payment::create([
                            'event_id' => $registration->event_id,
                            'user_id' => $registration->user_id,
                            'event_registration_id' => $registration->id,
                            'amount' => $amount,
                            'method' => 'stripe',
                            'transaction_id' => $paymentIntent->id,
                            'payment_intent_id' => $paymentIntent->id,
                            'payer_email' => $registration->email,
                            'payer_name' => $registration->full_name,
                            'status' => 'pending',
                            'metadata' => [
                                'client_secret' => $paymentIntent->client_secret,
                                'created_via' => 'web',
                            ],
                ]);
            }

            $registration->update([
                'payment_gateway' => 'stripe',
                'gateway_session_id' => $paymentIntent->id,
            ]);

            Log::info('Stripe payment intent created', [
                'payment_id' => $payment->id,
                'intent_id' => $paymentIntent->id,
                'registration_id' => $registration->id,
                'amount' => $amount,
            ]);

            return response()->json([
                        'success' => true,
                        'client_secret' => $paymentIntent->client_secret,
                        'payment_id' => $payment->id,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe card error', [
                'error' => $e->getMessage(),
                'registration_id' => $request->registration_id,
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Card error: ' . $e->getMessage(),
                            ], 422);
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'registration_id' => $request->registration_id,
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to initialize payment. Please try again.',
                            ], 500);
        }
    }

    /**
     * Confirm Stripe Payment（可作为 Webhook 失败时的兜底，可保留）
     */
    public function confirmPayment(Request $request) {
        $request->validate([
            'registration_id' => 'required|exists:event_registrations,id',
            'payment_intent_id' => 'required|string',
            'payment_method' => 'required|in:stripe',
        ]);

        try {
            DB::beginTransaction();

            $registration = EventRegistration::with('event')->findOrFail($request->registration_id);

            // 基本授权检查
            if ($registration->user_id !== auth()->id()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized access.',
                                ], 403);
            }

            // 找到 Payment 记录
            $payment = Payment::where('event_registration_id', $registration->id)
                    ->where('payment_intent_id', $request->payment_intent_id)
                    ->where('method', 'stripe')
                    ->firstOrFail();

            if ($payment->user_id !== auth()->id()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized access to this payment.',
                                ], 403);
            }

            // 如果已经是 success，说明 Webhook 已经处理完，直接让前端跳 My Events/Receipt
            if ($payment->status === 'success') {
                DB::commit();

                return response()->json([
                            'success' => true,
                            'message' => 'Payment already confirmed.',
                            'redirect' => route('events.my'),
                ]);
            }

            // 再向 Stripe 验证一次（兜底），但不在这里改 status
            Stripe::setApiKey(config('services.stripe.secret'));
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                // 这里只是记录一下「网关说已成功」，真正写 success 交给 Webhook
                $metadata = $payment->metadata ?? [];
                $metadata['stripe_status'] = $paymentIntent->status;
                $metadata['stripe_payment_method_id'] = $paymentIntent->payment_method;

                // 可选：顺便把卡信息塞进 metadata，方便排查，但不改 status
                if ($paymentIntent->payment_method) {
                    $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentIntent->payment_method);
                    if ($paymentMethod && isset($paymentMethod->card)) {
                        $metadata['card_brand'] = $paymentMethod->card->brand;
                        $metadata['card_last4'] = $paymentMethod->card->last4;
                        $metadata['card_exp_month'] = $paymentMethod->card->exp_month;
                        $metadata['card_exp_year'] = $paymentMethod->card->exp_year;
                    }
                }

                // 更新 metadata
                $payment->updateQuietly([
                    'metadata' => $metadata,
                ]);

                DB::commit();

                Log::info('DEBUG: Before Metadata Update', ['reg_status' => $registration->status]);

                $payment->update(['metadata' => $metadata]); // 执行你认为只更新 Payment 的代码

                $registration->refresh(); // 刷新一下
                Log::info('DEBUG: After Metadata Update', ['reg_status' => $registration->status]);

                Log::info('Stripe payment confirmed via API (waiting for webhook)', [
                    'payment_id' => $payment->id,
                    'registration_id' => $registration->id,
                    'amount' => $payment->amount,
                ]);

                // 前端只得到「处理中」，真正的 success 等 Webhook 改 DB
                return response()->json([
                            'success' => true,
                            'message' => 'Payment processing. Please wait for confirmation.',
                            'redirect' => route('registrations.receipt', ['registration' => $registration->id]),
                ]);
            } else {
                // 不是 succeeded，当作失败走 catch
                throw new \Exception("Payment status is {$paymentIntent->status}, not succeeded");
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Stripe payment confirmation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'registration_id' => $request->registration_id,
            ]);

            if (isset($payment)) {
                // 这里只处理「明确失败」的情况
                $payment->markAsFailed($e->getMessage());
            }

            return response()->json([
                        'success' => false,
                        'message' => 'Payment verification failed. Please contact support if amount was charged.',
                            ], 500);
        }
    }

//    /**
//     * Create Stripe Checkout Session
//     */
//    public function createStripeSession(Request $request) {
//        $request->validate([
//            'registration_id' => 'required|exists:event_registrations,id',
//            'terms_accepted' => 'required|accepted',
//        ]);
//
//        try {
//            $registration = EventRegistration::with('event')->findOrFail($request->registration_id);
//
//            // 统一授权检查
//            $authCheck = $this->authorizePayment($registration);
//
//            if (!$authCheck['authorized']) {
//                return response()->json([
//                            'success' => false,
//                            'message' => $authCheck['message'],
//                                ], $authCheck['code']);
//            }
//
//            // 计算剩余秒数
//            $remainingSeconds = now()->diffInSeconds($registration->expires_at, false);
//
//            if ($remainingSeconds <= 0) {
//                return response()->json([
//                            'success' => false,
//                            'message' => 'Payment time has expired.',
//                                ], 422);
//            }
//
//            // Initialize Stripe
//            Stripe::setApiKey(config('services.stripe.secret'));
//            $amount = $registration->event->fee_amount;
//
//            // 创建 Stripe Checkout Session（设置过期时间）
//            $session = StripeSession::create([
//                        'payment_method_types' => ['card'],
//                        'line_items' => [[
//                        'price_data' => [
//                            'currency' => 'myr',
//                            'product_data' => [
//                                'name' => $registration->event->title,
//                                'description' => "Registration for event: {$registration->event->title}",
//                            ],
//                            'unit_amount' => $amount * 100, // cents
//                        ],
//                        'quantity' => 1,
//                            ]],
//                        'mode' => 'payment',
//                        'success_url' => route('payments.stripe.success', ['registration_id' => $registration->id]),
//                        'cancel_url' => route('registrations.payment', ['registration' => $registration->id]),
//                        'customer_email' => $registration->email,
//                        'client_reference_id' => $registration->id,
//                        'metadata' => [
//                            'registration_id' => $registration->id,
//                            'registration_number' => $registration->registration_number,
//                            'event_id' => $registration->event_id,
//                            'user_id' => $registration->user_id,
//                        ],
//                        // 设置 Session 过期时间（传入 Unix timestamp）
////                        'expires_at' => $registration->expires_at->timestamp,
//            ]);
//
//            // ✅ 保存 gateway_session_id
//            $registration->update([
//                'payment_gateway' => 'stripe',
//                'gateway_session_id' => $session->id,
//            ]);
//
//            // Create pending payment record
//            Payment::create([
//                'event_id' => $registration->event_id,
//                'user_id' => $registration->user_id,
//                'event_registration_id' => $registration->id,
//                'amount' => $amount,
//                'method' => 'stripe',
//                'transaction_id' => $session->id,
//                'payment_intent_id' => null, // Will be filled by webhook
//                'payer_email' => $registration->email,
//                'payer_name' => $registration->full_name,
//                'status' => 'pending',
//                'metadata' => [
//                    'session_id' => $session->id,
//                    'created_via' => 'web',
//                ],
//            ]);
//
//            Log::info('Stripe session created', [
//                'session_id' => $session->id,
//                'registration_id' => $registration->id,
//                'expires_at' => $registration->expires_at,
//            ]);
//
//            return response()->json([
//                        'success' => true,
//                        'session_id' => $session->id,
//                        'public_key' => config('services.stripe.key'),
//            ]);
//        } catch (\Exception $e) {
//            Log::error('Stripe session creation failed', [
//                'error' => $e->getMessage(),
//                'registration_id' => $request->registration_id,
//            ]);
//
//            return response()->json([
//                        'success' => false,
//                        'message' => 'Failed to initialize payment. Please try again.',
//                            ], 500);
//        }
//    }
//
//    /**
//     * Stripe Success Callback
//     */
//    public function stripeSuccess(Request $request) {
//        // Stripe 会重定向到这里，但实际确认由 Webhook 处理
//        $registrationId = $request->query('registration_id');
//
//        if (!$registrationId) {
//            return redirect()->route('events.my')
//                            ->with('info', 'Payment processing. Please wait for confirmation.');
//        }
//
//        $registration = EventRegistration::find($registrationId);
//
//        if (!$registration) {
//            return redirect()->route('events.my')
//                            ->with('error', 'Registration not found.');
//        }
//
//        // 如果已经是 confirmed，说明 webhook 已处理
//        if ($registration->status === 'confirmed') {
//            return redirect()->route('events.my')
//                            ->with('success', 'Payment successful! 🎉');
//        }
//
//        // 否则，显示等待确认页面
//        return redirect()->route('events.my')
//                        ->with('info', 'Payment is being processed. You will receive a confirmation email shortly.');
//    }

    /**
     * Create PayPal Order
     */
    public function createPayPalOrder(Request $request) {
        $request->validate([
            'registration_id' => 'required|exists:event_registrations,id',
            'terms_accepted' => 'required|accepted',
        ]);

        try {
            $registration = EventRegistration::with('event')->findOrFail($request->registration_id);

            // 统一授权检查
            $authCheck = $this->authorizePayment($registration);

            if (!$authCheck['authorized']) {
                return response()->json([
                            'success' => false,
                            'message' => $authCheck['message'],
                                ], $authCheck['code']);
            }

            // 过期检查：保持和之前一致的 30 分钟窗口规则
            $remainingSeconds = now()->diffInSeconds($registration->expires_at, false);
            if ($remainingSeconds <= 0) {
                return response()->json([
                            'success' => false,
                            'message' => 'Payment time has expired. Please register again.',
                                ], 422);
            }

            $existingPayment = Payment::where('event_registration_id', $registration->id)
                    ->where('method', 'paypal')
                    ->where('status', 'pending') // 只复用 pending
                    ->latest('id')
                    ->first();

            $amount = $registration->event->fee_amount;

            // Call PayPal API to create order
            $paypalOrder = $this->createPayPalOrderViaAPI($registration, $amount);

            if (!isset($paypalOrder['id'])) {
                throw new \Exception('Invalid PayPal response: ' . json_encode($paypalOrder));
            }

            // 保存 gateway info
            $registration->update([
                'payment_gateway' => 'paypal',
                'gateway_session_id' => $paypalOrder['id'],
            ]);

            if ($existingPayment) {
                // 复用已有 pending 记录
                $existingPayment->update([
                    'transaction_id' => $paypalOrder['id'],
                    'amount' => $amount,
                    'status' => 'pending',
                    'payer_email' => $registration->email,
                    'payer_name' => $registration->full_name,
                    'metadata' => [
                        'paypal_order_id' => $paypalOrder['id'],
                        'created_via' => 'web',
                    ],
                    'error_message' => null,
                ]);

                $payment = $existingPayment;
            } else {
                // Create pending payment record
                $payment = Payment::create([
                            'event_id' => $registration->event_id,
                            'user_id' => $registration->user_id,
                            'event_registration_id' => $registration->id,
                            'amount' => $amount,
                            'method' => 'paypal',
                            'transaction_id' => $paypalOrder['id'],
                            'payer_email' => $registration->email,
                            'payer_name' => $registration->full_name,
                            'status' => 'pending',
                            'metadata' => [
                                'paypal_order_id' => $paypalOrder['id'],
                                'created_via' => 'web',
                            ],
                ]);
            }

            Log::info('PayPal order created', [
                'order_id' => $paypalOrder['id'],
                'registration_id' => $registration->id,
            ]);

            return response()->json([
                        'success' => true,
                        'order_id' => $paypalOrder['id'],
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', [
                'error' => $e->getMessage(),
                'registration_id' => $request->registration_id,
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to create PayPal order. Please try again.',
                            ], 500);
        }
    }

    /**
     * Capture PayPal Order
     */
    public function capturePayPalOrder(Request $request) {
        $request->validate([
            'registration_id' => 'required|exists:event_registrations,id',
            'order_id' => 'required|string',
        ]);

        try {
            // 注意：这里不再需要 DB::beginTransaction，因为我们不写数据库
            // 或者保留 transaction 仅仅为了读取时的锁，也没问题。

            $registration = EventRegistration::with('event')->findOrFail($request->registration_id);

            // 在 Capture 前检查是否过期
            if ($registration->is_expired) {
                return response()->json([
                            'success' => false,
                            'message' => 'Payment time has expired. Cannot process payment.',
                                ], 422);
            }

            // 基本授权检查
            if ($registration->user_id !== auth()->id()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized access.',
                                ], 403);
            }

            // Find payment record
            $payment = Payment::where('event_registration_id', $registration->id)
                    ->where('transaction_id', $request->order_id)
                    ->where('method', 'paypal')
                    ->firstOrFail();

            // 如果已经是成功状态，直接返回
            if ($payment->status === 'success') {
                return response()->json([
                            'success' => true,
                            'message' => 'Payment already confirmed.',
                            'redirect' => route('events.my'),
                ]);
            }

            // Capture PayPal order via API
            $captureResult = $this->capturePayPalOrderViaAPI($request->order_id);

            if ($captureResult['status'] === 'COMPLETED') {
                // 真正的状态更新将由 PayPal 发来的 Webhook (PAYMENT.CAPTURE.COMPLETED) 触发。

                Log::info('PayPal captured successfully via API, waiting for webhook confirmation', [
                    'payment_id' => $payment->id,
                    'registration_id' => $registration->id,
                    'paypal_order_id' => $request->order_id
                ]);

                return response()->json([
                            'success' => true,
                            'message' => 'Payment processing. You will receive a confirmation shortly.',
                            'redirect' => route('registrations.receipt', ['registration' => $registration->id]), // 跳转回我的票务页，那里会显示 pending
                ]);
            } else {
                throw new \Exception("PayPal order status is {$captureResult['status']}, not COMPLETED");
            }
        } catch (\Exception $e) {
            Log::error('PayPal payment capture failed', [
                'error' => $e->getMessage(),
                'registration_id' => $request->registration_id,
            ]);

            if (isset($payment)) {
                $payment->markAsFailed($e->getMessage());
            }

            return response()->json([
                        'success' => false,
                        'message' => 'Payment processing failed. Please contact support.',
                            ], 500);
        }
    }

    /**
     * Helper: Create PayPal Order via API
     */
    private function createPayPalOrderViaAPI($registration, $amount) {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        $mode = config('services.paypal.mode', 'sandbox');

        $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // Get access token
        $ch = curl_init("{$baseUrl}/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$clientId}:{$secret}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $response = curl_exec($ch);
        $tokenData = json_decode($response, true);
        curl_close($ch);

        $accessToken = $tokenData['access_token'];

        // Create order
        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $registration->registration_number,
                    'description' => "Event: {$registration->event->title}",
                    'amount' => [
                        'currency_code' => 'MYR',
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                ],
            ],
            'application_context' => [
                'brand_name' => 'TAREvent',
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING', // 这行会隐藏收货地址
            ],
        ];

        $ch = curl_init("{$baseUrl}/v2/checkout/orders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));

        $response = curl_exec($ch);
        $orderResult = json_decode($response, true);
        curl_close($ch);

        return $orderResult;
    }

    /**
     * Helper: Capture PayPal Order via API
     */
    private function capturePayPalOrderViaAPI($orderId) {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        $mode = config('services.paypal.mode', 'sandbox');

        $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        // Get access token
        $ch = curl_init("{$baseUrl}/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$clientId}:{$secret}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $response = curl_exec($ch);
        $tokenData = json_decode($response, true);
        curl_close($ch);

        $accessToken = $tokenData['access_token'];

        // Capture order
        $ch = curl_init("{$baseUrl}/v2/checkout/orders/{$orderId}/capture");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ]);
        curl_setopt($ch, CURLOPT_POST, true);

        $response = curl_exec($ch);
        $captureResult = json_decode($response, true);
        curl_close($ch);

        return $captureResult;
    }

    /**
     * Check payment status (for polling after payment)
     */
    public function checkStatus(Request $request, EventRegistration $registration) {
        // Authorization
        if ($registration->user_id !== auth()->id()) {
            return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access.',
                            ], 403);
        }

        $payment = $registration->payment;

        if (!$payment) {
            return response()->json([
                        'success' => false,
                        'status' => 'no_payment',
                        'message' => 'No payment found.',
            ]);
        }

        // Return current status
        return response()->json([
                    'success' => true,
                    'status' => $payment->status,
                    'registration_status' => $registration->status,
                    'payment_id' => $payment->id,
        ]);
    }

    /**
     * Show payment receipt (success page with polling)
     */
    public function receipt(EventRegistration $registration) {
        // Authorization
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        $payment = $registration->payment;

        if (!$payment) {
            return redirect()
                            ->route('events.my')
                            ->with('error', 'No payment found for this registration.');
        }

        // If payment is still pending, show pending page with polling
        if ($payment->status === 'pending') {
            return view('events.payment-pending', compact('registration', 'payment'));
        }

        // If payment failed, redirect to my events
        if ($payment->status === 'failed') {
            return redirect()
                            ->route('events.my')
                            ->with('error', 'Payment failed. Please try again or contact support.');
        }

        // Show success receipt
        return view('events.payment-receipt', compact('registration', 'payment'));
    }
    
    /**
     * Download payment receipt
     */
    public function downloadReceipt(Payment $payment)
    {
        // Authorization
        if ($payment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        try {
            return PdfHelper::generateReceipt($payment, true);
        } catch (\Exception $e) {
            Log::error('Receipt download failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to generate receipt. Please try again.');
        }
    }

    /**
     * Show payment history page
     */
    public function history() {
        return view('events.payment-history'); 
    }

    /**
     * Fetch payment history via AJAX
     */
    public function fetchHistory(Request $request) {
        try {
            $query = Payment::where('user_id', auth()->id())
                    ->with(['event', 'registration']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                            ->orWhereHas('event', function ($eq) use ($search) {
                                $eq->where('title', 'like', "%{$search}%");
                            });
                });
            }

            // Filter by type (payment or refund)
            if ($request->filled('type')) {
                if ($request->type === 'refund') {
                    $query->where('refund_status', 'completed');
                } elseif ($request->type === 'payment') {
                    $query->whereNull('refund_status');
                }
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by method
            if ($request->filled('method')) {
                $query->where('method', $request->method);
            }

            // Sort
            $sort = $request->input('sort', 'recent');
            if ($sort === 'recent') {
                $query->orderBy('created_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } elseif ($sort === 'amount_high') {
                $query->orderBy('amount', 'desc');
            } elseif ($sort === 'amount_low') {
                $query->orderBy('amount', 'asc');
            }

            $payments = $query->get();

            // Calculate statistics
            $statistics = [
                'success_count' => Payment::where('user_id', auth()->id())
                        ->where('status', 'success')
                        ->whereNull('refund_status')
                        ->count(),
                'pending_count' => Payment::where('user_id', auth()->id())
                        ->where('status', 'pending')
                        ->count(),
                'refund_total' => Payment::where('user_id', auth()->id())
                        ->where('refund_status', 'completed')
                        ->sum('refund_amount'),
                'total_spent' => Payment::where('user_id', auth()->id())
                        ->where('status', 'success')
                        ->whereNull('refund_status')
                        ->sum('amount'),
            ];

            $formattedPayments = $payments->map(function ($payment) {
                $isRefund = $payment->refund_status === 'completed';

                return [
            'id' => $payment->id,
            'type' => $isRefund ? 'refund' : 'payment',
            'amount' => $isRefund ? $payment->refund_amount : $payment->amount,
            'method' => $payment->method,
            'status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
            'created_at' => $payment->created_at->toISOString(),
            'paid_at' => $payment->paid_at ? $payment->paid_at->toISOString() : null,
            'event_title' => $payment->event ? $payment->event->title : 'N/A',
            'event_date' => $payment->event ? $payment->event->start_time->toISOString() : null,
            'registration_number' => $payment->registration ? $payment->registration->registration_number : 'N/A',
            'payer_name' => $payment->payer_name,
            'payer_email' => $payment->payer_email,
            'refund_status' => $payment->refund_status,
            'refund_reason' => $payment->refund_reason,
            'refund_requested_at' => $payment->refund_requested_at ? $payment->refund_requested_at->toISOString() : null,
            'refund_processed_at' => $payment->refund_processed_at ? $payment->refund_processed_at->toISOString() : null,
            'refund_rejection_reason' => $payment->refund_rejection_reason,
            'card_info' => $payment->metadata['card_brand'] ?? null ? [
        'brand' => $payment->metadata['card_brand'] ?? null,
        'last4' => $payment->metadata['card_last4'] ?? null,
            ] : null,
                ];
            });

            return response()->json([
                        'success' => true,
                        'payments' => $formattedPayments,
                        'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch payment history error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch payment history.',
                            ], 500);
        }
    }
}
