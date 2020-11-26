<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_DatePicker extends ZendX_JQuery_Form_Element_DatePicker
{
    private $byPass=false;
    
    public function getValue()
    {
        if (!$this->bypass)
        {
            $val=explode('-', $this->_value);
            if(is_array($val) && count($val) == 3)
            {
                parent::setValue($val[2].'/'.$val[1].'/'.$val[0]);
            }
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
    

    public function reverseDate($value)
    {
        $this->bypass=true;
        $this->setJQueryParam('dateFormat', 'yy-mm-dd');
        $val=explode('/', $value);
        if(is_array($val) && count($val) == 3)
        {
            $value = $val[2].'-'.$val[1].'-'.$val[0];
        }
        parent::setValue($value);
        return true;
    }

}