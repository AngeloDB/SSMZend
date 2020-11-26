<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Dojo_View_Helper_Dialog
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Calendar
 *
 * @category   Zend
 * @package    Zend_Dojo_View_Helper_Dialog
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License  Extended
 */
class My_Dojo_View_Helper_Dialog extends My_Dojo_View_Helper_Dijit_Extended
{
	const DIALOG_DOJO = 'dijit.Dialog';
	const DIALOG_DOJOX = 'dojox.widget.Dialog';

	/**
	 * Holds the default dialog type.
	 */
	public static $_dialogType = self::DIALOG_DOJO;

	/**
	 * Sets the default dialog type to use.
	 *
	 * @param string $dojoType Type of dijit to use.
	 */
	public static function setDialogType($dojoType)
	{
		switch ($dojoType) {
			case self::DIALOG_DOJOX:
				self::$_dialogType = $dojoType;
				break;
			default:
				self::$_dialogType = self::DIALOG_DOJO;
				break;
		}
	}

	/**
	 * DataStore view helper.
	 *
	 * @param string $id JavaScript id for the dialog.
	 * @param array $attribs Attributes for the dialog.
	 * @param array $options Options for the dialog.
	 */
	public function dialog($id = '', array $attribs = array(), array $options = array())
	{
		if (!$id) {
			throw new Zend_Exception('Invalid arguments: required jsId.');
		}

		// Determine dialog type
		$dialogType = self::$_dialogType;
		if (array_key_exists('useDojox', $options)) {
			$dialogType = self::DIALOG_DOJOX;
		}

		// Require module
		$this->dojo->requireModule($dialogType);

		// Add styles
		if ($dialogType == self::DIALOG_DOJOX) {
			self::addStylesheet('/dojox/widget/Dialog/Dialog.css');
		}

		// Programmatic
		if ($this->_useProgrammatic()) {
			if (!$this->_useProgrammaticNoScript()) {
				$this->dojo->addJavascript('var ' . $id . ";\n");
				$js = $id . ' = ' . 'new ' . $dialogType . '(' . Zend_Json::encode($attribs) . ");";
				$this->dojo->_addZendLoad("function(){{$js}}");
			}
			return '';
		}

		// Set extra attribs for declarative
		if (!array_key_exists('id', $attribs)) {
			$attribs['id'] = $id;
		}

		if (!array_key_exists('jsId', $attribs)) {
			$attribs['jsId'] = $id;
		}

		if (!array_key_exists('dojoType', $attribs)) {
			$attribs['dojoType'] = $dialogType;
		}

		if (array_key_exists('content', $attribs)) {
			$content = $attribs['content'];
			unset($attribs['content']);
		}

		return '<div' . $this->_htmlAttribs($attribs) . '>' . $content . "</div>\n";
	}
}