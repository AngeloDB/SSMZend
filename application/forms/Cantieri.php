<?php

class Form_Cantieri extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idCantiere
        $tmpElem = new Zend_Form_Element_Hidden('idCantiere');
        $this->addElement($tmpElem);

        // Cantiere
        $tmpElem = new Zend_Form_Element_Text('Cantiere',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Cantiere');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Committente
        $tmpElem = new Zend_Form_Element_Text('Committente',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Committente');
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

        // Inizio
        $this->addElement('DateTextBox', 'Inizio',
                array('Decorators'   => $this->dojoElementDecorators,
                    'Label'        => 'Data inizio',
                    'Locale'       => 'it-IT',
                    'FormatLength' => 'long',
                    'DatePattern'  => 'dd/MM/yyyy'));

        // Fine
        $this->addElement('DateTextBox', 'Fine',
                array('Decorators'   => $this->dojoElementDecorators,
                    'Label'        => 'Data fine',
                    'Locale'       => 'it-IT',
                    'FormatLength' => 'long',
                    'DatePattern'  => 'dd/MM/yyyy'));

        // Note
        $tmpElem = new Zend_Form_Element_Textarea('Note',
                        array('rows' => 3));
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