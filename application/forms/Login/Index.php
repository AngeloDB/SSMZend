<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
class Form_login_Index extends Form_Myform
{

    public function init()
    {

        $tmpElem = new Zend_Form_Element_Text('username');
        $tmpElem->setLabel('Un')
            ->setDecorators($this->elementDecorators)
            ->setRequired(true);
        $this->addElement($tmpElem);

        $tmpElem= new Zend_Form_Element_Password('password');
        $tmpElem->setLabel('Pw')
            ->setDecorators($this->elementDecorators)
            ->setRequired(true);
        $this->addElement($tmpElem);

        $tmpElem = new Zend_Form_Element_Submit('login');
        $tmpElem->setLabel('Login')
            ->setDecorators($this->buttonDecorators);
        $this->addElement($tmpElem);

        $this->setDecorators(array(
                'FormElements',
                array('HtmlTag', array('tag' => 'table')),
                'Form' ));
    }
}