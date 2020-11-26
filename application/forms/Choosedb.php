<?php

class Form_Choosedb extends Form_Myform
{

    public function init()
    {
//        parent::init();
        $db = Zend_Registry::get('db');

        // Databases
        $sql = 'SHOW databases';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('Database');
        $tmpElem->setLabel('Database');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['Database'], $selValue['Database']);
        }
        $this->addElement($tmpElem);

        // Tables
        $sql = 'SHOW tables';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Multiselect('Tables');
        $tmpElem->setLabel('Tabelle');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['Tables_in_cantieri'],
                    $selValue['Tables_in_cantieri']);
        }
        $this->addElement($tmpElem);

        // Conferma
        $tmpElem = new Zend_Form_Element_Submit('Conferma',
                        array('label' => 'Conferma'));
        $tmpElem->setDecorators($this->buttonDecorators);
        $this->addElement($tmpElem);

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }

}