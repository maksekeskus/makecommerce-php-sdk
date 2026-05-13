# MakeCommerce PHP SDK — Complete Implementation Reference

MakeCommerce is a Baltic payment gateway (Estonia, Latvia, Lithuania).  
Developer portal: https://developer.makecommerce.net/

---

## Installation

```bash
composer require maksekeskus/makecommerce-php-sdk
```

Requirements: PHP ^8.1, ext-json

---

## Initialization

### Public test credentials

Use these to get real API responses immediately — no sign-up required:

| Key | Value |
|-----|-------|
| Shop ID | `3425d8b7-0225-4367-8c6f-16b1aba8d766` |
| Secret key | `J5S4lcVjC1QfJec8IQPhHSKeAiEf10bPV7KrHPx9AmIl9nCoEtNtJo63SF0YKpFQ` |
| Publishable key | `79p15UvwBLlZfqmoMY8D8LAjq4CwI8Tn` |

These are also available as constants: `Environment::TEST_SHOP_ID`, `Environment::TEST_SECRET_KEY`, `Environment::TEST_PUBLISHABLE_KEY`.

```php
use MakeCommerce\MakeCommerceClient;
use MakeCommerce\Environment;

// Using public test credentials — works immediately, no account needed
$client = new MakeCommerceClient(
    shopId: Environment::TEST_SHOP_ID,
    secretKey: Environment::TEST_SECRET_KEY,
    platform: 'MyPlatform',
    platformVersion: '1.0.0',
    testEnv: true
);

// Or with your own credentials
$client = new MakeCommerceClient(
    shopId: 'YOUR_SHOP_ID',
    secretKey: 'YOUR_SECRET_KEY',
    platform: 'MyPlatform',
    platformVersion: '1.0.0',
    testEnv: true   // true = test, false = production (default)
);
```

API base URLs:
- Test: `https://api.test.maksekeskus.ee/v1/`
- Production: `https://api.maksekeskus.ee/v1/`

Own credentials: https://merchant.test.maksekeskus.ee (test) | https://merchant.maksekeskus.ee (production)

---

## Response & Error

Every method returns `MCResponse` or throws `MCException`.

```php
// MCResponse properties
$response->code;      // int — 200 or 201
$response->body;      // array — JSON-decoded body (null for XML responses)
$response->rawBody;   // string — raw body string
$response->headers;   // array — all response headers

// MCException
use MakeCommerce\MCException;
try {
    $r = $client->createTransaction([...]);
} catch (MCException $e) {
    $e->getMessage(); // error description or validation message
    $e->getCode();    // HTTP status code: 400, 401, etc.
}
```

Error response body shape (from the API):
```json
{
  "code": 400,
  "message": "Bad Request",
  "errors": [
    { "type": "validation", "parameter": "transaction.amount", "expected": "string" }
  ]
}
```

---

## SHOP

### getShopConfiguration()

Returns the shop's settings, enabled payment methods and features.

```php
$response = $client->getShopConfiguration();
```

No parameters.

**Response `$response->body`:**
```json
{
  "id": "abc123",
  "object": "shop",
  "created_at": "2023-01-15T10:00:00Z",
  "modified_at": "2024-06-01T12:00:00Z",
  "name": "My Shop",
  "status": "active",
  "return": {
    "url": "https://myshop.com/payment/return",
    "method": "GET"
  },
  "notifications": {
    "email": "owner@myshop.com",
    "url": "https://myshop.com/payment/notify",
    "method": "POST"
  },
  "contact": {
    "email": "support@myshop.com",
    "phone": "+372 5000 0000"
  },
  "payment_methods": {
    "banklinks": [
      {
        "name": "SWEDBANK",
        "url": "https://payment.url/...",
        "country": "EE",
        "countries": ["EE", "LV", "LT"],
        "min_amount": 0.01,
        "max_amount": 15000.00,
        "channel": "BANKLINK",
        "display_name": "Swedbank",
        "logo_url": "https://static.makecommerce.net/logos/swedbank.svg"
      }
    ],
    "cards": [
      {
        "name": "CARD",
        "url": "https://payment.url/...",
        "country": "EE",
        "countries": ["EE", "LV", "LT"],
        "min_amount": 0.01,
        "max_amount": 15000.00,
        "channel": "CARD",
        "display_name": "Visa/Mastercard",
        "logo_url": "https://static.makecommerce.net/logos/card.svg"
      }
    ],
    "payLater": [],
    "other": []
  },
  "features": [
    { "object": "feature", "name": "recurring", "enabled": true },
    { "object": "feature", "name": "refunds", "enabled": true }
  ]
}
```

