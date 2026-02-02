<?php

namespace tws\payment\models;

use yii\base\Model;

/**
 * This class represents the transaction model.
 *
 * @property string|int $id
 * @property string|int $payerId
 * @property string|int $cardId
 * @property string|int $paymentId
 * @property string $ipAddress
 * @property double $amount
 * @property string $currency
 * @property string $description
 * @property string|int $type
 * @property string|int $status
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class Transaction extends Model
{
	/**
	 * @var string|int the transaction ID.
	 */
	public $id;

	/**
	 * @var string|int the transaction payer ID.
	 */
	public $payerId;

	/**
	 * @var string|int the transaction card ID.
	 */
	public $cardId;

	/**
	 * @var string|int the transaction payment ID.
	 */
	public $paymentId;

	/**
	 * @var string the transaction IP address.
	 */
	public $ipAddress;

	/**
	 * @var string the transaction amount.
	 */
	public $amount;

	/**
	 * @var string the transaction currency.
	 */
	public $currency;

	/**
	 * @var string the transaction description.
	 */
	public $description;

	/**
	 * @var string|int the transaction type.
	 */
	public $type;

	/**
	 * @var string|int the transaction status.
	 */
	public $status;
}
