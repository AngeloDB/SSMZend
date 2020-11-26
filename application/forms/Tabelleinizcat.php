<?php

class Form_Tabelleinizcat extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idIniziativa_categoria
        $tmpElem = new Zend_Form_Element_Hidden('idIniziativa_categoria');
        $this->addElement($tmpElem);

        // idIniziativa
        $sql = 'SELECT idIniziativa AS KeyId,
                Iniziativa AS Nome FROM iniziative';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idIniziativa');
        $tmpElem->setLabel('Iniziativa');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // idCategoria
        $sql = 'SELECT idCategoria AS KeyId,
                Categoria AS Nome FROM categorie';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idCategoria');
        $tmpElem->setLabel('Categoria');
        $tmpElem->setDecorators($this->elementDecorators);


        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);
        // Costo_unitario
        $tmpElem = new Zend_Form_Element_Text('Costo_unitario',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Costo unitario')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Valore_unitario
        $tmpElem = new Zend_Form_Element_Text('Valore_unitario',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Valore unitario')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Valore_percepito
        $tmpElem = new Zend_Form_Element_Text('Valore_percepito',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Valore percepito')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Limite_risparmioUnitario1
        $tmpElem = new Zend_Form_Element_Text('Limite_risparmioUnitario1',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Primo limite risparmio')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // RisparmioUnitario1
        $tmpElem = new Zend_Form_Element_Text('RisparmioUnitario1',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Percentuale risparmio prima fascia')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Limite_risparmioUnitario2
        $tmpElem = new Zend_Form_Element_Text('Limite_risparmioUnitario2',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Secondo limite risparmio')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // RisparmioUnitario1
        $tmpElem = new Zend_Form_Element_Text('RisparmioUnitario2',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Percentuale risparmio seconda fascia')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // MaxPercRetribuzione
        $tmpElem = new Zend_Form_Element_Text('MaxPercRetribuzione',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Percentuale massima sulla retribuzione')
            ->setDecorators($this->elementDecorators);
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

