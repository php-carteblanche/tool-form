<?php
/**
 * @category  	CarteBlanche
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 * @license   	GPL v3
 * @copyright 	Les Ateliers Pierrot <http://www.ateliers-pierrot.fr>
 * @link      	https://github.com/php-carteblanche/carteblanche
 * @package   	Tools
 */

namespace Tool;

use \CarteBlanche\App\Kernel;
use \CarteBlanche\App\Router;
use \CarteBlanche\App\Abstracts\AbstractTool;

class Form extends AbstractTool
{

	var $view='form.htm';

	var $form_id;
	var $fields;
	var $values;
	var $errors;
	var $multipart=false;

	protected $fields_stack=array();

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
		if (empty($this->fields)) return array();
		if (false===getContainer()->get('request')->isPost()) return array();
		if (empty($this->fields_stack)) $this->buildFieldsStack();

		$_posted = CarteBlanche::getContainer()->get('request')->post;
		if (empty($this->values)) $this->values = array();
		if (empty($this->errors)) $this->errors = array();

		$ok_posted=false;
		foreach($_posted as $pst_var=>$pst_val)
			if (!empty($pst_val)) $ok_posted=true;
		if (!$ok_posted) {
			$this->errors['form'] = 'No values received!';
		} else {
			foreach($this->fields_stack as $_field)
			{
				$this->values[$_field->getName()] = $_field->treat();
				if ($_field->hasErrors())
					$this->errors[$_field->getName()] = true;
			}
/*
			foreach($_posted as $posted_var=>$posted_val) 
			{
				if (isset($this->fields[$posted_var])) 
				{
					$field = $this->fields[$posted_var];
					$posted_val = CarteBlanche::getContainer()->get('request')->getPost($posted_var);
					$this->values[$posted_var] = $posted_val;

					if (isset($field['null']) && $field['null']==false) 
					{
						if (empty($posted_val))
							$this->errors[$posted_var] = 'This field is required';
					}
				
					if (preg_match('/^integer(\((.*)\))?/i', $field['type'], $matches) || preg_match('/^float(\((.*)\))?/i', $field['type'], $matches)) 
					{
						if (!empty($posted_val) && !is_numeric($posted_val))
							$this->errors[$posted_var] = 'This field only accept numeric values';
					}

					if (isset($field['validation'])) 
					{
						$validation_rules = explode('|', $field['validation']);
						if (!empty($validation_rules))
						foreach($validation_rules as $fct) {
							if (function_exists($fct)) {
								$response = (bool) $fct( $posted_val );
								if ($response!=true) {
									$this->errors[$posted_var] = "This field must pass test function '$fct'";
									break;
								}
							}
						}
	
					}

				}
			}
*/
		}
/*
		foreach($this->fields as $field_name=>$field) 
		{
			if (preg_match('/(.*)?blob$/i', $field['type'], $matches)) {
				if (!empty($_FILES[$field_name]) && file_exists($_FILES[$field_name]['tmp_name'])) {
					$this->values[$field_name] = file_get_contents( $_FILES[$field_name]['tmp_name'] );
				}
			}
		}
*/
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

echo '<pre>';
		foreach($this->fields as $field) 
		{
echo '<br />field name : '.$field->getName();
echo '<br />field : '.var_export($field,1);
			$field_name = $field->getName();

			if (in_array($field_name, array('created_at','updated_at'))) continue;

			elseif ($field_name=='id') 
			{
				$field = new \Tool\Form\FormField\IntegerField(
					'id', 
					!empty($this->values['id']) ? $this->values['id'] : null,
					null,
					array( 'hidden'=>true )
				);
				$this->setField( $field );
			}

			elseif ('string'==$field->getType()) 
			{
				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';

				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.(!$field->isNullable() ? $this->required_flag : '');

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

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );
			}

		}
