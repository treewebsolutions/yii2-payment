<?php

namespace tws\payment\processors;

use yii\base\Event;

/**
 * ProcessorEvent represents the event parameter used for events triggered by [[BaseProcessor]].
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class ProcessorEvent extends Event
{
	/**
	 * @var yii\httpclient\Request the HTTP Client request being send.
	 */
	public $request;

	/**
	 * @var yii\httpclient\Response the HTTP Client response.
	 */
	public $response;

	/**
	 * @var bool if response is successful.
	 */
	public $isSuccessful;

	/**
	 * @var bool whether to continue sending the request. Event handlers of
	 * [[\tws\payment\processors\BaseProcessor::EVENT_BEFORE_SEND]] may set this property to decide whether
	 * to continue send or not.
	 */
	public $isValid = true;
}
