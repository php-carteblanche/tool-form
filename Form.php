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

namespace Tool;

use \CarteBlanche\CarteBlanche;
use \CarteBlanche\App\Kernel;
use \CarteBlanche\App\FrontController;
use \CarteBlanche\App\Router;
use \CarteBlanche\Abstracts\AbstractTool;

class Form extends AbstractTool
{

	var $view='form.htm';

	var $form_id;
	var $fields;
	var $values;
	var $errors;
	var $multipart=false;

	protected $fields_stack=array();
	protected $comment_mask;
	protected $default_mask;
	protected $field_div_mask;
	protected $label_mask;
	protected $markdown_flag;
	protected $required_flag;

	static $html5_allowed = true;

	public function buildViewParams()
	{
		$this->comment_mask = '<span class="comment">%s</span>';
		$this->field_div_mask = "\n".'<div class="field">%s</div>';
		$this->label_mask = "\n".'<label for="%s">%s</label>';
		$this->default_mask = sprintf($this->comment_mask, 'Default value is <em>%s</em>');

		$this->markdown_flag = sprintf($this->comment_mask, '<a href="http://daringfireball.net/projects/markdown/syntax" title="Overview of this syntax on daringfireball.net">Markdown</a> syntax allowed.');
		$this->required_flag = '<span title="Required field" class="required_field"> *</span>';

		if (empty($this->form_id)) 
			$this->form_id = uniqid();
		if (empty($this->fields)) 
			trigger_error( "Trying to build an empty form!", E_USER_WARNING);

		return array(
			'form_id'=>$this->form_id,
			'fields'=>$this->fields,
			'values'=>$this->values,
			'errors'=>$this->errors,
			'fields_stack'=>$this->fields_stack,
			'formfields_ctt'=>$this->getFormContent(),
			'multipart'=>$this->multipart,
		);
	}

// ------------------------
// TREATMENTS
// ------------------------

	public function treatPost()
	{
		if (empty($this->fields)) return array(array(), array());
		if (false===getContainer()->get('request')->isPost()) return array(array(), array());
		if (empty($this->fields_stack)) $this->buildFieldsStack();

		$_posted = CarteBlanche::getContainer()->get('request')->getData();
		if (empty($this->values)) $this->values = array();
		if (empty($this->errors)) $this->errors = array();

		$ok_posted=false;
		foreach($_posted as $pst_var=>$pst_val)
			if (!empty($pst_val)) $ok_posted=true;
		if (!$ok_posted) {
			$this->errors['form'] = 'No values received!';
		} else {
			foreach ($this->fields_stack as $_field) {
				$this->values[$_field->getName()] = $_field->treat();
				if ($_field->hasErrors())
					$this->errors[$_field->getName()] = true;
			}
		}
		return array($this->values, $this->errors);
	}

	public function getFormContent()
	{
		if (empty($this->fields_stack)) $this->buildFieldsStack();

		$formfields_ctt = '';
		foreach($this->fields_stack as $_field)
			$formfields_ctt .= "\n".$_field->__toString();

		return $formfields_ctt;
	}

// ------------------------
// OUTPUT
// ------------------------

