<?php
class Form_Login_Update extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');

        // id
        $tmpElem = new Zend_Form_Element_Hidden('id');
        $this->addElement($tmpElem);

        // Username
        $tmpElem = new Zend_Form_Element_Text('username',
            array('size'=>80,
                'maxLenght'=>80));
        $tmpElem->setLabel('Username')
                ->setRequired(true)
                ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Vecchia password
        $tmpElem = new Zend_Form_Element_Password('old_passwd',
            array('size'=>40,
                'maxLenght'=>40));
        $tmpElem->setLabel('Vecchia password')
                ->setRequired(true)
                ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Nuova password
        $tmpElem = new Zend_Form_Element_Password('new1_passwd',
            array('size'=>40,
                'maxLenght'=>40));
        $tmpElem->setLabel('Nuova password')
                ->setRequired(true)
                ->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Ripeti nuova password
        $tmpElem = new Zend_Form_Element_Password('new2_passwd',
            array('size'=>40,
                'maxLenght'=>40));
        $tmpElem->setLabel('Ripeti nuova password')
                ->setRequired(true)
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
                'Form',
        ));
    }
}