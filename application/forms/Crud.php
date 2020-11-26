<?php

class Form_Crud extends Form_Myform
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
                    $params=$elemDef['parameters'];
                    $params['Decorators'] = $this->dojoElementDecorators;
                    $this->addElement('DateTextBox', $elemId, $params);
                    break;

                case 'hidden':
                    $tmpElem = new Zend_Form_Element_Hidden($elemId);
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

}