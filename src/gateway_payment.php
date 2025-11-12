<?php
require_once 'config.php';

function create_gateway_checkout($orderId, $amount, $currency = 'PHP', $metadata = []) {
    $url = GATEWAY_BASE_URL . '/v2/invoices';

    $payload = [
        'external_id' => 'order-' . $orderId,
        'amount' => round($amount, 2),
        'currency' => $currency,
        'description' => 'OrgMerch Order #' . $orderId,
        'success_redirect_url' => GATEWAY_RETURN_SUCCESS . "&order_id={$orderId}",
        'failure_redirect_url' => GATEWAY_RETURN_FAILED . "&order_id={$orderId}",
        'payment_methods' => ['GCASH'], 
        'metadata' => $metadata
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, XENDIT_SECRET_KEY . ":");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        error_log("Xendit cURL Error: $err");
        return false;
    }

    $data = json_decode($resp, true);
    return $data['invoice_url'] ?? false;
}

?>