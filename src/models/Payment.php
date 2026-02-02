<?php

namespace tws\payment\models;

use yii\base\Model;

/**
 * This class represents the payment model.
 *
 * @property string|int $id
 * @property string|int $customId
 * @property string|int $payerId
 * @property string|int $cardId
 * @property string $ipAddress
 * @property double $amount
 * @property string $currency
 * @property string $description
 * @property array $recurringConfig
 * @property string|int $type
 * @property string|int $status
 * @property array $metadata
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class Payment extends Model
{
	/**
	 * @var string|int the payment ID.
	 */
	public $id;

	/**
	 * @var string|int the payment custom ID.
	 */
	public $customId;

	/**
	 * @var string|int the payment payer ID.
	 */
	public $payerId;

	/**
	 * @var string|int the payment card ID.
	 */
	public $cardId;

	/**
	 * @var string the payment IP address.
	 */
	public $ipAddress;

	/**
	 * @var string the payment amount.
	 */
	public $amount;

	/**
	 * @var string the payment currency.
	 */
	public $currency;

	/**
	 * @var string the payment description.
	 */
	public $description;

	/**
	 * @var array the payment recurring configuration.
	 *
	 * This should have the following format:
	 *
	 * ```php
	 * [
	 *		'period' => 1,
	 *		'cycle' => 'month',
	 * ]
	 * ```
	 */
	public $recurringConfig;

	/**
	 * @var string|int the payment type.
	 */
	public $type;

	/**
	 * @var string|int the payment status.
	 */
	public $status;

	/**
	 * @var string the payment metadata.
	 */
	public $metadata;
}
