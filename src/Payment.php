<?php

namespace tws\payment;

use tws\payment\processors\ProcessorInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * This class implements the payment functionality based on different payment processors.
 *
 * To use Payment, you should configure it in the application configuration like the following:
 *
 * ```php
 * [
 *     'components' => [
 *         'payment' => [
 *             'class' => 'tws\payment\Payment',
 *             'processors' => [
 *             		'stripe' => [
 * 										'class' => 'tws\payment\processors\Stripe',
 *                 		'baseUrl' => 'https://base.api.url/v1',
 *                 		'privateKey' => 'API_PRIVATE_KEY',
 *                 		'publicKey' => 'API_PUBLIC_KEY',
 * 								],
 *             		'twispay' => [
 * 										'class' => 'tws\payment\processors\Twispay',
 *                 		'baseUrl' => 'https://base.api.url/v1',
 *                 		'apiKey' => 'API_KEY',
 *                 		'siteId' => 'API_SITE_ID',
 * 								],
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ],
 * ```
 *
 * To create a payment processor instance, you may use the following code:
 *
 * ```php
 * Yii::$app->payment->via(PROCESSOR_KEY);
 * ```
 *
 * @property array|ProcessorInterface[] $processors This property is read-only.
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class Payment extends Component implements PaymentInterface
{
	/**
	 * @var array a list of processors with their configuration.
	 */
	private $_processors = [];


	/**
	 * Gets the processors list.
	 * @return array
	 */
	public function getProcessors()
	{
		return $this->_processors;
	}

	/**
	 * Sets the processor configuration.
	 * @param array|ProcessorInterface[] $processors a list of processors configuration or the instances themselves.
	 * @throws InvalidConfigException on invalid argument.
	 */
	public function setProcessors($processors)
	{
		if (!is_array($processors) || !ArrayHelper::isAssociative($processors)) {
			throw new InvalidConfigException('"' . get_class($this) . '::processors" should be an associative array');
		}
		$this->_processors = $processors;
	}

	/**
	 * Adds a new processor to the list.
	 * @param string|int $key the key of the processor.
	 * @param array|ProcessorInterface $processor the processor configuration or the instance itself.
	 * @throws InvalidConfigException on invalid arguments.
	 */
	public function addProcessor($key, $processor)
	{
		if (empty($key)) {
			throw new InvalidConfigException('The key for each "' . get_class($this) . '"::processors is mandatory.');
		}
		if (!is_array($processor) && !is_object($processor)) {
			throw new InvalidConfigException('"' . get_class($this) . '::processors" should be either object or array, "' . gettype($processor) . '" given.');
		}
		$this->_processors[$key] = $processor;
	}

	/**
	 * Creates a payment processor instance by its array configuration.
	 * @param array $config processor configuration.
	 * @throws \yii\base\InvalidConfigException on invalid processor configuration.
	 * @return ProcessorInterface processor instance.
	 */
	protected function createProcessor(array $config)
	{
		/** @var ProcessorInterface $processor */
		$processor = Yii::createObject($config);

		return $processor;
	}

	/**
	 * @inheritdoc
	 * @throws InvalidConfigException on invalid processor configuration.
	 */
	public function via($processor)
	{
		return $this->createProcessor($this->_processors[$processor]);
	}
}
