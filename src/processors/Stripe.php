<?php

namespace tws\payment\processors;

use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * This class is a wrapper over the Stripe payment processor REST API.
 *
 * @link https://stripe.com/docs/api
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class Stripe extends BaseProcessor
{
	const PAYMENT_TYPE_PURCHASE = 'purchase';
	const PAYMENT_TYPE_RECURRING = 'recurring';

	const PAYMENT_STATUS_IN_PROGRESS = 'pending';
	const PAYMENT_STATUS_SUCCEEDED = 'succeeded';
	const PAYMENT_STATUS_FAILED = 'failed';

	const TRANSACTION_STATUS_SUCCEEDED = 'succeeded';
	const TRANSACTION_STATUS_FAILED = 'failed';

	/**
	 * @var string The API base URL.
	 */
	public $baseUrl;

	/**
	 * @var string|int The API private key.
	 */
	public $privateKey;

	/**
	 * @var string|int The API public key.
	 */
	public $publicKey;

	/**
	 * @var array The mapping of Payer model class properties to this processor API.
	 * The key of this array represents the object property.
	 * The value of this array represents the API property.
	 */
	protected static $payerPropertiesMap = [
		'id' => 'id',
		'customId' => 'metadata[customId]',
		'name' => 'name',
		'email' => 'email',
		'phone' => 'phone',
		'address' => 'address',
		'description' => 'description',
		'metadata' => 'metadata',
	];

	/**
	 * @var array The mapping of Payment model class properties to this processor API.
	 * The key of this array represents the object property.
	 * The value of this array represents the API property.
	 */
	protected static $paymentPropertiesMap = [
		'id' => 'id',
		'customId' => 'metadata[customId]',
		'payerId' => 'customer',
		'cardId' => 'source',
		'ipAddress' => 'metadata[ipAddress]',
		'amount' => 'amount',
		'currency' => 'currency',
		'description' => 'description',
		'recurringConfig' => ['intervalValue', 'intervalType'],
		'type' => 'paid',
		'status' => 'status',
		'metadata' => 'metadata',
	];

	/**
	 * @var array The mapping of Transaction model class properties to this processor API.
	 * The key of this array represents the object property.
	 * The value of this array represents the API property.
	 */
	protected static $transactionPropertiesMap = [
		'id' => 'id',
		// TODO: map this properties to the Stripe API requirements
		'payerId' => '',
		'cardId' => 'card',
		'paymentId' => '',
		'ipAddress' => '',
		'amount' => 'amount',
		'currency' => 'currency',
		'description' => '',
		'type' => 'type',
		'status' => '',
	];


	//region PAYER
	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/customers/retrieve
	 */
	public function findPayer($id)
	{
		try {
			$payer = $this->makeRequest("/customers/{$id}","GET");
			return $this->toObject($this->payerModelClass, $payer);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/customers/create
	 */
	public function createPayer($payer)
	{
		try {
			$payer = $this->toApiArray($this->payerModelClass, $payer);
			$payer = $this->makeRequest("/customers","POST", $payer);
			return $this->toObject($this->payerModelClass, $payer);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/customers/update
	 */
	public function updatePayer($id, $payer)
	{
		try {
			$payer = $this->toApiArray($this->payerModelClass, $payer);
			$payer = $this->makeRequest("/customers/{$id}","POST", $payer);
			return $this->toObject($this->payerModelClass, $payer);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/customers/delete
	 */
	public function deletePayer($id)
	{
		if (is_array($id)) {
			$successCount = 0;
			foreach ($id as $payerId) {
				$result = $this->makeRequest("/customers/{$payerId}","DELETE");
				if ($result['deleted']) {
					$successCount++;
				}
			}
			return $successCount;
		}
		$result = $this->makeRequest("/customers/{$id}","DELETE");
		return $result['deleted'];
	}
	//endregion PAYER

	//region PAYMENT
	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/charges/retrieve
	 */
	public function findPayment($id)
	{
		try {
			$payment = $this->makeRequest("/charges/{$id}","GET");
			$payment['amount'] /= 100;
			return $this->toObject($this->paymentModelClass, $payment);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/charges/create
	 */
	public function createPayment($payment)
	{
		try {
			$payment = $this->toApiArray($this->paymentModelClass, $payment);
			$payment['amount'] *= 100;
			$payment = $this->makeRequest("/charges","POST", $payment);
			$payment['amount'] /= 100;
			return $this->toObject($this->paymentModelClass, $payment);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/charges/update
	 */
	public function updatePayment($id, $payment)
	{
		try {
			$payment = $this->toApiArray($this->paymentModelClass, $payment);
			$payment['amount'] *= 100;
			$payment = $this->makeRequest("/charges/{$id}","POST", $payment);
			$payment['amount'] /= 100;
			return $this->toObject($this->paymentModelClass, $payment);
		} catch (\Exception $e) {
			return false;
		}
	}

	// TODO: review this method since is not done yet.
	/**
	 * @inheritdoc
	 * @link ???
	 */
	public function cancelRecurringPayment($id)
	{
		if (is_array($id)) {
			$successCount = 0;
			foreach ($id as $subscriptionId) {
				$response = $this->makeRequest("/subscriptions/{$subscriptionId}","POST");
				if ($response['code'] == 200) {
					$successCount++;
				}
			}
			return $successCount;
		}
		$response = $this->makeRequest("/subscriptions/{$id}","POST");
		return $response['code'] == 200;
	}
	//endregion PAYMENT

	//region TRANSACTION
	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/issuing/transactions/retrieve
	 */
	public function findTransaction($id)
	{
		try {
			$transaction = $this->makeRequest("/transactions/{$id}","GET");
			return $this->toObject($this->transactionModelClass, $transaction);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 * @link https://stripe.com/docs/api/issuing/transactions/list
	 */
	public function findTransactions($filters)
	{
		try {
			if (isset($filters['cardId'])) {
				$filters['card'] = ArrayHelper::remove($filters, 'cardId');
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
			if (!empty($properties['metadata']) && array_key_exists('customId', $properties['metadata'])) {
				$properties['metadata[customId]'] = ArrayHelper::remove($properties['metadata'], 'customId');
			}
			if (isset($properties['name'])) {
				list($firstName, $middleName, $lastName) = explode(' ', $properties['name']);
				$properties['name'] = [
					'firstName' => $firstName,
					'middleName' => empty($lastName) ? null : $middleName,
					'lastName' => empty($lastName) ? $middleName : $lastName,
				];
			}
			if (isset($properties['address'])) {
				list($streetName, $streetNumber) = explode(',', $properties['address']['line1']);
				$properties['address'] = [
					'streetName' => $streetName,
					'streetNumber' => $streetNumber,
					'locality' => $properties['address']['city'],
					'zipCode' => $properties['address']['postal_code'],
					'county' => $properties['address']['state'],
					'country' => $properties['address']['country'],
				];
			}
		} elseif ($className === $this->paymentModelClass) {
			$propertiesMap = self::$paymentPropertiesMap;
			if (!empty($properties['metadata'])) {
				if (array_key_exists('customId', $properties['metadata'])) {
					$properties['metadata[customId]'] = ArrayHelper::remove($properties['metadata'], 'customId');
				}
				if (array_key_exists('ipAddress', $properties['metadata'])) {
					$properties['metadata[ipAddress]'] = ArrayHelper::remove($properties['metadata'], 'ipAddress');
				}
			}
		} elseif ($className === $this->transactionModelClass) {
			$propertiesMap = self::$transactionPropertiesMap;
		}

		foreach ($propertiesMap as $modelProp => $apiProp) {
			if (array_key_exists($apiProp, $properties)) {
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
			if (isset($properties['customId'])) {
				$properties['metadata']['customId'] = ArrayHelper::remove($properties, 'customId');
			}
			if (isset($properties['name'])) {
				$properties['name'] = implode(' ', array_filter([
					$properties['name']['firstName'],
					$properties['name']['middleName'],
					$properties['name']['lastName'],
				]));
			}
			if (isset($properties['address'])) {
				$properties['address'] = [
					'line1' => implode(', ', array_filter([
						$properties['address']['streetName'],
						$properties['address']['streetNumber'],
					])),
					'city' => $properties['address']['locality'],
					'postal_code' => $properties['address']['zipCode'],
					'state' => $properties['address']['county'],
					'country' => $properties['address']['country'],
				];
			}
		} elseif ($className === $this->paymentModelClass) {
			$propertiesMap = self::$paymentPropertiesMap;
			if (isset($properties['customId'])) {
				$properties['metadata']['customId'] = ArrayHelper::remove($properties, 'customId');
			}
			if (isset($properties['ipAddress'])) {
				$properties['metadata']['ipAddress'] = ArrayHelper::remove($properties, 'ipAddress');
			}
		} elseif ($className === $this->transactionModelClass) {
			$propertiesMap = self::$transactionPropertiesMap;
		}

		foreach ($propertiesMap as $modelProp => $apiProp) {
			if (array_key_exists($modelProp, $properties)) {
				$config[$propertiesMap[$modelProp]] = $properties[$modelProp];
			}
		}

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

			$request = $client->createRequest()
				->addHeaders(["Authorization" => "Bearer {$this->privateKey}"])
				->setMethod($method)
				->setUrl($url)
				->setData($data);

			if (!$this->beforeSend($request)) {
				return false;
			}

			/** @var \yii\httpclient\Response $response */
			$response = $request->send();

			$this->afterSend($request, $response, $response->isOk);

			return $response->isOk ? $response->data : false;
		} catch (\Exception $e) {
			return false;
		}
	}
}
