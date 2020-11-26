<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Table
 *
 * @author italba
 */
class Gorilla_Pdf_Table
{
	public $x;
	public $y;
	public $width;
	public $border = 0.5;
	public $page;
	protected $_pages = array();
	protected $_rows = array();
    protected $_header;

	function __construct($page, $x, $y)
    {
		$this->page  = $page;
		$this->x     = $x;
		$this->y     = $y;
	}

	public function addHeader(Gorilla_Pdf_Table_Row $row)
    {
		$this->_header = $row;
		$this->addRow($row);
	}

	public function addRow(Gorilla_Pdf_Table_Row $row)
    {
		$this->_rows[] = $row;
	}

	public function render(Gorilla_Pdf_Page $docHeader)
    {
		$y = $this->page->getHeight() - $this->y;
		foreach($this->_rows as $key => $row)
        {
			if($y - $row->testRender($this->page, $this->x, $y) < 120)
            {
				$font = $this->page->getFont();
				$font_size = $this->page->getFontSize();
				//$linewidth = $this->page->getLineWidth();
				$this->page = new Gorilla_Pdf_Page($docHeader); //this->page
				$this->page->setFont($font, $font_size);
				$this->page->setLineWidth($this->border);
				$this->_pages[] = $this->page;
				$y = $this->page->getHeight()-120;
                if ($this->_header != null && $key > 0)
                {
                    $this->_header->render($this->page, $this->x, $y);
                    $y -= $this->_header->getHeight();
                }
			}
			$row->render($this->page, $this->x, $y);
			$y -= $row->getHeight();
		}
		return $this->_pages;
	}
}