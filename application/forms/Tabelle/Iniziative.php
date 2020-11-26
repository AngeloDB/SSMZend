<?php
class Form_Tabelle_Iniziative extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idIniziativa
        $tmpElem = new Zend_Form_Element_Hidden('idIniziativa');
        $this->addElement($tmpElem);

        // Iniziativa
        $tmpElem = new Zend_Form_Element_Text('Iniziativa',
            array('size'=>80,
                'maxLenght'=>80));
        $tmpElem->setLabel('iniziativa');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Risparmio
        $tmpElem = new Zend_Form_Element_Text('Risparmio',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Risparmio');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Legislazione
        $tmpElem = new Zend_Form_Element_TextArea('Legislazione',
            array('rows'=>3));
        $tmpElem->setLabel('Legislazione');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Note
        $tmpElem = new Zend_Form_Element_TextArea('Note',
            array('rows'=>3));
        $tmpElem->setLabel('Note');
        $tmpElem->setDecorators($this->elementDecorators);
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