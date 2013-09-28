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

class SelectField extends AbstractFormField
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
		if (null===$this->getOption('values')) return '';
		$str = '<select'
			.' name="'.$this->getName().'"'
			.\Library\Helper\Html::parseAttributes( $this->getAttributes() )
			.'>'
			.$this->buildOptionsList( $this->getOption('values') )
			.'</select>';
		return $str;
	}
	
	public function buildOptionsList( $values )
	{
		$options_list='';

		if (!is_array($values) && !($values instanceof \Patterns\Commons\Collection))
			throw new \RuntimeException(
				sprintf('List of options for a SelectField object must be an array or a Collection, got "%s"!', gettype($values))
			);

		if (!empty($values)) {
			$options_list="\n\t".'<option value=""'
//				.( empty($this->values[$field_name]) ? ' selected="selected"' : '' )
				.( null===$this->getValue() ? ' selected="selected"' : '' )
				.'></option>';
			foreach ($values as $id_result=>$data) {
				if (!isset($data['id'])) $data['id'] = $id_result;
				$options_list .= $this->buildOptionEntry($data);
				if (!empty($data['children']))
				foreach ($data['children'] as $id_child=>$child) {
				    if (!isset($child['id'])) $child['id'] = $id_child;
    				$options_list .= $this->buildOptionEntry($child, ' &gt; ');
				}
			}
		}
		return $options_list;
	}
	
	public function buildOptionEntry($data, $content_prefix = '')
	{
		$options_entry = "\n\t".'<option value="'.$data['id'].'"'
			.( $this->getValue()==$data['id'] ? ' selected="selected"' : '' )
			.'>'
			.$content_prefix
			.( isset($data['slug']) ? $data['slug'].' [id:'.$data['id'].']' : $data['id'] )
			.'</option>';
		return $options_entry;
	}
	
}

// Endfile