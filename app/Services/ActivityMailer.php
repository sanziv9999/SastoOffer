<?php

namespace App\Services;

use App\Jobs\SendActivityMailJob;
use App\Mail\ActivityMail;
use App\Models\MailDispatch;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ActivityMailer
{
    public function sendForUser(User $user, string $mailType, array $payload, string $uniqueKey): bool
    {
        $email = trim((string) $user->email);
        if ($email === '') {
            return false;
        }

        $message = $this->buildMessage($user, $mailType, $payload);
        if ($message === null) {
            return false;
        }

        return $this->sendOnce(
            recipientEmail: $email,
            mailType: $mailType,
            uniqueKey: $uniqueKey,
            subject: $message['subject'],
            context: $payload,
            message: $message,
        );
    }

    public function sendRegistrationWelcome(User $user): bool
    {
        return $this->sendForUser(
            user: $user,
            mailType: 'user.registered',
            payload: [],
            uniqueKey: 'user:' . $user->id,
        );
    }

    public function sendVendorStatusChanged(VendorProfile $vendorProfile, string $status): bool
    {
        $user = $vendorProfile->user;
        if (! $user) {
            return false;
        }

        return $this->sendForUser(
            user: $user,
            mailType: 'vendor.status.' . $status,
            payload: [
                'business_name' => $vendorProfile->business_name,
                'status' => $status,
                'slug' => $vendorProfile->slug,
            ],
            uniqueKey: 'vendor:' . $vendorProfile->id . ':status:' . $status,
        );
    }

    public function sendOrderPlacedCustomer(Order $order): bool
    {
        $order->loadMissing(['user', 'vendor']);
        if (! $order->user) {
            return false;
        }

        return $this->sendForUser(
            user: $order->user,
            mailType: 'order.placed.customer',
            payload: [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'total' => (float) $order->grand_total,
                'vendor_name' => $order->vendor?->business_name,
            ],
            // Deduplicate by shared checkout order number for customer notifications.
            uniqueKey: 'order-number:' . $order->order_number . ':customer:' . $order->user_id,
        );
    }

    public function sendOrderPlacedVendor(Order $order): bool
    {
        $order->loadMissing('vendor.user');
        $vendorUser = $order->vendor?->user;
        if (! $vendorUser) {
            return false;
        }

        return $this->sendForUser(
            user: $vendorUser,
            mailType: 'order.placed.vendor',
            payload: [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'total' => (float) $order->grand_total,
                'customer_name' => $order->user?->name,
            ],
            uniqueKey: 'order:' . $order->id . ':vendor',
        );
    }

    public function sendOrderStatusChangedCustomer(Order $order, string $status): bool
    {
        $order->loadMissing(['user', 'vendor']);
        if (! $order->user) {
            return false;
        }

        return $this->sendForUser(
            user: $order->user,
            mailType: 'order.status_changed.customer',
            payload: [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'status' => $status,
                'vendor_name' => $order->vendor?->business_name,
            ],
            uniqueKey: 'order:' . $order->id . ':status:' . $status . ':customer',
        );
    }

    public function sendOrderStatusChangedVendor(Order $order, string $status): bool
    {
        $order->loadMissing('vendor.user');
        $vendorUser = $order->vendor?->user;
        if (! $vendorUser) {
            return false;
        }

        return $this->sendForUser(
            user: $vendorUser,
            mailType: 'order.status_changed.vendor',
            payload: [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'status' => $status,
            ],
            uniqueKey: 'order:' . $order->id . ':status:' . $status . ':vendor',
        );
    }

    protected function sendOnce(
        string $recipientEmail,
        string $mailType,
        string $uniqueKey,
        string $subject,
        array $context,
        array $message,
    ): bool {
        $contextHash = hash('sha256', json_encode($context));

        $dispatch = MailDispatch::firstOrCreate(
            [
                'recipient_email' => $recipientEmail,
                'mail_type' => $mailType,
                'unique_key' => $uniqueKey,
            ],
            [
                'subject' => $subject,
                'context_hash' => $contextHash,
            ],
        );

        if (! $dispatch->wasRecentlyCreated) {
            return false;
        }

        try {
            if (config('queue.default') === 'sync') {
                Mail::to($recipientEmail)->send(new ActivityMail(
                    subjectLine: $message['subject'],
                    title: $message['title'],
                    lines: $message['lines'],
                    actionText: $message['actionText'],
                    actionUrl: $message['actionUrl'],
                    metaLabel: $message['metaLabel'],
                    metaValue: $message['metaValue'],
                ));

                $dispatch->update(['sent_at' => now()]);
            } else {
                SendActivityMailJob::dispatch(
                    mailDispatchId: $dispatch->id,
                    recipientEmail: $recipientEmail,
                    subjectLine: $message['subject'],
                    title: $message['title'],
                    lines: $message['lines'],
                    actionText: $message['actionText'],
                    actionUrl: $message['actionUrl'],
                    metaLabel: $message['metaLabel'],
                    metaValue: $message['metaValue'],
                );
            }
            return true;
        } catch (\Throwable $e) {
            $dispatch->delete();
            Log::warning('Failed to send activity mail', [
                'mail_type' => $mailType,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function buildMessage(User $user, string $mailType, array $payload): ?array
    {
        $appName = config('app.name', 'Sasto Offer');

        return match ($mailType) {
            'user.registered' => [
                'subject' => "Welcome to {$appName}",
                'title' => 'Welcome aboard!',
                'lines' => [
                    "Hi {$user->name}, your account has been created successfully.",
                    'You can now explore deals, place orders, and manage your profile from your dashboard.',
                ],
                'actionText' => 'Open Dashboard',
                'actionUrl' => $this->dashboardUrlFor($user),
                'metaLabel' => 'Account',
                'metaValue' => $user->email,
            ],
            'vendor.status.verified' => [
                'subject' => 'Vendor Verification Approved',
                'title' => 'Your vendor profile is verified',
                'lines' => [
                    'Great news! Your vendor account has been approved.',
                    'You can now publish and manage deals from your vendor dashboard.',
                ],
                'actionText' => 'Open Vendor Dashboard',
                'actionUrl' => url('/vendor/dashboard'),
                'metaLabel' => 'Business',
                'metaValue' => (string) ($payload['business_name'] ?? ''),
            ],
            'vendor.status.rejected' => [
                'subject' => 'Vendor Verification Update',
                'title' => 'Your vendor profile needs changes',
                'lines' => [
                    'Your vendor profile was reviewed and is currently marked as rejected.',
                    'Please update the required details and submit again for review.',
                ],
                'actionText' => 'Update Vendor Profile',
                'actionUrl' => url('/vendor/settings'),
                'metaLabel' => 'Business',
                'metaValue' => (string) ($payload['business_name'] ?? ''),
            ],
            'vendor.status.suspended' => [
                'subject' => 'Vendor Account Suspended',
                'title' => 'Your vendor profile is suspended',
                'lines' => [
                    'Your vendor profile is currently suspended.',
                    'Please contact support if you believe this needs clarification.',
                ],
                'actionText' => 'View Profile Settings',
                'actionUrl' => url('/vendor/settings'),
                'metaLabel' => 'Business',
                'metaValue' => (string) ($payload['business_name'] ?? ''),
            ],
            'vendor.status.pending' => [
                'subject' => 'Vendor Verification Pending',
                'title' => 'Your vendor profile is under review',
                'lines' => [
                    'Your vendor profile has been marked as pending review.',
                    'We will notify you again once verification is complete.',
                ],
                'actionText' => 'Open Vendor Settings',
                'actionUrl' => url('/vendor/settings'),
                'metaLabel' => 'Business',
                'metaValue' => (string) ($payload['business_name'] ?? ''),
            ],
            'order.placed.customer' => [
                'subject' => 'Order Confirmed: ' . ($payload['order_number'] ?? ''),
                'title' => 'Your order has been placed',
                'lines' => [
                    'Thank you for your purchase. Your order was placed successfully.',
                    'You can track your order status from My Purchases.',
                ],
                'actionText' => 'View My Purchases',
                'actionUrl' => url('/dashboard/purchases'),
                'metaLabel' => 'Order',
                'metaValue' => (string) ($payload['order_number'] ?? ''),
            ],
            'order.placed.vendor' => [
                'subject' => 'New Order Received: ' . ($payload['order_number'] ?? ''),
                'title' => 'You have a new order',
                'lines' => [
                    'A customer placed a new order for your deal.',
                    'Please review and process the order from your vendor orders page.',
                ],
                'actionText' => 'Open Vendor Orders',
                'actionUrl' => url('/vendor/orders'),
                'metaLabel' => 'Order',
                'metaValue' => (string) ($payload['order_number'] ?? ''),
            ],
            'order.status_changed.customer' => [
                'subject' => 'Order Status Updated: ' . ($payload['order_number'] ?? ''),
                'title' => 'Your order status has changed',
                'lines' => [
                    'Your order status is now: ' . $this->formatStatus((string) ($payload['status'] ?? 'updated')) . '.',
                    'You can check details and tracking from My Purchases.',
                ],
                'actionText' => 'View My Purchases',
                'actionUrl' => url('/dashboard/purchases'),
                'metaLabel' => 'Order',
                'metaValue' => (string) ($payload['order_number'] ?? ''),
            ],
            'order.status_changed.vendor' => [
                'subject' => 'Order Status Saved: ' . ($payload['order_number'] ?? ''),
                'title' => 'Order status update confirmed',
                'lines' => [
                    'The order status has been updated to: ' . $this->formatStatus((string) ($payload['status'] ?? 'updated')) . '.',
                    'You can continue managing this order from Vendor Orders.',
                ],
                'actionText' => 'Open Vendor Orders',
                'actionUrl' => url('/vendor/orders'),
                'metaLabel' => 'Order',
                'metaValue' => (string) ($payload['order_number'] ?? ''),
            ],
            default => null,
        };
    }

    protected function dashboardUrlFor(User $user): string
    {
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return url('/admin');
        }

        if ($user->hasRole('vendor')) {
            return url('/vendor/dashboard');
        }

        return url('/dashboard');
    }

    protected function formatStatus(string $status): string
    {
        return ucfirst(str_replace('_', ' ', $status));
    }
}
