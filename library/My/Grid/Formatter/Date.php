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
class My_Grid_Formatter_Date implements Bvb_Grid_Formatter_FormatterInterface
    {

    public $locale = 'it_IT';

    function __construct($options = array())
        {
        //$this->locale = $loc;
        }

    function format($value)
        {
        try
            {
            $date = new Zend_Date($value);
//            $date->setLocale($this->locale);
            $tmpDate = $date->get('dd/MM/YYYY');
//            $tmpDate = $date->toString('dd/MM/y');
//            $date = date_create($value);
//            $tmpDate = date_format($date, 'dd/MM/y');
            }
        catch (Exception $e)
            {
            return '';
            }
        if ($tmpDate == '30/11/1999' || $tmpDate == '00/00/0000')
            return '';
        else
            return $tmpDate;
        }

    }