<?php
/**
 * CarteBlanche - PHP framework package - Form tool
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/carte-blanche>
 */

namespace Tool\Form\FormField;

use \CarteBlanche\App\Router;
use \Tool\Form\AbstractFormField;

class IntegerField extends AbstractFormField
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
		$type = isset($this->options['hidden']) && $this->options['hidden']==true ? 'hidden' : 'text';

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