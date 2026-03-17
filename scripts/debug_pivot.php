<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\DealOfferType::with('offerType')->first();
if (!$p) {
    echo "no pivot\n";
    exit(0);
}

echo "deal_id={$p->deal_id} offer_type_id={$p->offer_type_id}\n";
echo "orig={$p->original_price} final={$p->final_price} disc_pct={$p->discount_percent} disc_amt={$p->discount_amount}\n";
echo "params=" . json_encode($p->params) . "\n";
echo "rule=" . json_encode($p->offerType?->calculation_rule) . "\n";

