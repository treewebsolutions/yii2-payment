<?php

namespace tws\payment\processors;

use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * This class is a wrapper over the Twispay payment processor REST API.
 *
 * @link https://docs.twispay.com/
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class Twispay extends BaseProcessor
{
	const PAYMENT_TYPE_PURCHASE = 'purchase';
	const PAYMENT_TYPE_RECURRING = 'recurring';

	const PAYMENT_STATUS_IN_PROGRESS = 'in-progress';
	const PAYMENT_STATUS_SUCCEEDED = 'complete-ok';
	const PAYMENT_STATUS_FAILED = 'complete-failed';

	const TRANSACTION_STATUS_SUCCEEDED = 'complete-ok';
	const TRANSACTION_STATUS_FAILED = 'complete-failed';

	/**
	 * @var string The API base URL.
	 */
	public $baseUrl;

	/**
	 * @var string|int The API key.
	 */
	public $apiKey;

	/**
	 * @var string|int The API site ID.
	 */
	public $siteId;

	/**
	 * @var array The mapping of Payer model class properties to this processor API.
	 * The key of this array represents the object property.
	 * The value of this array represents the API property.
	 */
	protected static $payerPropertiesMap = [
		'id' => 'id',
		'customId' => 'identifier',
		'name' => ['firstName', 'lastName'],
		'email' => 'email',
		'phone' => 'phone',
		'address' => ['address', 'city', 'state', 'zipCode', 'country'],
		'description' => 'tag[description]',
		'metadata' => 'tag',
	];

	/**
	 * @var array The mapping of Payment model class properties to this processor API.
	 * The key of this array represents the object property.
	 * The value of this array represents the API property.
	 */
	protected static $paymentPropertiesMap = [
		'id' => 'id',
		'customId' => 'externalOrderId',
		'payerId' => 'customerId',
		'cardId' => 'cardId',
		'ipAddress' => 'ip',
		'amount' => 'amount',
		'currency' => 'currency',
		'description' => 'description',
		'recurringConfig' => ['intervalValue', 'intervalType'],
		'type' => 'orderType',
		'status' => 'orderStatus',
		'metadata' => 'tag',
	];

	/**
	 * @var array The mapping of Transaction model class properties to this processor API.
	 * The key of this array represents the object property.
	 * The value of this array represents the API property.
	 */
	protected static $transactionPropertiesMap = [
		'id' => 'id',
		'payerId' => 'customerId',
		'cardId' => 'cardId',
		'paymentId' => 'orderId',
		'ipAddress' => 'ip',
		'amount' => 'amount',
		'currency' => 'currency',
		'description' => 'description',
		'type' => 'transactionType',
		'status' => 'transactionStatus',
	];


	//region PAYER
	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Customer-get_customer
	 */
	public function findPayer($id)
	{
		try {
			$payer = $this->makeRequest("/customer/{$id}","GET");
			return $this->toObject($this->payerModelClass, $payer);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Customer-post_customer
	 */
	public function createPayer($payer)
	{
		try {
			$payer = $this->toApiArray($this->payerModelClass, $payer);
			$response = $this->makeRequest("/customer","POST", $payer);
			if (!empty($response['customerId'])) {
				return $this->findPayer($response['customerId']);
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Customer-put_customer
	 */
	public function updatePayer($id, $payer)
	{
		try {
			$payer = $this->toApiArray($this->payerModelClass, $payer);
			$response = $this->makeRequest("/order","PUT", $payer);
			if (!empty($response['customerId'])) {
				return $this->findPayer($response['customerId']);
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Customer-delete_customer
	 */
	public function deletePayer($id)
	{
		if (is_array($id)) {
			$successCount = 0;
			foreach ($id as $payerId) {
				$response = $this->makeRequest("/customer/{$payerId}","DELETE");
				if ($response['code'] == 200) {
					$successCount++;
				}
			}
			return $successCount;
		}
		$response = $this->makeRequest("/customer/{$id}","DELETE");
		return $response['code'] == 200;
	}
	//endregion PAYER

	//region PAYMENT
	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Order-get_order
	 */
	public function findPayment($id)
	{
		try {
			$payment = $this->makeRequest("/order/{$id}","GET");
			return $this->toObject($this->paymentModelClass, $payment);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Order-post_order
	 */
	public function createPayment($payment)
	{
		try {
			$payment = $this->toApiArray($this->paymentModelClass, $payment);
			$payment = array_merge([
				'ip' => '127.0.0.1',
				'orderType' => 'purchase',
				'transactionMethod' => 'card',
				'force' => 1,
			], $payment);

			$response = $this->makeRequest("/order","POST", $payment);
			if (!empty($response['orderId'])) {
				return $this->findPayment($response['orderId']);
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Order-put_order
	 */
	public function updatePayment($id, $payment)
	{
		try {
			$payment = $this->toApiArray($this->paymentModelClass, $payment);
			$payment = array_merge([
				'ip' => '127.0.0.1',
			], $payment);

			$response = $this->makeRequest("/order/{$id}","PUT", $payment);
			if (!empty($response['orderId'])) {
				return $this->findPayment($response['orderId']);
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Order-delete_order
	 */
	public function cancelRecurringPayment($id)
	{
		if (is_array($id)) {
			$successCount = 0;
			foreach ($id as $paymentId) {
				$response = $this->makeRequest("/order/{$paymentId}","DELETE");
				if ($response['code'] == 200) {
					$successCount++;
				}
			}
			return $successCount;
		}
		$response = $this->makeRequest("/order/{$id}","DELETE");
		return $response['code'] == 200;
	}
	//endregion PAYMENT

	//region TRANSACTION
	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Transaction-get_transaction
	 */
	public function findTransaction($id)
	{
		try {
			$transaction = $this->makeRequest("/transaction/{$id}","GET");
			return $this->toObject($this->transactionModelClass, $transaction);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 * @link http://docs.twispay.com/#operations-Transaction-get_transaction
	 */
	public function findTransactions($filters)
	{
		try {
			if (isset($filters['payerId'])) {
				$filters['customerId'] = ArrayHelper::remove($filters, 'payerId');
			}
			if (isset($filters['paymentId'])) {
				$filters['orderId'] = ArrayHelper::remove($filters, 'paymentId');
			}
			if (isset($filters['startDate'])) {
				$filters['createdAtFrom'] = (new \DateTime(ArrayHelper::remove($filters, 'startDate')))->format(DATE_ISO8601);
			}
			if (isset($filters['endDate'])) {
				$filters['createdAtTo'] = (new \DateTime(ArrayHelper::remove($filters, 'endDate')))->format(DATE_ISO8601);
			}
			if (isset($filters['sort'])) {
				$filters['reverseSorting'] = ArrayHelper::remove($filters, 'sort') == SORT_DESC ? 1 : 0;
			}

			$transactions = $this->makeRequest("/transaction","GET", $filters);
			if (count($transactions) > 0) {
				foreach ($transactions as $i => $transaction) {
					$transactions[$i] = $this->toObject($this->transactionModelClass, $transaction);
				}
				return $transactions;
			}
			return [];
		} catch (\Exception $e) {
			return [];
		}
	}
	//endregion TRANSACTION

	/**
	 * Creates an instance of a class with properties mapped from this processor API.
	 *
	 * @param string $className the class name to be used in order to identify the mapping.
	 * @param array $properties a list of properties to be mapped with the [[$className]].
	 * @return object|null a new object of mapped properties.
	 * @throws \yii\base\InvalidConfigException
	 */
	protected function toObject($className, $properties)
	{
		$propertiesMap = [];
		$config = [
			'class' => $className,
		];

		if ($className === $this->payerModelClass) {
			$propertiesMap = self::$payerPropertiesMap;
			if (isset($properties['firstName']) || isset($properties['middleName']) || isset($properties['lastName'])) {
				$properties['name'] = [
					'firstName' => $properties['firstName'],
					'middleName' => $properties['middleName'],
					'lastName' => $properties['lastName'],
				];
			}
			if (isset($properties['address'])) {
				list($streetName, $streetNumber) = explode(',', $properties['address']);
				$properties['address'] = [
					'streetName' => $streetName,
					'streetNumber' => $streetNumber,
					'locality' => $properties['city'],
					'zipCode' => $properties['zipCode'],
					'county' => $properties['state'],
					'country' => $properties['country'],
				];
			}
			if (!empty($properties['tag']) && array_key_exists('description', $properties['tag'])) {
				$properties['tag[description]'] = ArrayHelper::remove($properties['tag'], 'description');
			}
		} elseif ($className === $this->paymentModelClass) {
			$propertiesMap = self::$paymentPropertiesMap;
			if ($properties['orderType'] == self::PAYMENT_TYPE_RECURRING && isset($properties['intervalValue']) && isset($properties['intervalType'])) {
				$properties['recurringConfig'] = [
					'period' => $properties['intervalValue'],
					'cycle' => $properties['intervalType'],
				];
			}
		} elseif ($className === $this->transactionModelClass) {
			$propertiesMap = self::$transactionPropertiesMap;
		}

		foreach ($propertiesMap as $modelProp => $apiProp) {
			if (is_array($apiProp)) {
				foreach ($apiProp as $apiPrp) {
					if (array_key_exists($apiPrp, $properties)) {
						$config[$modelProp] = $properties[$modelProp];
					}
				}
			} elseif (array_key_exists($apiProp, $properties)) {
				$config[$modelProp] = $properties[$apiProp];
			}
		}

		return Yii::createObject($config);
	}

	/**
	 * Creates an array with properties mapped to this processor API requirements.
	 *
	 * @param string $className the class name to be used in order to identify the mapping.
	 * @param array $properties a list of properties to be mapped with this processor API requirements.
	 * @return array of properties mapped to this processor API requirements.
	 */
	protected function toApiArray($className, $properties)
	{
		$propertiesMap = [];
		$config = [];

		if ($className === $this->payerModelClass) {
			$propertiesMap = self::$payerPropertiesMap;
			if (isset($properties['name'])) {
				$properties['firstName'] = implode(' ', array_filter([
					$properties['name']['firstName'],
					$properties['name']['middleName'],
				]));
				$properties['lastName'] = $properties['name']['lastName'];

				unset($properties['name']);
			}
			if (isset($properties['address'])) {
				$properties['city'] = $properties['address']['locality'];
				$properties['zipCode'] = $properties['address']['zipCode'];
//				$properties['state'] = $properties['address']['county']; // TODO: Using COUNTY as STATE triggers an API validation error
				$properties['country'] = $properties['address']['country'];
				$properties['address'] = implode(', ', array_filter([
					$properties['address']['streetName'],
					$properties['address']['streetNumber'],
				]));
			}
			if (isset($properties['description'])) {
				$properties['metadata']['description'] = ArrayHelper::remove($properties, 'description');
			}
		} elseif ($className === $this->paymentModelClass) {
			$propertiesMap = self::$paymentPropertiesMap;
			if ($properties['type'] == self::PAYMENT_TYPE_RECURRING && is_array($properties['recurringConfig'])) {
				$properties['intervalValue'] = $properties['recurringConfig']['period'];
				$properties['intervalType'] = $properties['recurringConfig']['cycle'];
				unset($properties['recurringConfig']);
			}
		} elseif ($className === $this->transactionModelClass) {
			$propertiesMap = self::$transactionPropertiesMap;
		}

		foreach ($propertiesMap as $modelProp => $apiProp) {
			if (is_array($apiProp)) {
				foreach ($apiProp as $apiPrp) {
					if (array_key_exists($apiPrp, $properties)) {
						$config[$apiPrp] = $properties[$apiPrp];
					}
				}
			} elseif (array_key_exists($modelProp, $properties)) {
				$config[$propertiesMap[$modelProp]] = $properties[$modelProp];
			}
		}

		// TODO: Remove the below line: Using METADATA as TAG triggers an API validation because a tag is a single word.
		unset($config['tag']);

		return $config;
	}

	/**
	 * Makes an HTTP request to the API.
	 *
	 * @param string $url the API endpoint.
	 * @param string $method the HTTP method which is GET by default.
	 * @param array $data the request body parameters if is the case.
	 * @return mixed
	 */
	protected function makeRequest($url, $method = 'GET', $data = [])
	{
		try {
			$client = new Client([
				'transport' => 'yii\httpclient\CurlTransport',
				'baseUrl' => $this->baseUrl,
				'requestConfig' => [
					'format' => Client::FORMAT_RAW_URLENCODED,
				],
				'responseConfig' => [
					'format' => Client::FORMAT_JSON,
				],
			]);

			// Force siteId for all requests
			if (!isset($data['siteId'])) {
				$data['siteId'] = $this->siteId;
			}

			$request = $client->createRequest()
				->addHeaders(["Authorization" => "Bearer {$this->apiKey}"])
				->setMethod($method)
				->setUrl($url)
				->setData($data);

			if (!$this->beforeSend($request)) {
				return false;
			}

			/** @var \yii\httpclient\Response $response */
			$response = $request->send();

			$this->afterSend($request, $response, $response->isOk);

			if ($response->isOk) {
				return isset($response->data['data']) ? $response->data['data'] : $response->data;
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Recursively sorts an array by its keys.
	 *
	 * @param array $data
	 */
	public static function recursiveKeySort(array &$data)
	{
		ksort($data, SORT_STRING);
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				self::recursiveKeySort($data[$key]);
			}
		}
	}

	/**
	 * Creates a Twispay jsonRequest.
	 *
	 * @param array $data
	 * @param string|int $apiKey
	 */
	public static function createJsonRequest(array $data)
	{
		return base64_encode(json_encode($data));
	}

	/**
	 * Creates a Twispay checksum.
	 *
	 * @param array $data The order parameters.
	 * @param string|int $secretKey The secret key (from Twispay).
	 * @return string
	 */
	public static function createChecksum(array $data, $secretKey)
	{
		return base64_encode(hash_hmac('sha512', json_encode($data), $secretKey, true));
	}

	/**
	 * Decrypt the IPN response from Twispay.
	 *
	 * @param string|int $encryptedIpnResponse
	 * @param string|int $secretKey The secret key (from Twispay).
	 * @return array
	 */
	public static function decryptIpnResponse($encryptedIpnResponse, $secretKey)
	{
		// Get the IV and the encrypted data
		$encryptedParts = explode(',', $encryptedIpnResponse, 2);
		$iv = base64_decode($encryptedParts[0]);
		$encryptedData = base64_decode($encryptedParts[1]);

		// Decrypt the encrypted data
		$decryptedIpnResponse = openssl_decrypt($encryptedData, 'aes-256-cbc', $secretKey, OPENSSL_RAW_DATA, $iv);

		// JSON decode the decrypted data
		return json_decode($decryptedIpnResponse, true, 4);
	}
}