	public function buildFieldsStack()
	{
//echo '<pre>';
		foreach ($this->fields as $field) {
//echo '<br />field name : '.$field->getName();
//echo '<br />field : '.var_export($field,1);
			$field_name = $field->getName();

			if (in_array($field->getType(), array('button', 'submit', 'reset'))) {
				$this->setField( new \Tool\Form\FormField\ButtonField(
					$field_name, 
					!empty($this->values['value']) ? $this->values['value'] : null,
					!empty($this->values['label']) ? $this->values['label'] : 
					    	str_replace('_', ' ', ucfirst($field_name)),
					$field->getData()
				));
			}

			elseif (in_array($field_name, array('created_at','updated_at'))) continue;

			elseif ($field_name=='id') {
				$this->setField( new \Tool\Form\FormField\IntegerField(
					'id', 
					!empty($this->values['id']) ? $this->values['id'] : null,
					null,
					array( 'hidden'=>true )
				));
			}

			elseif (in_array($field->getType(), array('string', 'numeric'))) {
				// options
				$field_opts = array(
					'validation_rules'=>$field->getCallbacksStack('validation'),
					'type'=> self::$html5_allowed ? $field->getTypeHtml5() : 'text',
				);
				$default = $field->getDefault();
				if (!empty($default) && is_string($default))
					$field_opts['comment'] = sprintf($this->default_mask, $default);
				unset($default);

				// attributes
				$field_attrs = array(
					'id'=>$field_name
				);
				if (!$field->isNullable())
					$field_attrs['required'] = 'true';
				if ($field->getValidationEntry('maxlength'))
					$field_attrs['maxlength'] = $field->getValidationEntry('maxlength');

				// datetime
				if (in_array($field->getTypeHtml5(), array('datetime', 'time', 'date'))) {
					$field_opts['html_before'] = "\n".FrontController::getInstance()->view($this->views_dir.'calendar.htm');
					$field_attrs['class'] = $field->getTypeHtml5()."_field";
					$field_attrs['onclick'] = "ds_sh(this);";
				}

				// datetime
				if ('numeric'==$field->getType()) {
					$field_opts['numeric'] = true;
					$field_attrs['class'] = "numeric_field";
				}

				// the form field
				$this->setField( new \Tool\Form\FormField\InputField(
					$field_name, 
					!empty($this->values[$field_name]) ? $this->values[$field_name] : '',
					str_replace('_', ' ', ucfirst($field_name))
						.(!$field->isNullable() ? $this->required_flag : ''),
					$field_opts, $field_attrs
				));
			}

			elseif ('text'==$field->getType()) {
				switch($field->getIntType()){
					case 'longtext': $cols=80; $rows=30; break;
					case 'mediumtext': $cols=30; $rows=8; break;
					default: $cols=40; $rows=12; break;
				}

				// options
				$field_opts = array(
					'validation_rules'=>$field->getCallbacksStack('validation'),
					'type'=> self::$html5_allowed ? $field->getTypeHtml5() : 'text',
				);
				$default = $field->getDefault();
				if (!empty($default) && is_string($default))
					$field_opts['comment'] = sprintf($this->default_mask, $default);
				unset($default);

				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'cols'=>$cols,
					'rows'=>$rows
				);
				if (!$field->isNullable())
					$field_attrs['required'] = 'true';
				if ($field->getValidationEntry('maxlength'))
					$field_attrs['maxlength'] = $field->getValidationEntry('maxlength');

				// the form field
				$this->setField( new \Tool\Form\FormField\TextareaField(
					$field_name, 
					!empty($this->values[$field_name]) ? $this->values[$field_name] : '',
					str_replace('_', ' ', ucfirst($field_name))
						.(!$field->isNullable() ? $this->required_flag : ''),
					$field_opts, $field_attrs
				));
			}

			elseif ('toggler'==$field->getType()) {
				// options
				$field_opts = array(
					'validation_rules'=>$field->getCallbacksStack('validation'),
					'type'=> 'checkbox',
				);
				$default = $field->getDefault();
				if (!empty($default) && is_string($default))
					$field_opts['comment'] = sprintf($this->default_mask, $default);
				unset($default);

				// attributes
				$field_attrs = array(
					'id'=>$field_name,
				);
				if (!$field->isNullable())
					$field_attrs['required'] = 'true';
				if (!empty($this->values[$field_name]))
					$field_attrs['checked'] = 'checked';

				// the form field
				$this->setField( new \Tool\Form\FormField\InputField(
					$field_name, '1',
					str_replace('_', ' ', ucfirst($field_name))
						.(!$field->isNullable() ? $this->required_flag : ''),
					$field_opts, $field_attrs
				));
			}
			
			elseif ('choice'==$field->getType()) {
				// options
				$field_opts = array(
					'validation_rules'=>$field->getCallbacksStack('validation'),
					'values' => $field->getValues(),
				);
				$comment='';
				$default = $field->getDefault();
				if (!empty($default) && is_string($default))
					$comment .= sprintf($this->default_mask, $default);
				unset($default);
				$_new = CarteBlanche::getContainer()->get('router')->buildUrl(array(
					'action'=>'create', 'model'=>$field->getRelationName(), 'controller'=>'crud', 
					'altdb'=>getContainer()->get('request')->getUrlArg('altdb')
				));
				$comment .= sprintf($this->comment_mask, '<a href="'.$_new.'" target="_blank" title="Create a new entry in a new window">Create a new '.$field_name.'</a>');
				$field_opts['comment'] = $comment;
				if (!empty($this->values[$field_name]))
					$field_opts['selected'] = $this->values[$field_name];

				// attributes
				$field_attrs = array(
					'id'=>$field_name,
				);
				if (!$field->isNullable())
					$field_attrs['required'] = 'true';

				// the form field
				$this->setField( new \Tool\Form\FormField\SelectField(
					$field_name, 
					!empty($this->values[$field_name]) ? $this->values[$field_name] : '',
					'Related '.str_replace('_', ' ', ucfirst($field_name))
						.(!$field->isNullable() ? $this->required_flag : ''),
					$field_opts, $field_attrs
				));

			}
			
			elseif ('file'==$field->getType()) {
				$this->multipart = true;
				unset($maxlength);
				if ($field->getValidationEntry('maxlength')) {
					$maxlength = $field->getValidationEntry('maxlength');
				} else {
					switch($field->getIntType()){
						case 'medium': $maxlength = '50000'; break;
						case 'tiny': $maxlength = '10000'; break;
						default: $maxlength = '100000'; break;
					}
				}
				unset($accept);
				if ($field->getValidationEntry('accept')) {
					$accept = is_array($field->getValidationEntry('accept')) ? 
						join(',', $field->getValidationEntry('accept')) : $field->getValidationEntry('accept');
				}

				// options
				$field_opts = array(
					'validation_rules'=>$field->getCallbacksStack('validation'),
					'type'=> self::$html5_allowed ? $field->getTypeHtml5() : 'text',
				);
				$comment='';
				$default = $field->getDefault();
				if (!empty($default) && is_string($default))
					$field_opts['comment'] = sprintf($this->default_mask, $default);
				unset($default);
				if (!empty($accept))
					$comment .= sprintf($this->comment_mask, 'Accept file types : <em>'.$accept.'</em>');
				$field_opts['comment'] = $comment;

				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'maxlength'=>$maxlength
				);
				if (!empty($accept))
					$field_attrs['accept'] = $accept;
				if (!$field->isNullable())
					$field_attrs['required'] = 'true';

				// the form field
				$this->setField( new \Tool\Form\FormField\FileField(
					$field_name, 
					!empty($this->values[$field_name]) ? $this->values[$field_name] : '',
					str_replace('_', ' ', ucfirst($field_name))
						.(!$field->isNullable() ? $this->required_flag : ''),
					$field_opts, $field_attrs
				));
			}

		}
//var_export($this->fields);
//exit('yo');
	}
	
// ------------------------
// UTILITIES
// ------------------------

	public function setField($field)
	{
		$this->fields_stack[] = $field;
	}

	public function renderField($str = '')
	{
		return sprintf(self::field_div_mask, $str);
	}

	public function renderLabel($str = '')
	{
		return sprintf(self::label_mask, $str);
	}

}

// Endfile