//var_export($this->fields);
exit('yo');
		foreach($this->fields as $field_name=>$field) 
		{
			$validation_type = null;
			$validation_rules = array();
			if (isset($field['validation'])) 
			{
				$validation_rules = explode('|', $field['validation']);
				if (self::$html5_allowed && count($validation_rules))
				{
					foreach($validation_rules as $_validate) 
					{
						if ($_validate=='is_email') $validation_type = 'email';
						if ($_validate=='is_url') $validation_type = 'url';
					}
				}
			}

			if (in_array($field_name, array('created_at','updated_at'))) continue;

			elseif ($field_name=='id') 
			{
				$field = new \Tool\Form\FormField\IntegerField(
					'id', 
					!empty($this->values['id']) ? $this->values['id'] : null,
					null,
					array( 'hidden'=>true )
				);
				$this->setField( $field );
/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				if (!empty($this->values['id']))
					$formfields_ctt .= "\n".'<input type="hidden" name="id" value="'
						.$this->values['id']
						.'" />';
*/
			}
			elseif (preg_match('/^(.*)_id$/i', $field_name, $matches)) 
			{
				if (isset($matches[1]) && is_string($matches[1])) 
				{
					$related_object = $matches[1];
					if ($related_object=='parent')
						$related_object = CarteBlanche::getContainer()->get('request')->getUrlArg('model');
					$db = CarteBlanche::getContainer()->get('database');
					$table_ok = $db->table_exists($related_object);

					$_altdb = $this->container->get('request')->getUrlArg('altdb', null);
					$tables = \CarteBlanche\Lib\AutoObject\AutoObjectMapper::getObjectsStructure($_altdb);
					if ($tables)
					foreach($tables as $table) 
					{
						if ($table->getTableName()==$related_object)
							$related_object_structure = $table->getStructureEntry('structure');
/*
						if (!empty($table['table']) && $table['table']==$related_object)
							$related_object_structure = $table['structure'];
*/
					}
					if ($table_ok && isset($related_object_structure)) 
					{
//						$relation = new BaseModel( $related_object, $related_object_structure );
						$relation = $table->getModel();
						if (!$relation->count()) continue;
						$related_slug = $relation->getSlugField();
						$_parent_special = $relation->getSpecialFields( 'related', $related_object.':id' );
						if (count($_parent_special)) $_parent_special = $_parent_special[0];
						$query = "SELECT id";
						if ($related_slug)
							$query .= ", {$related_slug}";
						if (!empty($_parent_special))
							$query .= ", {$_parent_special}";
						$query .= " FROM $related_object";
						$results = $db->query($query);

						$_mod = CarteBlanche::getContainer()->get('request')->getUrlArg('model');

/*
 Gestion des parents : liste les enfants sous le parent, avec un ">" devant
 Reste à gérer les sous-enfants ... à refaire donc, avec une autre logique
*/
						
						$_options_results = array();
						foreach($results->fetchAll() as $result) 
						{
							if (!($related_object==$_mod && isset($this->values['id']) && $result['id']==$this->values['id']))
							{
								if (!empty($_parent_special) && !empty($result[$_parent_special]))
								{
									if (!isset($_options_results[$result[$_parent_special]]))
										$_options_results[$result[$_parent_special]]=array(
											'children'=>array(),
											'object'=>null
										);
									$_options_results[$result[$_parent_special]]['children'][$result['id']] = $result;
								} else {
									if (!isset($_options_results[$result['id']]))
										$_options_results[$result['id']] = array(
											'children'=>array(),
											'object'=>$result
										);
									else
										$_options_results[$result['id']]['object'] = $result;
								}
							}
						}

						$options_list='';
						if (!empty($_options_results))
						{
							$options_list="\n\t".'<option value=""'
								.( empty($this->values[$field_name]) ? ' selected="selected"' : '' )
								.'></option>';
							foreach($_options_results as $id_result=>$_f_result) 
							{
								$result = $_f_result['object'];
								$options_list .= "\n\t".'<option value="'.$result['id'].'"'
									.( (!empty($this->values[$field_name]) && $this->values[$field_name]==$result['id']) ? ' selected="selected"' : '' )
									.'>'
									.( $related_slug ? $result[$related_slug].' [id:'.$result['id'].']' : $result['id'] )
									.'</option>';
								if (!empty($_f_result['children']))
								foreach($_f_result['children'] as $id_child=>$child)
								{
									$options_list .= "\n\t".'<option value="'.$child['id'].'"'
										.( (!empty($this->values[$field_name]) && $this->values[$field_name]==$child['id']) ? ' selected="selected"' : '' )
										.'>'
										.' &gt; '
										.( $related_slug ? $child[$related_slug].' [id:'.$child['id'].']' : $child['id'] )
										.'</option>';
								}
							}
						}

						if (!empty($options_list)) 
						{
							$_new = Router::buildUrl(array(
								'action'=>'create', 'model'=>$related_object, 'controller'=>'crud', 
								'altdb'=>getContainer()->get('request')->getUrlArg('altdb', null)
							));
							$formfields_ctt .= "\n".'<div class="field">'
								.'<label for="'.$field_name.'">'
								.'Related '.$related_object
								.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
								.'</label>'
								.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
								.'<select id="'.$field_name.'" name="'.$field_name.'"'
								.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
								.'>'
								.$options_list
								.'</select>'
								.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
								.sprintf($this->comment_mask, '<a href="'.$_new.'" target="_blank" title="Create a new entry in a new window">Create a new '.$field_name.'</a>')
								.'</div>';
						}
					}
				}
			}

			elseif (preg_match('/^varchar(\((.*)\))?/i', $field['type'], $matches)) 
			{
				if (isset($matches[2]) && is_numeric($matches[2]))
					$maxlength = $matches[2];

				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'type'=> self::$html5_allowed && !empty($validation_type) ? $validation_type : 'text',
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';
				if (!empty($maxlength))
					$field_attrs['maxlength'] = $maxlength;

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );
/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="'
					.( !empty($validation_type) ? $validation_type : 'text' )
					.'" id="'.$field_name.'" name="'.$field_name.'" value="'
					.( !empty($this->values[$field_name]) ? $this->values[$field_name] : '' )
					.'"'
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.( !empty($maxlength) ? ' maxlength="'.$maxlength.'"' : '' )
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.'</div>';
*/
			}

			elseif (preg_match('/(.*)text$/i', $field['type'], $matches)) 
			{
				$cols = 40;
				$rows = 12;
				if (!empty($matches[1])) 
				switch($matches[1]){
					case 'long': $cols=80; $rows=30; break;
					case 'medium': $cols=30; $rows=8; break;
				}

				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'type'=> self::$html5_allowed && preg_match('/^integer(\((.*)\))?/i', $field['type'], $matches) ? 'number' : 'text',
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'cols'=>$cols,
					'rows'=>$rows
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';
				if (!empty($maxlength))
					$field_attrs['maxlength'] = $maxlength;

				$field = new \Tool\Form\FormField\TextareaField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<textarea id="'.$field_name.'" name="'.$field_name.'" cols="'.$cols.'" rows="'.$rows.'"'
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.'>'
					.( !empty($this->values[$field_name]) ? $this->values[$field_name] : '' )
					.'</textarea>'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.( (!empty($field['markdown']) && $field['markdown']===true) ? $this->markdown_flag : '' )
					.'</div>';
*/
			}

			elseif (preg_match('/^integer(\((.*)\))?/i', $field['type'], $matches) || preg_match('/^float(\((.*)\))?/i', $field['type'], $matches)) 
			{
				if (isset($matches[1]) && is_numeric($matches[2]))
					$maxlength = $matches[2];

				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'numeric'=>true,
					'type'=> self::$html5_allowed && preg_match('/^integer(\((.*)\))?/i', $field['type'], $matches) ? 'number' : 'text',
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'class'=>"numeric_field"
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';
				if (!empty($maxlength))
					$field_attrs['maxlength'] = $maxlength;

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="'
					.( self::$html5_allowed && preg_match('/^integer(\((.*)\))?/i', $field['type'], $matches) ? 'number' : 'text' )
					.'" id="'.$field_name.'" name="'.$field_name.'" value="'
					.( !empty($this->values[$field_name]) ? $this->values[$field_name] : '' )
					.'" class="numeric_field"'
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.( !empty($maxlength) ? ' maxlength="'.$maxlength.'"' : '' )
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.'</div>';
*/
			}

			elseif ($field['type']=='date') 
			{

				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'type'=> self::$html5_allowed ? 'date' : 'text',
					'html_before' => "\n".getContainer()->get('kernel')->view($this->views_dir.'calendar.htm')
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'class'=>"date_field",
					'onclick'=>"ds_sh(this);"
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="'
					.( self::$html5_allowed ? 'date' : 'text' )
					.'" id="'.$field_name.'" name="'.$field_name.'" value="'
					.( !empty($this->values[$field_name]) ? $this->values[$field_name] : '' )
					.'" class="date_field" onclick="ds_sh(this);"'
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.( !empty($maxlength) ? ' maxlength="'.$maxlength.'"' : '' )
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.'</div>';
*/
			}

			elseif ($field['type']=='datetime') 
			{
				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'type'=> self::$html5_allowed ? 'datetime' : 'text',
					'html_before' => "\n"getContainer()->get('kernel')->view($this->views_dir.'calendar.htm')
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'class'=>"datetime_field",
					'onclick'=>"ds_sh(this);"
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n"getContainer()->get('kernel')->view($this->views_dir.'calendar.htm');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="'
					.( self::$html5_allowed ? 'datetime' : 'text' )
					.'" id="'.$field_name.'" name="'.$field_name.'" value="'
					.( !empty($this->values[$field_name]) ? $this->values[$field_name] : '' )
					.'" class="datetime_field" onclick="ds_sh(this);"'
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.'</div>';
*/
			}

			elseif ($field['type']=='time') 
			{
				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'type'=> self::$html5_allowed ? 'time' : 'text',
					'html_before' => "\n"getContainer()->get('kernel')->view($this->views_dir.'calendar.htm')
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name,
					'class'=>"time_field",
					'onclick'=>"ds_sh(this);"
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n"getContainer()->get('kernel')->view($this->views_dir.'calendar.htm');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="'
					.( self::$html5_allowed ? 'time' : 'text' )
					.'" id="'.$field_name.'" name="'.$field_name.'" value="'
					.( !empty($this->values[$field_name]) ? $this->values[$field_name] : '' )
					.'" class="time_field" onclick="ds_sh(this);"'
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.'</div>';
*/
			}

			elseif ($field['type']=='tinyint(1)' || $field['type']=='bit' || $field['type']=='boolean') 
			{
				// value
				$field_value = '1';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
					'type'=> 'checkbox',
				);
				if (!empty($field['default']) && is_string($field['default']))
					$field_opts['comment'] = sprintf($this->default_mask, $field['default']);
				// attributes
				$field_attrs = array(
					'id'=>$field_name,
				);
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';
				if (!empty($this->values[$field_name]))
					$field_attrs['checked'] = 'checked';

				$field = new \Tool\Form\FormField\InputField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="checkbox" id="'.$field_name.'" name="'.$field_name.'" value="1"'
					.( !empty($this->values[$field_name]) ? ' checked="checked"' : '' )
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.'</div>';
*/
			}

			elseif (preg_match('/(.*)blob$/i', $field['type'], $matches)) 
			{
				$this->multipart = true;
				if (!empty($field['maxlength'])) {
					$maxlength = $field['maxlength'];
				} else {
					$maxlength = '100000';
					if (isset($matches[1]) && is_numeric($matches[2]))
						$filelength = $matches[2];
					if (!empty($filelength) && $filelength=='medium')
						$maxlength = '50000';
					elseif (!empty($filelength) && $filelength=='tiny')
						$maxlength = '10000';
				}
				if (!empty($field['accept'])) {
					$accept = is_array($field['accept']) ? join(',', $field['accept']) : $field['accept'];
				}

				// value
				$field_value = !empty($this->values[$field_name]) ? $this->values[$field_name] : '';
				// label
				$field_label = str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '');
				// options
				$field_opts = array(
					'validation_rules'=>$validation_rules,
				);
				$comment='';
				if (!empty($field['default']) && is_string($field['default']))
					$comment .= sprintf($this->default_mask, $field['default']);
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
				if (isset($field['null']) && $field['null']==false)
					$field_attrs['required'] = 'true';

				$field = new \Tool\Form\FormField\FileField(
					$field_name, $field_value, $field_label, $field_opts, $field_attrs
				);
				$this->setField( $field );

