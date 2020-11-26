<?php

class Form_Simulazioni_Newcat extends Form_Myform
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

        // PercSottoscrizione
        $tmpElem = new Zend_Form_Element_Text('PercSottoscrizione',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Percentuale di sottoscrittori')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // ValSottoscrizione
        $tmpElem = new Zend_Form_Element_Text('ValSottoscrizione',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Valore totale sottoscrizione')
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

