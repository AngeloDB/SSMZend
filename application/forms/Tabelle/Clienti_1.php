<?php
class Form_Tabelle_Clienti extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idCliente
        $tmpElem = new Zend_Form_Element_Hidden('idCliente');
        $this->addElement($tmpElem);

        // Cliente
        $tmpElem = new Zend_Form_Element_Text('Cliente',
            array('size'=>80,
                'maxLenght'=>80));
        $tmpElem->setLabel('Cliente');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Ragione sociale
        $tmpElem = new Zend_Form_Element_Text('RagSoc',
            array('size'=>80,
                'maxLenght'=>80));
        $tmpElem->setLabel('Ragione sociale');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Sede
        $tmpElem = new Zend_Form_Element_Text('Sede',
            array('size'=>80,
                'maxLenght'=>80));
        $tmpElem->setLabel('Sede');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Indirizzo
        $tmpElem = new Zend_Form_Element_Text('Indirizzo',
            array('size'=>80,
                'maxLenght'=>80));
        $tmpElem->setLabel('Indirizzo');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Note
        $tmpElem = new Zend_Form_Element_Textarea('Note',
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