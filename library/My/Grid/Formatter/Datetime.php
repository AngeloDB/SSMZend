<?php

/**
 * Mascker
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c) Mascker (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    0.4  mascker $
 * @author     Mascker (Bento Vilas Boas) <geral@petala-azul.com > 
 */



class My_Grid_Formatter_Datetime
{
    public $locale = 'it_IT';
    
    function __construct($loc)
    {
        $this->locale = $loc;
    }

    function format($value)
    {
        $date = new Zend_Date ( $value );
        $date->setLocale($this->locale);
        return $date->toString ('dd/MM/y h:m');
    }

}