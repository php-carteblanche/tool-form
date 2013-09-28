<?php
/**
 * CarteBlanche - PHP framework package - AutoObject bundle
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
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