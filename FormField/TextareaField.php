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

class TextareaField extends AbstractFormField
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
		$str = '<textarea'
			.' name="'.$this->getName().'"'
			.\Library\Helper\Html::parseAttributes( $this->getAttributes() )
			.'>'
			.$this->getValue()
			.'</textarea>';
		return $str;
	}
	
}

// Endfile