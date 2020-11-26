<?php

class Form_Appalti extends Form_Myform
{

    public function init()
    {
//        parent::init();
        $db = Zend_Registry::get('db');

        // idAppalto
        $tmpElem = new Zend_Form_Element_Hidden('idAppalto');
        $this->addElement($tmpElem);

        // idCantiere
        $sql = 'SELECT idCantiere AS KeyId,
                Cantiere AS Nome FROM cantieri';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idCantiere');
        $tmpElem->setLabel('Cantiere');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // idDitta
        $sql = 'SELECT idImpresa AS KeyId,
                Impresa AS Nome FROM imprese';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idImprese');
        $tmpElem->setLabel('Impresa');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // Descrizione
        
        $tmpElem = new Zend_Form_Element_Text('Descrizione',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Descrizione');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);
        /* 'Decorators'   => $this->elementDecorators,
        
        $this->addElement('TextBox', 'Descrizione',
                    array('Decorators'   => $this->dojoElementDecorators,
                          'Label'        => 'Descrizione',
                          'Size'         => 80,
                          'MaxLenght'    => 80));
        */
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
        //$tmpElem = new Zend_Form_Element_Textarea('Note',
        //                array('rows' => 3));
        //$tmpElem->setLabel('Note');
        //$tmpElem->setDecorators($this->elementDecorators);
        //$this->addElement($tmpElem);

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