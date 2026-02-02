<?php

namespace tws\payment\processors;

use tws\payment\models\Payer;
use tws\payment\models\Payment;
use tws\payment\models\Transaction;

/**
 * ProcessorInterface is the interface that should be implemented by payment processor classes.
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
interface ProcessorInterface
{
	/**
	 * Finds the payment processor payer by ID.
	 * @param string|int $id
	 * @return Payer|null
	 */
	public function findPayer($id);

	/**
	 * Creates a payment processor payer.
	 * @param array $payer
	 * @return Payer|bool
	 */
	public function createPayer($payer);

	/**
	 * Updates a payment processor payer.
	 * @param string|int $id
	 * @param array $payer
	 * @return Payer|bool
	 */
	public function updatePayer($id, $payer);

	/**
	 * Deletes the payment processor payer.
	 * @param string|int|array $id the single or multiple payer IDs to be deleted.
	 * @return bool|int the boolean operation result or the number of records affected.
	 */
	public function deletePayer($id);

	/**
	 * Find the payment processor payment by ID.
	 * @param string|int $id
	 * @return Payment|null
	 */
	public function findPayment($id);

	/**
	 * Creates a payment processor payment.
	 * @param array $payment
	 * @return Payment|bool
	 */
	public function createPayment($payment);

	/**
	 * Updates a payment processor payment.
	 * @param string|int $id
	 * @param array $payment
	 * @return Payment|bool
	 */
	public function updatePayment($id, $payment);

	/**
	 * Cancels a recurring payment processor payment.
	 * @param string|int|array $id the single or multiple recurring payment IDs to be cancelled.
	 * @return bool|int the boolean operation result or the number of records affected.
	 */
	public function cancelRecurringPayment($id);

	/**
	 * Find the payment processor transaction by ID.
	 * @param array|string|int $id
	 * @return Transaction|null
	 */
	public function findTransaction($id);

	/**
	 * Find the payment processor transactions by specified filters.
	 * @param array $filters
	 * @return Transaction[]|array
	 */
	public function findTransactions($filters);
}
