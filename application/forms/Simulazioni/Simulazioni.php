<?php

class Form_Simulazioni_Simulazioni extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idSimulazione
        $tmpElem = new Zend_Form_Element_Hidden('idIsimulazione');
        $this->addElement($tmpElem);

        // DataSim
        $this->addElement('DateTextBox', 'DataSim',
                array('Decorators'   => $this->dojoElementDecorators,
                    'Label'        => 'Data simulazione',
                    'Locale'       => 'it-IT',
                    'FormatLength' => 'long',
                    'DatePattern'  => 'dd/MM/yyyy'));

        // idCliente
        $sql = 'SELECT idCliente AS KeyId,
                Cliente AS Nome FROM clienti';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idCliente');
        $tmpElem->setLabel('Cliente');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);


        // TotImporto
        $tmpElem = new Zend_Form_Element_Text('TotImporto',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Importo totale')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Legislazione
        $tmpElem = new Zend_Form_Element_TextArea('LegislazioneAz',
            array('rows'=>3));
        $tmpElem->setLabel('Contratto aziendale');
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
        $tmpElem->setAttrib('onclick', 'return chiudiCliente(this)');
        $this->addElement($tmpElem);

        $this->setDecorators(array(
                'FormElements',
                array('HtmlTag', array('tag' => 'table')),
                'Form'));
    }


}