---

### getShopPaymentMethods(array $queryParams = [])

Returns available payment methods, optionally filtered by transaction/amount/currency/country.

```php
// All available methods
$response = $client->getShopPaymentMethods();

// Filtered for a specific transaction context
$response = $client->getShopPaymentMethods([
    'transaction' => 'TXN_ID_123',   // optional — filter to methods valid for this transaction
    'amount'      => '49.90',         // optional — filter by amount
    'currency'    => 'EUR',           // optional — filter by currency
    'country'     => 'EE',            // optional — filter by customer country (ISO 3166-1 alpha-2)
]);
```

All parameters optional. No parameters required.

**Response `$response->body`:**
```json
{
  "banklinks": [
    {
      "name": "SWEDBANK",
      "url": "https://payment.url/...",
      "country": "EE",
      "countries": ["EE", "LV", "LT"],
      "min_amount": 0.01,
      "max_amount": 15000.00,
      "channel": "BANKLINK",
      "display_name": "Swedbank",
      "logo_url": "https://static.makecommerce.net/logos/swedbank.svg"
    },
    {
      "name": "SEB",
      "url": "https://payment.url/...",
      "country": "EE",
      "countries": ["EE"],
      "min_amount": 0.01,
      "max_amount": 15000.00,
      "channel": "BANKLINK",
      "display_name": "SEB",
      "logo_url": "https://static.makecommerce.net/logos/seb.svg"
    }
  ],
  "cards": [
    {
      "name": "CARD",
      "url": "https://payment.url/...",
      "country": "EE",
      "countries": ["EE", "LV", "LT"],
      "min_amount": 0.01,
      "max_amount": 15000.00,
      "channel": "CARD",
      "display_name": "Visa/Mastercard",
      "logo_url": "https://static.makecommerce.net/logos/card.svg"
    }
  ],
  "payLater": [
    {
      "name": "INBANK",
      "url": "https://payment.url/...",
      "country": "EE",
      "countries": ["EE", "LV", "LT"],
      "min_amount": 75.00,
      "max_amount": 5000.00,
      "channel": "INBANK",
      "display_name": "Inbank",
      "logo_url": "https://static.makecommerce.net/logos/inbank.svg"
    }
  ],
  "other": []
}
```

---

### getAccountStatement(array $queryParams)

Returns JSON account statement. Either `since` or `payout_id` is required.

```php
$response = $client->getAccountStatement([
    'since'     => '2024-01-01',  // required if payout_id not provided (YYYY-MM-DD)
    'until'     => '2024-12-31',  // optional (YYYY-MM-DD)
    'payout_id' => 'PAY_123',     // required if since not provided
    'page'      => '1',           // optional
    'per_page'  => '50',          // optional
]);
```

**Response `$response->body`** — array of statement entries:
```json
[
  {
    "created": "2024-03-15T14:22:10Z",
    "amount": 47.50,
    "balance_before": 120.00,
    "balance_after": 167.50,
    "type": "PAYMENT",
    "transaction": "ORDER-456",
    "transaction_id": "txn_abc123def456",
    "channel": "CARD",
    "merchant_reference": "ORDER-456",
    "payout_id": "PAY_789",
    "original_amount": 47.50,
    "original_currency": "EUR",
    "exchange_rate": 1.0
  }
]
```


