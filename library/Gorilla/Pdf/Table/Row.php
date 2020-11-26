<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Row
 *
 * @author italba
 */
class Gorilla_Pdf_Table_Row
{
	protected $_width;
	protected $_height;
	protected $_cols = array();

	public function addColumn(Gorilla_Pdf_Table_Column $col)
    {
		$this->_cols[] = $col;
	}

	public function render($page, $x, $y)
    {
		$tmp_x = $x;
		$max_height = 0;
		foreach($this->_cols as $col)
        {
			$col->render($page, $x, $y);
			$height = $col->getHeight();
			if($height > $max_height)
            {
				$max_height = $height;
			}
			$x += $col->getWidth();
		}
		$this->_height = $max_height;
		$this->renderBorder($page, $tmp_x, $y);
	}

	public function testRender($page, $x, $y)
    {
		$tmp_x = $x;
		$max_height = 0;
		foreach($this->_cols as $col)
        {
			$col->testRender($page, $x, $y);
			$height = $col->getHeight();
			if($height > $max_height)
            {
				$max_height = $height;
			}
			$x += $col->getWidth();
		}
		$this->_height = $max_height;
		return $this->_height;
	}

	public function renderBorder($page, $x, $y)
    {
		foreach($this->_cols as $col)
        {
			$col->renderBorder($page, $x, $y, $this->_height);
			$x += $col->getWidth();
		}
	}

	public function getHeight()
    {
		return $this->_height;
	}
}
