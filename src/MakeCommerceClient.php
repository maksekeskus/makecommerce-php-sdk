<?php

declare(strict_types=1);

namespace MakeCommerce;

use GuzzleHttp\Client;
use MakeCommerce\Actions\Refunds\CreateRefundAction;
use MakeCommerce\Actions\Refunds\ListRefundsAction;
use MakeCommerce\Actions\Refunds\ViewRefundAction;
use MakeCommerce\Actions\Shop\ViewAccountstatementAction;
use MakeCommerce\Actions\Shop\ViewConfigurationAction;
use MakeCommerce\Actions\Shop\ViewFeesAction;
use MakeCommerce\Actions\Shop\ViewPaymentMethods;
use MakeCommerce\Actions\Transactions\AddMerchantDataToTransactionAction;
use MakeCommerce\Actions\Transactions\CreatePaymentAction;
use MakeCommerce\Actions\Transactions\CreateTransactionAction;
use MakeCommerce\Actions\Transactions\ListTransactionsAction;
use MakeCommerce\Actions\Transactions\ViewTransactionAction;

class MakeCommerceClient
{
    private string $shopId;
    private string $secretKey;
    private Client $client;
    private string $apiUrl;

    private array $appInfo = [
        'module' => 'MakeCommerce PHP SDK',
        'module_version' => '2.0.0',
        'platform' => 'PLATFORM_TEST',
        'platform_version' => 'PLATFORM_VERSION_1'
    ];

