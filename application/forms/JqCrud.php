<?php

class Form_JqCrud extends Form_Myjqform
{

    public function init()
    {
        $db = Zend_Registry::get('db');
        $formDef = Zend_Registry::get('edt');
        
        foreach ($formDef as $elemId => $elemDef)
        {
            switch ($elemDef['type'])
            {
                case 'date':
                    $altElem = new Custom_AlternateDate($elemId);
                    $this->addElement($altElem);

                    $tmpElem = new ZendX_JQuery_Form_Element_DatePicker(
                        $elemId.'-alternate',
                        array('Decorators' => $this->jqueryElementDecorators,
                            'Locale' => 'it-IT',
                            'FormatLength' => 'long',
                            'DatePattern' => 'dd/MM/yy'));
                    $tmpElem->setJQueryParams(array('dateFormat' => 'dd/mm/yy',
                        'altField' => '#'.$elemId,
                        'regional' => 'it',
                        'altFormat' => 'yy-mm-dd',
                        'navigationAsDateFormat' => true));
                    $tmpElem->setLabel($elemDef['parameters']['Label']);
//                    $tmpElem->addValidator(new Zend_Validate_Callback(
//                            array($tmpElem,'reverseDate')));
                    $altElem->setDatePicker($tmpElem);
                    $this->addElement($tmpElem);
                    break;

                case 'hidden':
                    $tmpElem = new Custom_AlternateDate($elemId);
                    $this->addElement($tmpElem);
                    break;

                case 'select':
                    $resSelect = $db->fetchAll($elemDef['selsql']);
                    $tmpElem = new Zend_Form_Element_Select($elemId);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->elementDecorators);

                    $tmpElem->addMultiOption('', '');
                    foreach ($resSelect as $selValue)
                    {
                        $tmpElem->addMultiOption($selValue['KeyId'],
                                $selValue['Value']);
                    }
                    $this->addElement($tmpElem);
                    break;

                case 'submit':
                    $tmpElem = new Zend_Form_Element_Submit($elemId,
                                    array('label' => $elemDef['label']));
                    $tmpElem->setDecorators($this->buttonDecorators);
                    $this->addElement($tmpElem);
                    break;

                case 'text':
                    $tmpElem = new Zend_Form_Element_Text($elemId,
                                    $elemDef['parameters']);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->elementDecorators);
                    $this->addElement($tmpElem);
                    break;

                case 'textarea':
                    $tmpElem = new Zend_Form_Element_Textarea($elemId,
                                    $elemDef['parameters']);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->elementDecorators);
                    $this->addElement($tmpElem);
                    break;

                default:
                    break;
            }
        }

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }
    
    function setDate($date) {
    $date = $this->getValue('date');
    return new Zend_Date($date, "MM/dd/yyyy");
}


}