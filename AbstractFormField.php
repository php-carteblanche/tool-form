<?php
/**
 * CarteBlanche - PHP framework package - Form tool
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License Apache-2.0 <http://www.apache.org/licenses/LICENSE-2.0.html>
 * Sources <http://github.com/php-carteblanche/carteblanche>
 */

namespace Tool\Form;

use \CarteBlanche\CarteBlanche;
use \CarteBlanche\App\Kernel;
use \Library\Helper\Html;

abstract class AbstractFormField
{
	var $field;

	protected $label=null;
	protected $name=null;
	protected $value=null;
	protected $errors=array();
	protected $options=array();
	protected $attributes=array();

	static $html5_allowed = true;

	public function __construct( $name, $value, $label, $options=null, $attributes=null )
	{
		$this->name = $name;
		$this->value = $value;
		$this->label = $label;
		if (!is_null($options))
			$this->options = array_merge($this->options, $options);
		if (!is_null($attributes))
			$this->attributes = array_merge($this->attributes, $attributes);
	}

	public function __toString()
	{
		$this->build();
		return $this->field;
	}

// ---------------
// GETTERS
// ---------------

	public function getOption( $name )
	{
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getAttribute( $name )
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function getErrorsAsString()
	{
		return is_array($this->errors) ? join(' ', $this->errors) : $this->errors;
	}

	public function hasErrors()
	{
		return !empty($this->errors);
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function prepend()
	{
		return isset($this->options['html_before']) ? $this->options['html_before'] : '';
	}

	public function append()
	{
		return isset($this->options['html_after']) ? $this->options['html_after'] : '';
	}

// ---------------
// Rendering & treatments
// ---------------

	/**
	 * Build the field HTML string
	 */
	public function build()
	{
		$this->field = $this->prepend()
			.'<div class="field">'
			.( $this->getLabel() ? '<label for="'.$this->getName().'">'.$this->getLabel().'</label>' : '' )
			.( $this->hasErrors() ? '<span class="error">'.$this->getErrorsAsString().'</span>' : '' )
			.$this->buildField()
			.$this->getOption('comment')
			.'</div>'
			.$this->append();

		return $this->field;
	}

	/**
	 * Treat form values setting errors if so
	 */
	public function treat()
	{
		$this->value = CarteBlanche::getContainer()->get('request')->getPost($this->getName(), '');
		$validation_rules = $this->getOption('validation_rules');

		// required field
		if ($this->getAttribute('required') && $this->getAttribute('required')=='true' && $this->getValue()=='')
		{
			$this->errors[] = 'This field is required';
		}
		
		// numeric fields
		elseif ($this->getOption('numeric') && $this->getOption('numeric')==true && 
			($this->getValue()!='' && !is_numeric($this->getValue()))
		){
			$this->errors[] = 'This field only accepts numeric values';
		}

		// validation
		elseif (!empty($validation_rules)) 
		{
			foreach($validation_rules as $fct) 
			{

//echo '<br />searching validation function "'.$fct.'"';
//echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;treating validation "'.$fct.'" on value "'.$this->getValue().'"';
					$callback = new \CarteBlanche\Library\Callback(
						$this->getValue(), array($fct), 'boolean'
					);
					$response = $callback->getResult();

//echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;=> '.var_export($response,1);

					if ($response!==true) 
					{
						$this->errors[] = "This field must pass test function '$fct'";
						break;
					}

			}

		}

		$val = $this->treatField();
		if (!empty($val)) return $val;
		return $this->getValue();
	}

// ---------------
// Interface
// ---------------

	/**
	 * Build the field HTML string in the sub-class
	 */
	abstract public function buildField();

	/**
	 * Treat form values setting errors in the sub-class if so
	 */
	abstract public function treatField();

}

// Endfile