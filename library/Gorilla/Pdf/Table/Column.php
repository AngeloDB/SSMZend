<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Column
 *
 * @author italba
 */
class Gorilla_Pdf_Table_Column
{
	protected $_height;
	protected $_width;
	protected $_text;
	protected $_padding = 3;
	protected $_align = 'left';

	public function setText($text)
    {
		$this->_text = $text;
		return $this;
	}

	public function getWidth()
    {
		return $this->_width;
	}

	public function setWidth($width)
    {
		$this->_width = $width;
		return $this;
	}

	public function getHeight()
    {
		return $this->_height;
	}

	public function setAlignment($align)
    {
		$this->_align = $align;
	}

	public function render($page, $x, $y)
    {
		$font_size = $page->getFontSize();
		$size = $page->drawVariableText($this->_text, $x+$this->_padding, $page->getHeight() - $y + $font_size, $this->_width-$this->_padding, $this->_align);
		$this->_height = $size['height']+$this->_padding;
		$this->_width = $size['width'] + $this->_padding; //$this->_width
	}

	public function testRender($page, $x, $y)
    {
		$font_size = $page->getFontSize();
		$size = $page->getVariableText($this->_text, $x+$this->_padding, $page->getHeight() - $y + $font_size, $this->_width-$this->_padding);
		$this->_height = $size['height'] + $this->_padding;
		$this->_width = $this->_width + $this->_padding;
	}

	public function renderBorder($page, $x, $y, $height)
    {
		$font_size = $page->getFontSize();
		$page->drawRectangle($x, $y, $x+$this->_width, $y - $height, $fillType = Zend_Pdf_Page::SHAPE_DRAW_STROKE);
	}
}
