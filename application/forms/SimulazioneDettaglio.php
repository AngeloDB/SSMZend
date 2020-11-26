<?php

class Form_SimulazioneDettaglio extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idSimulazione
        $tmpElem = new Zend_Form_Element_Hidden('idSimulazione');
        $this->addElement($tmpElem);

        // idCliente
        $sql = 'SELECT idCliente AS KeyId,
                Cliente AS Nome FROM Clienti';
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

        // DataSim
        $tmpElem = new Zend_Form_Element_Text('DataSim',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Data simulazione')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // TotVantaggioCli
        $tmpElem = new Zend_Form_Element_Text('TotVantaggioCli',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Totale vantaggi azienda')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // TotVantaggioPers
        $tmpElem = new Zend_Form_Element_Text('TotVantaggioPers',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Totale vantaggi personale')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Iniziative
        $sql1 = 'SELECT idIniziativa AS KeyId1,
                Iniziativa AS Nome1 FROM iniziative';
        $resSelect1 = $db->fetchAll($sql1);
        foreach ($resSelect1 as $selValue1)
        {
            // TotVantaggioCli
            $tmpElem = new Zend_Form_Element_Checkbox('Iniziativa_'.$selValue1['KeyId1']);
            $tmpElem->setLabel($selValue1['Nome1'])
                ->setDecorators($this->elementDecorators);
            $this->addElement($tmpElem);
            $sql2 = 'SELECT idCategoria AS KeyId2,
                    Categoria AS Nome2 FROM categorie';
            $resSelect2 = $db->fetchAll($sql2);
            foreach ($resSelect2 as $selValue2)
            {
                // PercSottoscrizione
                $tmpElem = new Zend_Form_Element_Text('PercSott_'.
                    $selValue1['KeyId1'].'_'.$selValue2['KeyId2'],
                    array('size'=>20,
                        'maxLenght'=>20));
                $tmpElem->setLabel('Perc. sottoscrittori '.$selValue2['Nome2'])
                    ->setDecorators($this->elementDecorators);
                $this->addElement($tmpElem);
            }
        }

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

