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

namespace Tool\Form\FormField;

use \CarteBlanche\App\Router;
use \Tool\Form\AbstractFormField;

class ButtonField extends AbstractFormField
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
		if (is_null($type)) $type = 'button';
		
		$value = $this->getValue();
		if (is_null($value)) $value = $this->getOption('value');

		$action = $this->getOption('action');
		if (is_null($action)) $action = $value;

		$str = '<button'
			.' type="'.$type.'"'
			.' name="'.$this->getName().'"'
			.' value="'.$value.'"'
			.\Library\Helper\Html::parseAttributes( $this->getAttributes() )
			.'>'
			.$action
			.'</button>';
		return $str;
	}
	
}

// Endfile