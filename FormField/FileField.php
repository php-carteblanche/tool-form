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

class FileField extends AbstractFormField
{

	protected $file_content;

	// sprintf with id, field_name, model	
	public static $change_link_mask = '&nbsp;<small>[<a href="javascript:field_toggler(\'%s\', \'%s\', \'%s\' );" title="Upload a new file" class="upload_toggler">change</a>]</small>';
	// sprintf with id, field_name
	public static $change_link_mask_html5 = '&nbsp;<small>[<a href="javascript:field_toggler(\'%s\', \'%s\' );" title="Upload a new file" class="upload_toggler">change</a>]</small>';

	protected function getFileContent()
	{
		if (!empty($_FILES[$this->getName()]) && file_exists($_FILES[$this->getName()]['tmp_name'])) 
		{
			$this->file_content = file_get_contents( $_FILES[$this->getName()]['tmp_name'] );
		} else {
			$this->file_content = parent::getValue();
		}
	}
	
	public function getValue()
	{
		if (empty($this->file_content)) $this->getFileContent();
		return $this->file_content;
	}

	/**
	 * Build the field HTML string in the sub-class
	 */
	public function treatField()
	{
		if (!$this->hasErrors() && empty($this->file_content))
		{
			$this->getFileContent();
			return $this->file_content;
		}
		return '';
	}

	/**
	 * Treat form values setting errors in the sub-class if so
	 */
	public function buildField()
	{
		$field_str = '<input type="file"'
			.' name="'.$this->getName().'"'
			.\Library\Helper\Html::parseAttributes( $this->getAttributes() )
			.' />';

		$str='';
		$_val = $this->getValue();
		if (!empty($_val)) {
			$_doc = new \Tool\DocumentField(array(
				'document_content'=>$_val,
				'max_width'=>100,
				'max_height'=>60,
			));
			$js_str = \Library\Helper\Html::javascriptProtect( $field_str );
			$dom_id = uniqid();
			if (self::$html5_allowed)
				$change_link = sprintf(self::$change_link_mask_html5, $dom_id, $this->getName());
			else
				$change_link = sprintf(self::$change_link_mask_html5, $dom_id, $this->getName(), $js_str);
			
			$str .= '<div id="'.$dom_id.'"'
				.(self::$html5_allowed ? ' data-prototype="'.$js_str.'">' : '')
				.$_doc->__toString()
				.$change_link
				.'</div>';
		}
		else
		{
			$str = $field_str;
		}

		return $str;
	}
	
}

// Endfile