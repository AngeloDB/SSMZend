<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Page
 *
 * @author italba
 */

class Gorilla_Pdf_Page extends Zend_Pdf_Page
{
	public function drawInfoBox($header, $lines, $x, $y, $width, $height) {
		$font_size = $this->getFontSize();
		//-- Draw the box
		$this->drawRectangle($x, $this->getHeight()-$y, $x+$width, $this->getHeight()-$y-$height, $fillType = Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		//-- Draw the header bottom
		$this->drawLine($x, $this->getHeight()-$y-($font_size*2), $x+$width, $this->getHeight()-$y-($font_size*2));
		//-- Draw the header text
		$this->drawText($header, $x+($font_size / 2), $this->getHeight()-$y-$font_size-($font_size/4), 'UTF-8');
		$this->drawMultilineText($lines, $x+3, $y+($font_size*3), 'UTF-8');
	}

	public function drawMultilineText($lines, $x, $y) {
		$y = $this->getHeight() - $y;
		$font_size = $this->getFontSize();
		foreach($lines as $i=>$line) {
			$this->drawText($line, $x+2, $y-($font_size * 1.2 * $i), 'UTF-8');
		}
	}

	public function getVariableText($str, $x, $y, $max_width) {
		$y = $this->getHeight() - $y;
		$font = $this->getFont();
		$font_size = $this->getFontSize();
		//-- Find out each word's width
		$words = explode(' ', $str);
		$words_lens = array();
		$em = $font->getUnitsPerEm();
		$space_size = array_sum($font->widthsForGlyphs(array(ord(' ')))) / $em * $font_size;
        error_reporting(0);
        foreach($words as $i=>$word) {
			$word .= ' ';
			$glyphs = array();
            foreach(range(0, strlen($word)-1) as $v) {
				$glyphs[] = ord($str[$v]);
			}
			$words_lens[] = array_sum($font->widthsForGlyphs($glyphs)) / $em * $font_size;
		}
		//-- Push words onto lines to be drawn.
		$y_inc = $y;
		$x_inc = 0;
		$lines = array();
		$line = array();
		$i = 0;
		$max_length = count($words);
		while($i < $max_length) {
			if(($x_inc + $words_lens[$i]) < $max_width) {
				$x_inc += $words_lens[$i] + $space_size;
				$line[] = $words[$i];
			} else {
				$lines[] = array($line, $x, $y_inc);
				$y_inc -= $font_size;
				$x_inc = 0;
				$line = array();
				$line[] = $words[$i+1];
				$i++;
			}
			$i++;
		}
        error_reporting(E_ALL ^ E_NOTICE);
		unset($words_lens);
		$lines[] = array($line, $x, $y_inc);
		//var_dump($lines); echo "<br/>";
		return array('width'=>$max_width, 'height'=>($font_size * count($lines)), 'lines'=>$lines);
	}

	public function drawVariableText($str, $x, $y, $max_width, $align='left') {
		$text = $this->getVariableText($str, $x, $y, $max_width);
		foreach($text['lines'] as $line) {
			list($str, $x, $y) = $line;
			$x_pos = $x;
			if($align == 'right') {
				$len = $this->calculateTextWidth(implode(' ', $str));
				$x_pos += $max_width - $len;
			} else if ($align == 'center') {
				$len = $this->calculateTextWidth(implode(' ', $str));
				$x_pos += ($max_width - $len) / 2;
			}
			$this->drawText(implode(' ', $str), $x_pos, $y, 'UTF-8');
		}
		return array('width'=>$max_width, 'height'=>$text['height']);
	}

	public function calculateTextWidth($str) {
		$font = $this->getFont();
		$font_size = $this->getFontSize();
		//-- Find out each word's width
		$em = $font->getUnitsPerEm();
		$glyphs = array();
		foreach(range(0, strlen($str)-1) as $i) {
			$glyphs[] = ord($str[$i]);
		}
		return array_sum($font->widthsForGlyphs($glyphs)) / $em * $font_size;
	}
}