---

### getAccountStatementXML(array $queryParams)

Returns account statement as XML. Requires `until` AND either `since` or `payout_id`.

```php
$response = $client->getAccountStatementXML([
    'since'     => '2024-01-01',  // required if payout_id not provided
    'until'     => '2024-12-31',  // required
    'payout_id' => 'PAY_123',     // required if since not provided
    'page'      => '1',           // optional
    'per_page'  => '50',          // optional
]);

$xmlString = $response->rawBody; // use rawBody — body will be null (not JSON)
```

**Response:** XML string in `$response->rawBody`. `$response->body` is null.

---

### getAccountStatementCAMT053(array $queryParams)

Same as XML but returns CAMT053 banking XML format. Same parameters and constraints as `getAccountStatementXML`.

```php
$response = $client->getAccountStatementCAMT053([
    'since' => '2024-01-01',
    'until' => '2024-12-31',
]);

$camt053Xml = $response->rawBody;
```

---

### getShopFees(array $queryParams = [])

Returns monthly service fees (non-transaction accounting records).

```php
$response = $client->getShopFees([
    'since'    => '2024-01-01',  // optional (YYYY-MM-DD)
    'until'    => '2024-12-31',  // optional (YYYY-MM-DD)
    'page'     => '1',           // optional
    'per_page' => '50',          // optional
]);
```

**Response `$response->body`** — array:
```json
[
  {
    "accounting_id": "fee_abc123",
    "object": "fee",
    "created_at": "2024-03-01T00:00:00Z",
    "amount": 25.00,
    "vat": 5.00
  }
]
```

---

## TRANSACTIONS

### createTransaction(array $data)

Creates a new payment transaction. Returns a transaction object with a `_links.Pay.href` payment URL to redirect the customer to.

**Required:** `transaction.amount`, `transaction.currency`, `customer.ip`

```php
$response = $client->createTransaction([
    'transaction' => [
        'amount'             => '49.90',          // string, numeric, required
        'currency'           => 'EUR',            // string, 3-char ISO 4217, required
        'reference'          => 'ORDER-123',      // your order ID, optional but recommended
        'merchant_data'      => 'any-string',     // arbitrary string, returned on callback, optional
        'recurring_required' => false,            // bool, true to save card token, optional
        'transaction_url'    => [                 // optional, overrides shop defaults
            'return_url' => [
                'url'    => 'https://myshop.com/payment/return',
                'method' => 'GET',                // 'GET' or 'POST'
            ],
            'cancel_url' => [
                'url'    => 'https://myshop.com/payment/cancel',
                'method' => 'GET',
            ],
            'notification_url' => [
                'url'    => 'https://myshop.com/payment/notify',
                'method' => 'POST',
            ],
        ],
    ],
    'customer' => [
        'ip'      => '1.2.3.4',             // string IPv4, required
        'email'   => 'buyer@example.com',   // optional
        'country' => 'EE',                  // optional, ISO 3166-1 alpha-2
        'locale'  => 'et',                  // optional, 2-char language code
    ],
    // Do NOT pass app_info — the SDK injects it automatically
]);

// The payment URL to redirect the customer to:
$paymentUrl    = $response->body['_links']['Pay']['href'];
$transactionId = $response->body['id'];

header('Location: ' . $paymentUrl);
exit;
```

