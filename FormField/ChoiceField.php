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

class CheckboxField extends AbstractFormField
{

	public function treatField()
	{
		return null;
	}

	public function buildField()
	{
		$str = '<input'
			.' type="checkbox"'
			.' name="'.$this->getName().'"'
			.' value="'.$this->getValue().'"'
			.\Library\Helper\Html::parseAttributes( $this->getAttributes() )
			.' />';
		return $str;
	}
	
}

// Endfile