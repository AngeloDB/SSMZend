<?php

class Form_Query extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        $this->setName('query');
        // joins
        $tmpElem = new Zend_Form_Element_Hidden('joins');
        $this->addElement($tmpElem);

        // join
        $tmpElem = new Zend_Form_Element_Select('Join');
        $tmpElem->setLabel('Join');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        $tmpElem->addMultiOption('master', 'Master table');
        $tmpElem->addMultiOption('inner', 'Inner join');
        $tmpElem->addMultiOption('left', 'Left join');
        $tmpElem->addMultiOption('right', 'Right join');
        $this->addElement($tmpElem);

        // Alias
        $tmpElem = new Zend_Form_Element_Select('Alias');
        $tmpElem->setLabel('Alias');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        for($letter = ord('A'); $letter <= ord('Z'); $letter++)
        {
            $tmpElem->addMultiOption(chr($letter), chr($letter));
        }
        $this->addElement($tmpElem);

        // Tables
        $sql = 'SHOW tables';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('Table');
        $tmpElem->setLabel('Tabella');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['Tables_in_cantieri'],
                    $selValue['Tables_in_cantieri']);
        }
        $this->addElement($tmpElem);

        // On
        $tmpElem = new Zend_Form_Element_Textarea('On',
                        array('rows' => 3));
        $tmpElem->setLabel('On (join condition)');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Fields
/*        $tmpElem = new Zend_Form_Element_Select('Fields');
        $tmpElem->setLabel('Fields');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addMultiOption('', '');
        $this->addElement($tmpElem);
*/
        // Conferma
        $tmpElem = new Zend_Form_Element_Submit('Conferma',
                        array('label' => 'Conferma'));
        $tmpElem->setDecorators($this->buttonDecorators);
        $this->addElement($tmpElem);

        // Salva
        $tmpElem = new Zend_Form_Element_Submit('Salva',
                        array('label' => 'Salva'));
        $tmpElem->setDecorators($this->buttonDecorators);
        $this->addElement($tmpElem);

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }

}