**Response `$response->body`** (HTTP 201):
```json
{
  "_links": {
    "Pay": { "href": "https://payment.makecommerce.net/pay/txn_abc123" },
    "self": { "href": "https://api.maksekeskus.ee/v1/transactions/txn_abc123" }
  },
  "payment_methods": {},
  "id": "txn_abc123def456",
  "object": "transaction",
  "created_at": "2024-03-15T14:00:00Z",
  "completed_at": null,
  "refunded_at": null,
  "status": "CREATED",
  "reference": "ORDER-123",
  "customer": {
    "id": "cust_xyz789",
    "object": "customer",
    "created_at": "2024-03-15T14:00:00Z",
    "email": "buyer@example.com",
    "locale": "et",
    "country": "EE",
    "ip": "1.2.3.4",
    "ip_country": "EE",
    "name": null
  },
  "refunded_amount": 0.0,
  "refunded_original_amount": 0.0,
  "type": null,
  "method": null,
  "channel": null,
  "country": "EE",
  "fees": null,
  "fees_vat": null,
  "net_amount": null,
  "merchant_data": "any-string",
  "banklink": null,
  "card": null,
  "transaction_url": {
    "return_url":       { "url": "https://myshop.com/payment/return", "method": "GET" },
    "cancel_url":       { "url": "https://myshop.com/payment/cancel", "method": "GET" },
    "notification_url": { "url": "https://myshop.com/payment/notify", "method": "POST" },
    "cart_url":         { "url": null, "method": null }
  },
  "recurring_required": false
}
```

---

### getTransaction(string $transactionId)

Retrieves a single transaction by ID.

```php
$response = $client->getTransaction('txn_abc123def456');
```

**Response `$response->body`** — Transaction object (same shape as `createTransaction` response, with `_links.self` added, and populated `method`, `channel`, `fees`, etc. once completed):
```json
{
  "_links": {
    "self": { "href": "https://api.maksekeskus.ee/v1/transactions/txn_abc123def456" }
  },
  "id": "txn_abc123def456",
  "object": "transaction",
  "created_at": "2024-03-15T14:00:00Z",
  "completed_at": "2024-03-15T14:05:32Z",
  "refunded_at": null,
  "status": "COMPLETED",
  "reference": "ORDER-123",
  "customer": {
    "id": "cust_xyz789",
    "object": "customer",
    "created_at": "2024-03-15T14:00:00Z",
    "email": "buyer@example.com",
    "locale": "et",
    "country": "EE",
    "ip": "1.2.3.4",
    "ip_country": "EE",
    "name": "John Doe"
  },
  "refunded_amount": 0.0,
  "refunded_original_amount": 0.0,
  "type": "BANKLINK",
  "method": "SWEDBANK",
  "channel": "BANKLINK",
  "country": "EE",
  "fees": 0.42,
  "fees_vat": 0.08,
  "net_amount": 49.48,
  "merchant_data": "any-string",
  "banklink": {
    "object": "banklink",
    "created_at": "2024-03-15T14:05:32Z",
    "iban": "EE123456789012345678",
    "description": "ORDER-123"
  },
  "card": null,
  "transaction_url": {
    "return_url":       { "url": "https://myshop.com/payment/return", "method": "GET" },
    "cancel_url":       { "url": "https://myshop.com/payment/cancel", "method": "GET" },
    "notification_url": { "url": "https://myshop.com/payment/notify", "method": "POST" },
    "cart_url":         { "url": null, "method": null }
  },
  "recurring_required": false
}
```

When paid by card, `banklink` is null and `card` is populated:
```json
{
  "card": {
    "card_holder_name": "JOHN DOE",
    "card_number": "411111******1111",
    "expiry_date": "12/26",
    "card_type": "VISA"
  }
}
```

---

### getTransactions(array $queryParams = [])

Returns a paginated list of transactions.

```php
$response = $client->getTransactions([
    'since'           => '2024-01-01',   // optional, YYYY-MM-DD
    'until'           => '2024-12-31',   // optional, YYYY-MM-DD
    'completed_since' => '2024-01-01',   // optional, filter by completion date
    'completed_until' => '2024-12-31',   // optional
    'refunded_since'  => '2024-01-01',   // optional, filter by refund date
    'refunded_until'  => '2024-12-31',   // optional
    'status'          => 'COMPLETED',    // optional, comma-separated for multiple: 'COMPLETED,CANCELLED'
    'page'            => 1,              // optional
    'per_page'        => 50,             // optional
]);
```

