<?php

class Form_Simulazioni_Categorie extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idSimulazione
        $tmpElem = new Zend_Form_Element_Hidden('idSimulazione');
        $this->addElement($tmpElem);

        // idSimulazione-categoria
        $tmpElem = new Zend_Form_Element_Hidden('idSimulazione_categoria');
        $this->addElement($tmpElem);

        // idCliente_categoria
        $sql = 'SELECT A.idCliente_categoria AS KeyId,
                CONCAT(C.Categoria, " ", B.Cliente) AS Nome FROM
                clienti_categorie AS A
                JOIN clienti AS B ON B.idCliente=A.idCliente
                JOIN categorie AS C ON C.idCategoria=A.idCategoria';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idCliente_categoria');
        $tmpElem->setLabel('Categoria/cliente');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // Numero
        $tmpElem = new Zend_Form_Element_Text('Numero',
            array('size'=>4,
                'maxLenght'=>4));
        $tmpElem->setLabel('Numero')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Livello
        $tmpElem = new Zend_Form_Element_Text('Livello',
            array('size'=>40,
                'maxLenght'=>40));
        $tmpElem->setLabel('Livello')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // % single
        $tmpElem = new Zend_Form_Element_Text('Single',
            array('size'=>4,
                'maxLenght'=>4));
        $tmpElem->setLabel('Single')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // % coniuge a carico
        $tmpElem = new Zend_Form_Element_Text('Coniuge',
            array('size'=>4,
                'maxLenght'=>4));
        $tmpElem->setLabel('Coniuge')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // % 1 figlio
        $tmpElem = new Zend_Form_Element_Text('Unfiglio',
            array('size'=>4,
                'maxLenght'=>4));
        $tmpElem->setLabel('Un figlio')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // % 2 figli
        $tmpElem = new Zend_Form_Element_Text('Duefigli',
            array('size'=>4,
                'maxLenght'=>4));
        $tmpElem->setLabel('Due figli')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // % 3 figli
        $tmpElem = new Zend_Form_Element_Text('Trefigli',
            array('size'=>4,
                'maxLenght'=>4));
        $tmpElem->setLabel('Tre o più figli')
            ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Importo
        $tmpElem = new Zend_Form_Element_Text('Importo',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Stipendio base')
            ->setDecorators($this->elementDecorators);
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

