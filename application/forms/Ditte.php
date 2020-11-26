<?php

class Form_Ditte extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idDitta
        $tmpElem = new Zend_Form_Element_Hidden('idImpresa');
        $this->addElement($tmpElem);

        // Ditta
        $tmpElem = new Zend_Form_Element_Text('Ditta',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Impresa');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Responsabile
        $tmpElem = new Zend_Form_Element_Text('Responsabile',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Responsabile');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Indirizzo
        $tmpElem = new Zend_Form_Element_Text('Indirizzo',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Indirizzo');
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