<?php
class Form_Simulazioni_Iniziative extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // idIniziativa_categoria_simulazione
        $tmpElem = new Zend_Form_Element_Hidden('idIniziativa_categoria_simulazione');
        $this->addElement($tmpElem);

        // idSimulazione_categoria
        $tmpElem = new Zend_Form_Element_Hidden('idSimulazione_categoria');
        $this->addElement($tmpElem);

        // idIniziativa_categoria
        $sql = 'SELECT A.idIniziativa_categoria AS KeyId,
                CONCAT(C.Iniziativa, " ", B.Categoria) AS Nome FROM
                iniziative_categorie A JOIN categorie B
                ON B.idCategoria=A.idCategoria JOIN iniziative C
                ON C.idIniziativa=A.idIniziativa';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idIniziativa_categoria');
        $tmpElem->setLabel('Iniziativa-categoria');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // PercSottoscrizione
        $tmpElem = new Zend_Form_Element_Text('PercSottoscrizione',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Percentuale di sottoscrittori');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // ValSottoscrizione
        $tmpElem = new Zend_Form_Element_Text('ValSottoscrizione',
            array('size'=>20,
                'maxLenght'=>20));
        $tmpElem->setLabel('Valore totale');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Note
        $tmpElem = new Zend_Form_Element_TextArea('LegislazioneAz',
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