    /**
     * API client constructor
     *
     * @param string $shopId Shop ID
     * @param string $secretKey Secret API Key
     * @param bool $testEnv TRUE if connecting to API in test environment, FALSE otherwise. Default to FALSE.
     */
    public function __construct(
        string $shopId,
        string $secretKey,
        string $platform,
        string $platformVersion,
        bool $testEnv = false
    ) {
        if ($testEnv) {
            $this->apiUrl = Environment::TEST_BASEURI . Environment::API_VERSION;
        } else {
            $this->apiUrl = Environment::LIVE_BASEURI . Environment::API_VERSION;
        }

        $this->shopId = $shopId;
        $this->secretKey = $secretKey;
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'auth' => [$this->shopId, $this->secretKey]]);

        $this->appInfo['platform'] = $platform;
        $this->appInfo['platform_version'] = $platformVersion;
    }

    /**
     * Retrieves the shop configuration.
     * https://developer.makecommerce.net/api/get-shop-configuration
     *
     * @return MCResponse The response containing the shop configuration information
     * @throws MCException
     */
    public function getShopConfiguration(): MCResponse
    {
        $getShopConfiguration = new ViewConfigurationAction($this->client);

        return $getShopConfiguration->action();
    }

    /**
     * Retrieves the shop payment methods.
     * https://developer.makecommerce.net/api/get-payment-methods
     *
     * @param array $queryParams The query parameters for the request
     *
     * @return MCResponse The response containing the shop payment methods
     * @throws MCException
     */
    public function getShopPaymentMethods(array $queryParams = []): MCResponse
    {
        $getShopPaymentMethods = new ViewPaymentMethods($this->client);

        return $getShopPaymentMethods->action($queryParams);
    }

    /**
     * Retrieves the account statements.
     * https://developer.makecommerce.net/api/get-account-statement
     *
     * @param array $queryParams The query parameters
     *
     * @return MCResponse The response containing the account statements
     * @throws MCException
     */
    public function getAccountStatement(array $queryParams): MCResponse
    {
        $getAccountStatement = new ViewAccountstatementAction($this->client);

        return $getAccountStatement->action($queryParams);
    }

    /**
     * Retrieves the account statement in XML format.
     * https://developer.makecommerce.net/api/get-account-statement-xml
     *
     * @param array $queryParams The query parameters
     *
     * @return MCResponse The response containing the account statement
     * @throws MCException
     */
    public function getAccountStatementXML(array $queryParams): MCResponse
    {
        $getAccountStatement = new ViewAccountstatementAction($this->client);
        $getAccountStatement->setXML();

        return $getAccountStatement->action($queryParams);
    }

    /**
     * Retrieves the account statement in CAMT053 format.
     * https://developer.makecommerce.net/api/get-account-statement-camt-053
     *
     * @param array $queryParams The query parameters
     *
     * @return MCResponse The response containing the account statement
     * @throws MCException
     */
    public function getAccountStatementCAMT053(array $queryParams): MCResponse
    {
        $getAccountStatement = new ViewAccountstatementAction($this->client);
        $getAccountStatement->setCAMT053();

        return $getAccountStatement->action($queryParams);
    }

    /**
     * Retrieves the shop fees.
     * https://developer.makecommerce.net/api/get-fees
     *
     * @return MCResponse The response containing the shop fees information
     * @throws MCException
     */
    public function getShopFees(array $queryParams = []): MCResponse
    {
        $getFees = new ViewFeesAction($this->client);

        return $getFees->action($queryParams);
    }

    /**
     * Creates transaction.
     * https://developer.makecommerce.net/api/create-transaction
     *
     * @param array $data The query parameters for creating transaction
     *
     * @return MCResponse The response containing created transaction details
     * @throws MCException
     */
    public function createTransaction(array $data): MCResponse
    {
        $createTransaction = new CreateTransactionAction($this->client);
        $data['app_info'] = $this->appInfo;

        return $createTransaction->action($data);
    }


    /**
     * Used to verify MAC on payment return message
     * https://developer.makecommerce.net/guides/custom-api/RegularPaymentFlow#validation-of-mac-value
     *
     * @param array $data
     * example payload, when using GET callback ['json' => $_GET['json'], 'mac' => $_GET['mac']]
     *
     * @return bool Verify MAC
     * @throws MCException
     */
    public function verifyMac(array $data): bool
    {
        if (empty($data['json']) || empty($data['mac'])) {
            throw new MCException("Unable to extract needed data to verify MAC.");
        }
        $expectedMac = $data['mac'];
        $calculatedMac = strtoupper(hash('sha512', $data['json'] . $this->secretKey));

        return hash_equals($expectedMac, $calculatedMac);
    }


    /**
     * Retrieves a list of transactions.
     * https://developer.makecommerce.net/api/get-transaction-list
     *
     * @param array $queryParams The query parameters for getting transactions
     *
     * @return MCResponse The response containing the list of transactions
     * @throws MCException
     */
    public function getTransactions(array $queryParams = []): MCResponse
    {
        $getTransactions = new ListTransactionsAction($this->client);

        return $getTransactions->action($queryParams);
    }

    /**
     * Retrieves one transaction by id.
     * https://developer.makecommerce.net/api/get-transaction
     *
     * @param string $transactionId The ID of the transaction to retrieve
     *
     * @return MCResponse The response containing the details transaction
     * @throws MCException
     */
    public function getTransaction(string $transactionId): MCResponse
    {
        $getTransaction = new ViewTransactionAction($this->client);
        $getTransaction->setTransaction($transactionId);

        return $getTransaction->action();
    }

    /**
     * Get transaction statement by id.
     * https://developer.makecommerce.net/api/get-transaction-statement
     *
     * @param string $transactionId The ID of the transaction to retrieve transaction statement
     *
     * @return MCResponse The response containing the transaction statement details
     * @throws MCException
     */
    public function getTransactionStatement(string $transactionId): MCResponse
    {
        $getTransaction = new ViewTransactionAction($this->client);
        $getTransaction->setTransactionStatement($transactionId);

        return $getTransaction->action();
    }


    /**
     * Add Merchant Data to transaction
     * https://developer.makecommerce.net/api/add-merchant-data-to-transaction
     *
     * @param string $transactionId The ID of the transaction
     * @param array $data The merchant data to add, ['merchant_data' => 'example-data']
     *
     * @return MCResponse The response containing the result of adding merchant data
     * @throws MCException
     */
    public function addMerchantDataToTransaction(string $transactionId, array $data): MCResponse
    {
        $getTransaction = new AddMerchantDataToTransactionAction($this->client);
        $getTransaction->setTransaction($transactionId);

        return $getTransaction->action($data);
    }

    /**
     * Creates a payment for a transaction.
     * https://developer.makecommerce.net/api/create-payment
     * https://developer.makecommerce.net/guides/custom-api/recurringPayments
     * Create Payment endpoint is needed only to initiate recurring card payment with
     * previously acquired and saved token.
     * This endpoint is not relevant for regular one-time payments.
     *
     * @param string $transactionId The ID of the transaction
     * @param string $token The payment token
     *
     * @return MCResponse The response containing the result of creating a payment
     * @throws MCException
     */
    public function createPayment(string $transactionId, string $token): MCResponse
    {
        $createPayment = new CreatePaymentAction($this->client);
        $createPayment->setTransaction($transactionId);

        return $createPayment->action(['token' => $token]);
    }

    /**
     * Creates a refund for a transaction.
     * https://developer.makecommerce.net/api/create-refund
     *
     * @param string $transactionId The ID of the transaction
     * @param array $data The refund data
     *
     * @return MCResponse The response containing the result of creating a refund
     * @throws MCException
     */
    public function createRefund(string $transactionId, array $data): MCResponse
    {
        $createRefund = new CreateRefundAction($this->client);
        $createRefund->setTransaction($transactionId);
        $data['app_info'] = $this->appInfo;

        return $createRefund->action($data);
    }

    /**
     * Retrieves a specific refund.
     * https://developer.makecommerce.net/api/get-refund
     *
     * @param string $refund_id The ID of the refund
     *
     * @return MCResponse The response containing the specific refund
     * @throws MCException
     */
    public function getRefund(string $refund_id): MCResponse
    {
        $getRefund = new ViewRefundAction($this->client);
        $getRefund->setRefund($refund_id);

        return $getRefund->action();
    }

    /**
     * Retrieves the list of refunds.
     * https://developer.makecommerce.net/api/get-refund-list
     *
     * @param array $queryParams The query parameters for getting refunds
     *
     * @return MCResponse The response containing the list of refunds
     * @throws MCException
     */
    public function getRefunds(array $queryParams = []): MCResponse
    {
        $getRefunds = new ListRefundsAction($this->client);

        return $getRefunds->action($queryParams);
    }
}
