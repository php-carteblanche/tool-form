<?php
/**
 * CarteBlanche - PHP framework package - Form tool
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Tool\Form\FormField;

use \CarteBlanche\App\Router;
use \Tool\Form\AbstractFormField;

class InputField extends AbstractFormField
{

	/**
	 * Build the field HTML string in the sub-class
	 */
	public function treatField()
	{
		return null;
	}

	/**
	 * Treat form values setting errors in the sub-class if so
	 */
	public function buildField()
	{
		$type = $this->getOption('type');
		if (is_null($type)) $type = 'text';

		$str = '<input'
			.' type="'.$type.'"'
			.' name="'.$this->getName().'"'
			.' value="'.$this->getValue().'"'
			.\Library\Helper\Html::parseAttributes( $this->getAttributes() )
			.' />';
		return $str;
	}
	
}

// Endfile