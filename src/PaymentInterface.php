<?php

namespace tws\payment;

use tws\payment\processors\ProcessorInterface;

/**
 * PaymentInterface is the interface that should be implemented by Payment classes.
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
interface PaymentInterface
{
	/**
	 * Creates an instance of the specified payment processor.
	 * @param string $processor the payment processor.
	 * @return ProcessorInterface the payment processor instance.
	 */
	public function via($processor);
}