**Response `$response->body`** — array of Transaction objects (same shape as `getTransaction`):
```json
[
  {
    "id": "txn_abc123def456",
    "object": "transaction",
    "created_at": "2024-03-15T14:00:00Z",
    "completed_at": "2024-03-15T14:05:32Z",
    "refunded_at": null,
    "status": "COMPLETED",
    "reference": "ORDER-123",
    "customer": { "...": "..." },
    "type": "BANKLINK",
    "method": "SWEDBANK",
    "channel": "BANKLINK",
    "country": "EE",
    "fees": 0.42,
    "fees_vat": 0.08,
    "net_amount": 49.48,
    "merchant_data": "any-string",
    "banklink": { "...": "..." },
    "card": null,
    "transaction_url": { "...": "..." },
    "recurring_required": false
  }
]
```


---

### getTransactionStatement(string $transactionId)

Returns the accounting entries for a single transaction (payment fee, settlement, etc.).

```php
$response = $client->getTransactionStatement('txn_abc123def456');
```

**Response `$response->body`** — array of statement entries:
```json
[
  {
    "created": "2024-03-15T14:05:32Z",
    "amount": 49.90,
    "type": "PAYMENT",
    "transaction": "ORDER-123",
    "channel": "BANKLINK",
    "merchant_reference": "ORDER-123",
    "original_amount": 49.90,
    "exchange_rate": 1.0
  },
  {
    "created": "2024-03-15T14:05:32Z",
    "amount": -0.42,
    "type": "FEE",
    "transaction": "ORDER-123",
    "channel": "BANKLINK",
    "merchant_reference": "ORDER-123",
    "original_amount": -0.42,
    "exchange_rate": 1.0
  }
]
```

---

### addMerchantDataToTransaction(string $transactionId, array $data)

Attaches custom merchant data string to an existing transaction.

```php
$response = $client->addMerchantDataToTransaction('txn_abc123def456', [
    'merchant_data' => 'custom-data-string',
]);
```

**Response `$response->body`** — full Transaction object (same shape as `getTransaction`).

---

### verifyMac(array $data)

Verifies the MAC signature on the payment return/notification callback. **Always call this before trusting callback data.**

```php
// GET return URL: ?json=...&mac=...
$isValid = $client->verifyMac([
    'json' => $_GET['json'],
    'mac'  => $_GET['mac'],
]);

// POST notification body contains json and mac fields
$posted  = json_decode(file_get_contents('php://input'), true);
$isValid = $client->verifyMac([
    'json' => $posted['json'],
    'mac'  => $posted['mac'],
]);

if ($isValid) {
    $payload = json_decode($_GET['json'], true);
    // $payload['transaction']['status'] => 'COMPLETED', 'CANCELLED', etc.
    // $payload['transaction']['id']
    // $payload['transaction']['reference'] => your original order reference
}
```

Returns `true` or `false`. Throws `MCException` if `json` or `mac` keys are missing.

MAC algorithm: `strtoupper(hash('sha512', $json . $secretKey))`

---

### createPayment(string $transactionId, string $token)

Creates a recurring payment using a saved card token. **Only for recurring/subscription flows — do not use for regular one-time checkout.**

```php
$response = $client->createPayment('txn_abc123def456', 'SAVED_CARD_TOKEN');
```

**Response `$response->body`** (HTTP 201):
```json
{
  "id": "pay_xyz789",
  "object": "payment",
  "created_at": "2024-03-15T14:00:00Z",
  "status": "COMPLETED",
  "amount": 49.90,
  "currency": "EUR",
  "card": {
    "last2": "11",
    "maskedPan": "411111******1111",
    "name": "JOHN DOE",
    "object": "card",
    "type": "VISA"
  },
  "transaction": {
    "id": "txn_abc123def456",
    "status": "COMPLETED",
    "reference": "ORDER-123"
  }
}
```

