<?php

namespace tws\payment\processors;

use Yii;
use yii\base\BaseObject;
use yii\base\Component;

/**
 * BaseProcessor serves as a base class that implements the basic functions required by [[ProcessorInterface]].
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
abstract class BaseProcessor extends Component implements ProcessorInterface
{
	/**
	 * @event ProcessorEvent an event raised right before send request.
	 * You may set [[ProcessorEvent::isValid]] to be false to cancel the request.
	 */
	const EVENT_BEFORE_SEND = 'beforeSend';

	/**
	 * @event ProcessorEvent an event raised right after request send.
	 */
	const EVENT_AFTER_SEND = 'afterSend';

	/**
	 * @var string the Payer model class used by payment processors.
	 */
	public $payerModelClass = 'tws\payment\models\Payer';

	/**
	 * @var string the Payment model class used by payment processors.
	 */
	public $paymentModelClass = 'tws\payment\models\Payment';

	/**
	 * @var string the Transaction model class used by payment processors.
	 */
	public $transactionModelClass = 'tws\payment\models\Transaction';


	/**
	 * This method is invoked right before request send.
	 * You may override this method to do last-minute preparation for the data.
	 * If you override this method, please make sure you call the parent implementation first.
	 * @param yii\httpclient\Request $request
	 * @return bool whether to continue sending the request.
	 */
	public function beforeSend($request)
	{
		$event = new ProcessorEvent(['request' => $request]);
		$this->trigger(self::EVENT_BEFORE_SEND, $event);

		return $event->isValid;
	}

	/**
	 * This method is invoked right after request was send.
	 * You may override this method to do some postprocessing or logging based on request send status.
	 * If you override this method, please make sure you call the parent implementation first.
	 * @param yii\httpclient\Request $request
	 * @param yii\httpclient\Response $response
	 * @param bool $isSuccessful
	 */
	public function afterSend($request, $response, $isSuccessful)
	{
		$event = new ProcessorEvent([
			'request' => $request,
			'response' => $response,
			'isSuccessful' => $isSuccessful,
		]);
		$this->trigger(self::EVENT_AFTER_SEND, $event);
	}
}
