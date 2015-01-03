<?php
/**
 * This file is part of the CarteBlanche PHP framework.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * License Apache-2.0 <http://github.com/php-carteblanche/carteblanche/blob/master/LICENSE>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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