---

## REFUNDS

### createRefund(string $transactionId, array $data)

Creates a refund for a completed transaction. `amount` and `comment` are required.

```php
$response = $client->createRefund('txn_abc123def456', [
    'amount'  => '49.90',              // string, required — partial or full refund
    'comment' => 'Customer request',   // string, required
]);
```

**Response `$response->body`** (HTTP 201):
```json
{
  "id": "ref_abc123",
  "object": "refund",
  "created_at": "2024-03-20T09:00:00Z",
  "status": "CREATED",
  "comment": "Customer request",
  "transaction": {
    "id": "txn_abc123def456",
    "object": "transaction",
    "created_at": "2024-03-15T14:00:00Z",
    "completed_at": "2024-03-15T14:05:32Z",
    "refunded_at": "2024-03-20T09:00:00Z",
    "status": "REFUNDED",
    "reference": "ORDER-123",
    "customer": { "id": "cust_xyz789", "email": "buyer@example.com", "...": "..." },
    "refunded_amount": 49.90,
    "refunded_original_amount": 49.90,
    "type": "BANKLINK",
    "method": "SWEDBANK",
    "channel": "BANKLINK",
    "country": "EE",
    "fees": 0.42,
    "fees_vat": 0.08,
    "net_amount": 49.48,
    "merchant_data": "any-string",
    "banklink": { "object": "banklink", "iban": "EE123456789012345678", "description": "ORDER-123", "created_at": "2024-03-15T14:05:32Z" },
    "card": null,
    "transaction_url": { "...": "..." },
    "recurring_required": false
  }
}
```

---

### getRefund(string $refundId)

Retrieves a single refund by its ID.

```php
$response = $client->getRefund('ref_abc123');
```

**Response `$response->body`** — Refund object (same shape as `createRefund` response).

Refund statuses: `CREATED`, `PENDING`, `SETTLED`, `CANCELLED`, `FAILED`

---

### getRefunds(array $queryParams = [])

Returns a paginated list of refunds.

```php
$response = $client->getRefunds([
    'since'    => '2024-01-01',             // optional, YYYY-MM-DD
    'until'    => '2024-12-31',             // optional, YYYY-MM-DD
    'status'   => 'CREATED,SETTLED',        // optional, comma-separated
    'page'     => 1,                        // optional
    'per_page' => 50,                       // optional
]);
```

**Response `$response->body`** — array of Refund objects (same shape as `getRefund`):
```json
[
  {
    "id": "ref_abc123",
    "object": "refund",
    "created_at": "2024-03-20T09:00:00Z",
    "status": "SETTLED",
    "comment": "Customer request",
    "transaction": { "id": "txn_abc123def456", "...": "..." }
  }
]
```


---

## COMPLETE PAYMENT FLOW

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use MakeCommerce\MakeCommerceClient;
use MakeCommerce\MCException;

$client = new MakeCommerceClient(
    shopId: getenv('MC_SHOP_ID'),
    secretKey: getenv('MC_SECRET_KEY'),
    platform: 'MyShop',
    platformVersion: '1.0.0',
    testEnv: true
);

// ── STEP 1: Create transaction at checkout ───────────────────────────────────
try {
    $response = $client->createTransaction([
        'transaction' => [
            'amount'    => '49.90',
            'currency'  => 'EUR',
            'reference' => 'ORDER-' . $orderId,
            'transaction_url' => [
                'return_url'       => ['url' => 'https://myshop.com/return',  'method' => 'GET'],
                'cancel_url'       => ['url' => 'https://myshop.com/cancel',  'method' => 'GET'],
                'notification_url' => ['url' => 'https://myshop.com/notify',  'method' => 'POST'],
            ],
        ],
        'customer' => [
            'ip'    => $_SERVER['REMOTE_ADDR'],
            'email' => $customerEmail,
        ],
    ]);

    // Save the transaction ID against your order
    $transactionId = $response->body['id'];
    saveTransactionId($orderId, $transactionId);

    // Redirect customer to payment page
    header('Location: ' . $response->body['_links']['Pay']['href']);
    exit;

} catch (MCException $e) {
    // Show error to customer
    error_log('MakeCommerce: ' . $e->getMessage());
}

