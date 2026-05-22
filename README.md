# MakeCommerce PHP SDK

PHP SDK for the [MakeCommerce](https://makecommerce.net/) payment gateway — accepting bank transfers, cards, and pay-later payments.

- **Developer portal:** https://developer.makecommerce.net/
- **API reference:** https://developer.makecommerce.net/api/
- **Merchant portal (test):** https://merchant.test.maksekeskus.ee/
- **Merchant portal (production):** https://merchant.maksekeskus.ee/

---

## Requirements

- PHP 8.1+
- ext-json

---

## Installation

```bash
composer require maksekeskus/makecommerce-php-sdk
```

---

## Test Credentials

Use these public test credentials to get real API responses immediately — no sign-up required:

| Credential | Value |
|---|---|
| **Shop ID** | `3425d8b7-0225-4367-8c6f-16b1aba8d766` |
| **Secret key** | `J5S4lcVjC1QfJec8IQPhHSKeAiEf10bPV7KrHPx9AmIl9nCoEtNtJo63SF0YKpFQ` |
| **Publishable key** | `79p15UvwBLlZfqmoMY8D8LAjq4CwI8Tn` |

Also available as `Environment::TEST_SHOP_ID`, `Environment::TEST_SECRET_KEY`, `Environment::TEST_PUBLISHABLE_KEY`.

---

## Quick Start

```php
use MakeCommerce\MakeCommerceClient;
use MakeCommerce\MCException;
use MakeCommerce\Environment;

// Public test credentials — works immediately, no account needed
$client = new MakeCommerceClient(
    shopId: Environment::TEST_SHOP_ID,
    secretKey: Environment::TEST_SECRET_KEY,
    platform: 'MyPlatform',
    platformVersion: '1.0.0',
    module: 'MyPlugin',
    moduleVersion: '1.0.0',
    testEnv: true
);
```

For production, get your own credentials from the [merchant portal](https://merchant.maksekeskus.ee/).

---

## Payment Flow

The typical integration is three steps: create a transaction, redirect the customer, verify the result.

### 1. Create a transaction

```php
try {
    $response = $client->createTransaction([
        'transaction' => [
            'amount'    => '49.90',       // string, not float
            'currency'  => 'EUR',
            'reference' => 'ORDER-123',   // your order ID
            'transaction_url' => [
                'return_url'       => ['url' => 'https://myshop.com/payment/return', 'method' => 'GET'],
                'cancel_url'       => ['url' => 'https://myshop.com/payment/cancel', 'method' => 'GET'],
                'notification_url' => ['url' => 'https://myshop.com/payment/notify', 'method' => 'POST'],
            ],
        ],
        'customer' => [
            'ip'    => $_SERVER['REMOTE_ADDR'],  // required
            'email' => 'buyer@example.com',
        ],
    ]);

    $transactionId = $response->body['id'];
    $paymentUrl    = $response->body['_links']['Pay']['href'];

    header('Location: ' . $paymentUrl);
    exit;

} catch (MCException $e) {
    echo $e->getMessage(); // validation or API error
}
```

### 2. Verify the return

After the customer pays, MakeCommerce redirects them to your `return_url` with `json` and `mac` query parameters. Always verify the MAC before trusting the result.

```php
if (isset($_GET['json'], $_GET['mac'])) {
    if ($client->verifyMac(['json' => $_GET['json'], 'mac' => $_GET['mac']])) {
        $data = json_decode($_GET['json'], true);

        if ($data['transaction']['status'] === 'COMPLETED') {
            // Payment confirmed — mark order as paid
        }
    }
}
```

### 3. Handle server notification

MakeCommerce also sends a server-to-server POST to your `notification_url`. This is more reliable than the return URL (fires even if the browser closes). Same MAC check applies.

```php
$posted  = json_decode(file_get_contents('php://input'), true);

if ($client->verifyMac(['json' => $posted['json'], 'mac' => $posted['mac']])) {
    $payload = json_decode($posted['json'], true);

    if ($payload['transaction']['status'] === 'COMPLETED') {
        // Authoritative payment confirmation
    }
}
```

---

## Refunds

```php
$response = $client->createRefund('txn_abc123', [
    'amount'  => '49.90',
    'comment' => 'Customer request',
]);

$refundId = $response->body['id'];
```

---

## Method Reference

### Shop

| Method | Description |
|--------|-------------|
| `getShopConfiguration()` | Shop settings, enabled payment methods, features |
| `getShopPaymentMethods(array $params = [])` | Available payment methods, optionally filtered |
| `getAccountStatement(array $params)` | Account statement as JSON. Requires `since` or `payout_id` |
| `getAccountStatementXML(array $params)` | Account statement as XML. Requires `since`/`payout_id` + `until` |
| `getAccountStatementCAMT053(array $params)` | Account statement in CAMT053 banking XML format |
| `getShopFees(array $params = [])` | Monthly service fees (not transaction fees) |

### Transactions

| Method | Description |
|--------|-------------|
| `createTransaction(array $data)` | Create a payment transaction. Returns payment URL in `_links.Pay.href` |
| `getTransaction(string $id)` | Get a single transaction by ID |
| `getTransactions(array $params = [])` | Paginated transaction list with date/status filters |
| `getTransactionStatement(string $id)` | Accounting entries for a single transaction |
| `addMerchantDataToTransaction(string $id, array $data)` | Attach custom data to a transaction |
| `verifyMac(array $data)` | Verify MAC on payment return/notification callback |
| `createPayment(string $id, string $token)` | Initiate a recurring payment with a saved card token |

### Refunds

| Method | Description |
|--------|-------------|
| `createRefund(string $transactionId, array $data)` | Refund a completed transaction (full or partial) |
| `getRefund(string $refundId)` | Get a single refund by ID |
| `getRefunds(array $params = [])` | Paginated refund list with date/status filters |

---

## Query Parameter Reference

**`getShopPaymentMethods`**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `transaction` | string | no | Filter to methods valid for this transaction ID |
| `amount` | string | no | Filter by amount |
| `currency` | string | no | Filter by currency |
| `country` | string | no | Filter by customer country (ISO 3166-1 alpha-2) |

**`getAccountStatement`**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `since` | string (YYYY-MM-DD) | yes* | Start date. *Required if `payout_id` not given |
| `payout_id` | string | yes* | Payout ID. *Required if `since` not given |
| `until` | string (YYYY-MM-DD) | no | End date |
| `page` | string | no | Page number |
| `per_page` | string | no | Results per page |

**`getAccountStatementXML` / `getAccountStatementCAMT053`** — same as above but `until` is also required.

**`getTransactions`**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `since` | string (YYYY-MM-DD) | no | Created from date |
| `until` | string (YYYY-MM-DD) | no | Created to date |
| `completed_since` | string (YYYY-MM-DD) | no | Completed from date |
| `completed_until` | string (YYYY-MM-DD) | no | Completed to date |
| `refunded_since` | string (YYYY-MM-DD) | no | Refunded from date |
| `refunded_until` | string (YYYY-MM-DD) | no | Refunded to date |
| `status` | string | no | One or more statuses, comma-separated: `COMPLETED,CANCELLED` |
| `page` | integer | no | Page number |
| `per_page` | integer | no | Results per page |

**`getRefunds`**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `since` | string (YYYY-MM-DD) | no | From date |
| `until` | string (YYYY-MM-DD) | no | To date |
| `status` | string | no | One or more statuses, comma-separated |
| `page` | integer | no | Page number |
| `per_page` | integer | no | Results per page |

---

## Responses

Every method returns an `MCResponse` object:

```php
$response->code;      // int   — HTTP status (200 or 201)
$response->body;      // array — JSON-decoded body (null for XML responses)
$response->rawBody;   // string — raw response body
$response->headers;   // array — response headers
```

For XML responses (`getAccountStatementXML`, `getAccountStatementCAMT053`), use `$response->rawBody` — `$response->body` will be null.

Paginated responses include:

```php
$response->headers['X-Total-Count'][0]; // total number of records
$response->headers['Link'][0];          // RFC 5988 pagination links
```

---

## Error Handling

All methods throw `MakeCommerce\MCException` on failure.

```php
use MakeCommerce\MCException;

try {
    $response = $client->createTransaction([...]);
} catch (MCException $e) {
    $e->getMessage(); // Human-readable error or validation failure list
    $e->getCode();    // HTTP status code: 400, 401, 500, etc.
}
```

Common causes: missing required fields, invalid field format (amount must be a string), wrong credentials, invalid transaction ID.

---

## Transaction Statuses

| Status | Meaning |
|--------|---------|
| `CREATED` | Transaction created, awaiting payment |
| `PENDING` | Payment in progress |
| `COMPLETED` | Payment successful |
| `CANCELLED` | Cancelled by customer |
| `EXPIRED` | Transaction expired without payment |

## Refund Statuses

| Status | Meaning |
|--------|---------|
| `CREATED` | Refund initiated |
| `PENDING` | Processing |
| `SETTLED` | Refund paid out to customer |
| `CANCELLED` | Refund cancelled |
| `FAILED` | Refund failed |

---

## Notes

- **Amount is always a string** — pass `'49.90'`, not `49.90`
- **`customer.ip` is required** when creating a transaction
- **`app_info` is injected automatically** by the SDK — do not pass it yourself
- **`verifyMac` must always be called** before trusting return or notification data
- **`createPayment` is only for recurring card payments** with a saved token — not for regular checkout

---

## License

MIT
