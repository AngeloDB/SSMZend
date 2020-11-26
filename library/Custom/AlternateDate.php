<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_AlternateDate extends Zend_Form_Element_Hidden
{

    private $datePicker;

    public function init()
    {
        $this->setDisableLoadDefaultDecorators(true);
        $this->addDecorator("ViewHelper");
    }

    public function setDatePicker($dp)
    {
        $this->datePicker = $dp;
    }

    public function getValue()
    {
        $val = explode('-', $this->_value);
        if (is_array($val) && count($val) == 3)
        {
            $this->datePicker->setValue($val[2] . '/' . $val[1] . '/' . $val[0]);
        }
        return parent::getValue();
    }

//    public function isValid($data)
//    {
//        $val=explode('/', $value);
//        if(is_array($val) && count($val) == 3)
//        {
//            $value = $val[2].'-'.$val[1].'-'.$val[0];
//        }
//        $this->_value = $value;
//        return parent::isValid($data);
//    }
//    public function setValue($value)
//    {
//        $this->bypass=true;
//        $val=explode('/', $value);
//        if(is_array($val) && count($val) == 3)
//        {
//            $value = $val[2].'-'.$val[1].'-'.$val[0];
//        }
//        parent::setValue($value);
//        return true;
//    }


//    public function reverseDate($value)
//    {
//        $this->bypass = true;
//        $this->setJQueryParam('dateFormat', 'yy-mm-dd');
//        $val = explode('/', $value);
//        if (is_array($val) && count($val) == 3)
//        {
//            $value = $val[2] . '-' . $val[1] . '-' . $val[0];
//        }
//        parent::setValue($value);
//        return true;
//    }

}