// ── STEP 2: Handle return URL (GET https://myshop.com/return?json=...&mac=...) ─
if (isset($_GET['json'], $_GET['mac'])) {
    try {
        if ($client->verifyMac(['json' => $_GET['json'], 'mac' => $_GET['mac']])) {
            $data   = json_decode($_GET['json'], true);
            $status = $data['transaction']['status']; // 'COMPLETED' or 'CANCELLED'
            if ($status === 'COMPLETED') {
                markOrderPaid($data['transaction']['reference']);
            }
        }
    } catch (MCException $e) {
        error_log('MAC error: ' . $e->getMessage());
    }
}

// ── STEP 3: Handle server notification (POST https://myshop.com/notify) ───────
// More reliable than return URL — triggered server-to-server by MakeCommerce
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if ($client->verifyMac(['json' => $data['json'], 'mac' => $data['mac']])) {
    $payload = json_decode($data['json'], true);
    if ($payload['transaction']['status'] === 'COMPLETED') {
        markOrderPaid($payload['transaction']['reference']);
    }
}

// ── STEP 4: Refund ───────────────────────────────────────────────────────────
$response = $client->createRefund($transactionId, [
    'amount'  => '49.90',
    'comment' => 'Order cancelled by customer',
]);
$refundId     = $response->body['id'];
$refundStatus = $response->body['status']; // 'CREATED'
```

---

## Transaction & Refund Statuses

| Status | Meaning |
|--------|---------|
| `CREATED` | Created, awaiting payment |
| `PENDING` | Payment in progress |
| `COMPLETED` | Payment successful |
| `CANCELLED` | Cancelled by customer |
| `EXPIRED` | Transaction expired |

| Refund Status | Meaning |
|--------------|---------|
| `CREATED` | Refund initiated |
| `PENDING` | Processing |
| `SETTLED` | Refund paid out to customer |
| `CANCELLED` | Refund cancelled |
| `FAILED` | Refund failed |

---

## SDK Architecture

```
src/
├── MakeCommerceClient.php       # All public methods — the only class you call
├── MCResponse.php               # Response wrapper (code, body, rawBody, headers)
├── MCException.php              # Exception (extends \Exception)
├── Environment.php              # URL constants (TEST_BASEURI, LIVE_BASEURI, API_VERSION)
├── Actions/
│   ├── Action.php               # Abstract base: JSON Schema validation + HTTP dispatch
│   ├── Method.php               # Enum: GET, POST
│   ├── Shop/                    # ViewConfigurationAction, ViewPaymentMethods, etc.
│   ├── Transactions/            # CreateTransactionAction, ViewTransactionAction, etc.
│   └── Refunds/                 # CreateRefundAction, ViewRefundAction, ListRefundsAction
└── Schemas/                     # JSON Schema files — input validated before HTTP call
```

---

## Critical Notes

- **`amount` is always a string** — pass `'49.90'` not `49.90`
- **`app_info` is injected automatically** — never pass it yourself in `createTransaction` or `createRefund`
- **`customer.ip` is required** for `createTransaction`
- **`getAccountStatement` requires `since` or `payout_id`** — both cannot be omitted
- **`getAccountStatementXML` / `getAccountStatementCAMT053` require `until` AND (`since` or `payout_id`)**
- **`verifyMac` must be called on every return/notification** before trusting payment status
- **`createPayment` is only for recurring card payments** with a saved token — skip for regular checkout
- **XML/CAMT053 responses:** `$response->rawBody` has the content, `$response->body` is null
