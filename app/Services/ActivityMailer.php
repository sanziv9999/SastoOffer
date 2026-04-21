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

        $message = array_merge([
            'orderNumber' => null,
            'partnerLabel' => null,
            'partnerName' => null,
            'orderTotalFormatted' => null,
            'lineItems' => [],
            'statusLabel' => null,
        ], $message);

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
        $order->loadMissing(['user', 'vendor', 'items']);
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
                'line_items' => $this->lineItemsForOrder($order),
                'order_total_formatted' => $this->formatNpr($order->grand_total),
            ],
            // Deduplicate by shared checkout order number for customer notifications.
            uniqueKey: 'order-number:' . $order->order_number . ':customer:' . $order->user_id,
        );
    }

    public function sendOrderPlacedVendor(Order $order): bool
    {
        $order->loadMissing(['vendor.user', 'items']);
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
                'line_items' => $this->lineItemsForOrder($order),
                'order_total_formatted' => $this->formatNpr($order->grand_total),
            ],
            uniqueKey: 'order:' . $order->id . ':vendor',
        );
    }

    public function sendOrderStatusChangedCustomer(Order $order, string $status): bool
    {
        $order->loadMissing(['user', 'vendor', 'items']);
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
                'line_items' => $this->lineItemsForOrder($order),
                'order_total_formatted' => $this->formatNpr($order->grand_total),
            ],
            uniqueKey: 'order:' . $order->id . ':status:' . $status . ':customer',
        );
    }

    public function sendOrderStatusChangedVendor(Order $order, string $status): bool
    {
        $order->loadMissing(['vendor.user', 'items', 'user']);
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
                'customer_name' => $order->user?->name,
                'line_items' => $this->lineItemsForOrder($order),
                'order_total_formatted' => $this->formatNpr($order->grand_total),
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
                    orderNumber: $message['orderNumber'],
                    partnerLabel: $message['partnerLabel'],
                    partnerName: $message['partnerName'],
                    orderTotalFormatted: $message['orderTotalFormatted'],
                    lineItems: $message['lineItems'],
                    statusLabel: $message['statusLabel'],
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
                    orderNumber: $message['orderNumber'],
                    partnerLabel: $message['partnerLabel'],
                    partnerName: $message['partnerName'],
                    orderTotalFormatted: $message['orderTotalFormatted'],
                    lineItems: $message['lineItems'],
                    statusLabel: $message['statusLabel'],
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
                'subject' => 'Order confirmed: ' . ($payload['order_number'] ?? ''),
                'title' => 'Your offer is confirmed',
                'lines' => [
                    'Thank you for your purchase. Your order is confirmed and your deal details are summarized below.',
                    'Bring your claim code when you visit the vendor. You can review this order anytime in My Purchases.',
                ],
                'actionText' => 'View My Purchases',
                'actionUrl' => url('/dashboard/purchases'),
                'metaLabel' => 'Reference',
                'metaValue' => (string) ($payload['order_number'] ?? ''),
                'orderNumber' => $payload['order_number'] ?? null,
                'partnerLabel' => 'Vendor',
                'partnerName' => $payload['vendor_name'] ?? null,
                'orderTotalFormatted' => $payload['order_total_formatted'] ?? null,
                'lineItems' => $payload['line_items'] ?? [],
                'statusLabel' => 'Order placed',
            ],
            'order.placed.vendor' => [
                'subject' => 'New order: ' . ($payload['order_number'] ?? ''),
                'title' => 'You have a new order',
                'lines' => [
                    'A customer placed an order with your business. Items and pricing are shown below.',
                    'Please review and fulfil the order from your vendor orders page.',
                ],
                'actionText' => 'Open Vendor Orders',
                'actionUrl' => url('/vendor/orders'),
                'metaLabel' => 'Reference',
                'metaValue' => (string) ($payload['order_number'] ?? ''),
                'orderNumber' => $payload['order_number'] ?? null,
                'partnerLabel' => 'Customer',
                'partnerName' => $payload['customer_name'] ?? null,
                'orderTotalFormatted' => $payload['order_total_formatted'] ?? null,
                'lineItems' => $payload['line_items'] ?? [],
                'statusLabel' => 'New sale',
            ],
            'order.status_changed.customer' => $this->buildOrderStatusChangedCustomerMessage($payload),
            'order.status_changed.vendor' => $this->buildOrderStatusChangedVendorMessage($payload),
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

    protected function buildOrderStatusChangedCustomerMessage(array $payload): array
    {
        $appName = config('app.name', 'Sasto Offer');
        $status = (string) ($payload['status'] ?? 'updated');
        $orderNumber = (string) ($payload['order_number'] ?? '');
        $fmt = $this->formatStatus($status);

        $label = match ($status) {
            'redeemed' => 'Offer redeemed',
            'paid' => 'Paid',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => $fmt,
        };

        [$subject, $title, $lines] = match ($status) {
            'redeemed' => [
                'Offer redeemed: ' . $orderNumber,
                'Your offer has been redeemed',
                [
                    'The vendor has confirmed your visit. Your order ' . $orderNumber . ' is now marked as redeemed.',
                    'Thank you for using ' . $appName . '. We hope you enjoyed your deal.',
                ],
            ],
            'paid' => [
                'Payment confirmed: ' . $orderNumber,
                'Payment confirmed',
                [
                    'Your order ' . $orderNumber . ' is now marked as paid.',
                    'You can review your claim details and visit the vendor when you are ready.',
                ],
            ],
            'cancelled' => [
                'Order cancelled: ' . $orderNumber,
                'Your order was cancelled',
                [
                    'Your order ' . $orderNumber . ' has been cancelled.',
                    'If you did not expect this change, please contact the vendor or support.',
                ],
            ],
            'refunded' => [
                'Refund recorded: ' . $orderNumber,
                'Your order was refunded',
                [
                    'Your order ' . $orderNumber . ' has been marked as refunded.',
                    'If you have questions about your refund, please contact the vendor.',
                ],
            ],
            default => [
                'Order update: ' . $orderNumber,
                'Your order status has changed',
                [
                    'Your order ' . $orderNumber . ' is now: ' . $fmt . '.',
                    'You can review the full details and history in My Purchases.',
                ],
            ],
        };

        return [
            'subject' => $subject,
            'title' => $title,
            'lines' => $lines,
            'actionText' => 'View My Purchases',
            'actionUrl' => url('/dashboard/purchases'),
            'metaLabel' => 'Reference',
            'metaValue' => $orderNumber,
            'orderNumber' => $payload['order_number'] ?? null,
            'partnerLabel' => 'Vendor',
            'partnerName' => $payload['vendor_name'] ?? null,
            'orderTotalFormatted' => $payload['order_total_formatted'] ?? null,
            'lineItems' => $payload['line_items'] ?? [],
            'statusLabel' => $label,
        ];
    }

    protected function buildOrderStatusChangedVendorMessage(array $payload): array
    {
        $status = (string) ($payload['status'] ?? 'updated');
        $orderNumber = (string) ($payload['order_number'] ?? '');
        $fmt = $this->formatStatus($status);

        $label = match ($status) {
            'redeemed' => 'Redeemed',
            'paid' => 'Paid',
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => $fmt,
        };

        [$subject, $title, $lines] = match ($status) {
            'redeemed' => [
                'Order redeemed: ' . $orderNumber,
                'Order marked as redeemed',
                [
                    'The order ' . $orderNumber . ' is now marked as redeemed.',
                    'Thank you for confirming the customer visit.',
                ],
            ],
            'paid' => [
                'Order updated: ' . $orderNumber,
                'Order marked as paid',
                [
                    'The order ' . $orderNumber . ' is now marked as paid.',
                    'You can continue managing this order from Vendor Orders.',
                ],
            ],
            'cancelled' => [
                'Order cancelled: ' . $orderNumber,
                'Order marked as cancelled',
                [
                    'The order ' . $orderNumber . ' has been cancelled.',
                ],
            ],
            'refunded' => [
                'Order refunded: ' . $orderNumber,
                'Order marked as refunded',
                [
                    'The order ' . $orderNumber . ' has been marked as refunded.',
                ],
            ],
            default => [
                'Order status saved: ' . $orderNumber,
                'Order status update confirmed',
                [
                    'The order ' . $orderNumber . ' is now: ' . $fmt . '.',
                    'You can continue managing this order from Vendor Orders.',
                ],
            ],
        };

        return [
            'subject' => $subject,
            'title' => $title,
            'lines' => $lines,
            'actionText' => 'Open Vendor Orders',
            'actionUrl' => url('/vendor/orders'),
            'metaLabel' => 'Reference',
            'metaValue' => $orderNumber,
            'orderNumber' => $payload['order_number'] ?? null,
            'partnerLabel' => 'Customer',
            'partnerName' => $payload['customer_name'] ?? null,
            'orderTotalFormatted' => $payload['order_total_formatted'] ?? null,
            'lineItems' => $payload['line_items'] ?? [],
            'statusLabel' => $label,
        ];
    }

    protected function formatNpr(float|string $amount): string
    {
        return 'Rs. ' . number_format((float) $amount, 2);
    }

    protected function absoluteUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $trim = trim($url);

        if (preg_match('#^https?://#i', $trim)) {
            return $trim;
        }

        return url($trim);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function lineItemsForOrder(Order $order): array
    {
        $order->loadMissing('items');
        $orderStatus = (string) $order->status;
        $out = [];

        foreach ($order->items as $item) {
            $meta = is_array($item->meta) ? $item->meta : [];
            $claimedAt = $meta['claimed_at'] ?? null;
            $redeemed = $orderStatus === 'redeemed' || $claimedAt;

            $out[] = [
                'title' => $item->title,
                'image' => $this->absoluteUrl($meta['deal_image'] ?? null),
                'offer_type' => $meta['offer_type'] ?? null,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'redemption' => $redeemed ? 'redeemed' : 'pending',
            ];
        }

        return $out;
    }

    protected function formatStatus(string $status): string
    {
        return ucfirst(str_replace('_', ' ', $status));
    }
}
