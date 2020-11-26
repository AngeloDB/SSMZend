<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
class Form_Mail_Index extends Form_Myform
{

    public function init()
    {
        $db = Zend_Registry::get('db');
        $config = Zend_Registry::get('config');
        $uploadPath = $config->uploads->get('localPath');
        $tmpPath = $config->uploads->get('tmpPath');

        // Recipient
        $tmpElem = new Zend_Form_Element_Text('recipient');
        $tmpElem->setLabel('Destinatario:')
            ->setDecorators($this->elementDecorators)
            ->setRequired(true);
        $this->addElement($tmpElem);

        // Subject
        $tmpElem = new Zend_Form_Element_Text('subject');
        $tmpElem->setLabel('Soggetto:')
            ->setDecorators($this->elementDecorators)
            ->setRequired(true);
        $this->addElement($tmpElem);

        // Text
        $tmpElem = new Zend_Form_Element_Textarea('body',
            array('rows'=>3));
        $tmpElem->setLabel('Testo');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Attachment      
        $tmpElem = new Zend_Form_Element_File('currFile');
        $tmpElem->setLabel('Allegato')
                ->setDestination($tmpPath);
//                ->setValueDisabled(true)
        $tmpElem->setDecorators($this->fileElementDecorators);
        // ensure only 1 file
        $tmpElem->addValidator('Count', false, 1);
        //$tmpElem->setAttrib('onChange', 'addAttach(this.value)');
        //$tmpElem->addValidator('Count', false, array('min' => 1, 'max' => 3));
        //$tmpElem->setMultiFile(3);
        // limit to 1M
        $tmpElem->addValidator('Size', false, 102400000);
        // only JPEG, PNG, and GIFs
        $tmpElem->addValidator('Extension', false, 'jpg,png,gif');
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->addElement($tmpElem, 'currFile');
        $this->setAttrib('enctype', 'multipart/form-data');

        // Send
        $tmpElem = new Zend_Form_Element_Submit('send');
        $tmpElem->setLabel('Send')
            ->setDecorators($this->buttonDecorators);
        $this->addElement($tmpElem);

        $this->setDecorators(array(
                'FormElements',
                array('HtmlTag', array('tag' => 'table')),
                'Form' ));
    }
}