/*
var_export($field);
echo '<pre>'.$field.'</pre>';
exit('yo');
				$formfields_ctt .= "\n".'<div class="field">'
					.'<label for="'.$field_name.'">'
					.str_replace('_', ' ', ucfirst($field_name))
					.( (isset($field['null']) && $field['null']==false) ? $this->required_flag : '')
					.'</label>'
					.( isset($this->errors[$field_name]) ? '<span class="error">'.$this->errors[$field_name].'</span>' : '')
					.'<input type="file" id="'.$field_name.'" name="'.$field_name.'" maxlength="'.$maxlength.'"'
					.( !empty($accept) ? ' accept="'.$accept.'"' : '' )
					.( (isset($field['null']) && $field['null']==false) ? ' required="true"' : '')
					.' />'
					.( (!empty($field['default']) && is_string($field['default'])) ? sprintf($this->default_mask, $field['default']) : '' )
					.( !empty($accept) ? sprintf($this->comment_mask, 'Accept file types : <em>'.$accept.'</em>') : '' )
					.'</div>';
*/
			}

		}
		return $formfields_ctt;
	}
	
// ------------------------
// UTILITIES
// ------------------------

	public function setField( $field )
	{
		$this->fields_stack[] = $field;
	}

	public function renderField( $str='' )
	{
		return sprintf(self::field_div_mask, $str);
	}

	public function renderLabel( $str='' )
	{
		return sprintf(self::label_mask, $str);
	}

}

// Endfile