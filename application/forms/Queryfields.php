<?php

class Form_Queryfields extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        $this->setName('queryfields');

        // Fields
        $tmpElem = new Zend_Form_Element_Select('Fields');
        $tmpElem->setLabel('Fields');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        $this->addElement($tmpElem);

        // Conferma
        $tmpElem = new Zend_Form_Element_Submit('Copia',
                        array('label' => 'Copia'));
        $tmpElem->setDecorators($this->buttonDecorators);
        $tmpElem->setAttrib('onclick', 'return copyField()');
        $this->addElement($tmpElem);

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }

}