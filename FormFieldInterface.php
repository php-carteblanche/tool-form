<?php
/**
 * CarteBlanche - PHP framework package - AutoObject bundle
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Tool\Form;

/**
 */
interface FormFieldInterface
{

	public function getData();

	public function getName();

	public function getValues();

	public function getType();

	public function getTypeHtml5();

	public function getDefault();

	public function getValidations();

	public function getValidationEntry($stack_name);

	public function getCallbacks();

	public function getCallbacksStack($stack_name);

	public function getComments();
	
}

// Endfile