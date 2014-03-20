<?php
/**
 * This file is part of the CarteBlanche PHP framework
 * (c) Pierre Cassat and contributors
 * 
 * Sources <http://github.com/php-carteblanche/form-tool>
 *
 * License Apache-2.0
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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