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