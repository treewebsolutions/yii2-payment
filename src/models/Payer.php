<?php

namespace tws\payment\models;

use yii\base\Model;

/**
 * This class represents the payer model.
 *
 * @property string|int $id
 * @property string|int $customId
 * @property array $name
 * @property string $fullName This property is read-only.
 * @property string $email
 * @property string $phone
 * @property array $address
 * @property string $fullAddress This property is read-only.
 * @property string $description
 * @property array $metadata
 *
 * @author Tree Web Solutions <treewebsolutions.com@gmail.com>
 */
class Payer extends Model
{
	/**
	 * @var string|int the payer id.
	 */
	public $id;

	/**
	 * @var string|int the payer custom ID.
	 */
	public $customId;

	/**
	 * @var array the payer name.
	 *
	 * This should have the following format:
	 *
	 * ```php
	 * [
	 *		'firstName' => '',
	 *		'middleName' => '',
	 *		'lastName' => '',
	 * ]
	 * ```
	 */
	public $name = [];

	/**
	 * @var string the payer email.
	 */
	public $email;

	/**
	 * @var string the payer phone.
	 */
	public $phone;

	/**
	 * @var array the payer address.
	 *
	 * This should have the following format:
	 *
	 * ```php
	 * [
	 *		'streetName' => '',
	 *		'streetNumber' => '',
	 *		'locality' => '',
	 *		'zipCode' => '',
	 *		'county' => '',
	 *		'country' => '',
	 * ]
	 * ```
	 */
	public $address = [];

	/**
	 * @var string the payer description.
	 */
	public $description;

	/**
	 * @var array the payer key-value pairs metadata.
	 */
	public $metadata = [];


	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		$attributes = parent::attributes();
		$attributes[] = 'fullName';
		$attributes[] = 'fullAddress';

		return $attributes;
	}

	/**
	 * Gets the full name by concatenating the name parts.
	 * @return string the full name.
	 */
	public function getFullName()
	{
		$nameComponents = ['firstName', 'middleName', 'lastName'];
		return implode(' ', array_filter(array_replace(array_fill_keys($nameComponents, null), $this->name)));
	}

	/**
	 * Gets the full address by concatenating the address parts.
	 * @param string $separator a string that separates the address parts.
	 * @return string the full name.
	 */
	public function getFullAddress($separator = ', ')
	{
		$addressComponents = ['streetName', 'streetNumber', 'locality', 'zipCode', 'county', 'country'];
		return implode($separator, array_filter(array_replace(array_fill_keys($addressComponents, null), $this->address)));
	}
}
