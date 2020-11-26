<?php

class Form_Doc extends Form_Myjqform
{

    public function init()
    {
        $db = Zend_Registry::get('db');
        $config = Zend_Registry::get('config');
        $uploadPath = $config->uploads->get('localPath');
        $tmpPath = $config->uploads->get('tmpPath');

        // idDocumento
        $tmpElem = new Custom_AlternateDate('idDocumento');
        $this->addElement($tmpElem);

        // idImpresa
        $tmpElem = new Custom_AlternateDate('idImpresa');
        $this->addElement($tmpElem);

        // idAppaltoImpresa
        $tmpElem = new Custom_AlternateDate('idAppaltoImpresa');
        $this->addElement($tmpElem);

        // idTab_Documento
        $sql = 'SELECT idTab_Documento AS KeyId,
                Documento AS Nome FROM tab_documenti';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idTab_Documento');
        $tmpElem->setLabel('Tipo documento');
        $tmpElem->setDecorators($this->elementDecorators);

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // Documento
        $tmpElem = new Zend_Form_Element_Text('Documento',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Nome');
        $tmpElem->setDecorators($this->elementDecorators);
        $this->addElement($tmpElem);

        // Arrivo
        $tmpElem = new Zend_Form_Element_Hidden('Arrivo');
        $this->addElement($tmpElem);

        // Attachment      
        $tmpElem = new Zend_Form_Element_File('currFile');
        $tmpElem->setLabel('Nuovo allegato')
                ->setDestination($tmpPath);
//                ->setValueDisabled(true)
        $tmpElem->setDecorators($this->fileElementDecorators);
        // ensure only 1 file
        $tmpElem->addValidator('Count', false, 1);
        //$tmpElem->setAttrib('onChange', 'addAttach(this.value)');
        //$tmpElem->addValidator('Count', false, array('min' => 1, 'max' => 3));
        //$tmpElem->setMultiFile(3);
        // limit to 1M
        $tmpElem->addValidator('Size', false, 10240000);
        // only JPEG, PNG, and GIFs
        //$tmpElem->addValidator('Extension', false, 'jpg,png,gif');
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->addElement($tmpElem, 'currFile');

        // TransferFiles
        $tmpElem = new Custom_AlternateDate('transFiles');
        $this->addElement($tmpElem);

        // Scadenza
        $altElem = new Custom_AlternateDate('Scadenza');
        $this->addElement($altElem);

        $tmpElem = new ZendX_JQuery_Form_Element_DatePicker(
            'Scadenza-alternate',
            array('Decorators' => $this->jqueryElementDecorators,
                'Locale' => 'it-IT',
                'FormatLength' => 'long',
                'DatePattern' => 'dd/MM/yy'));
        $tmpElem->setJQueryParams(array('dateFormat' => 'dd/mm/yy',
            'altField' => '#Scadenza',
            'regional' => 'it',
            'altFormat' => 'yy-mm-dd',
            'navigationAsDateFormat' => true));
        $tmpElem->setLabel('Data scadenza');
        $altElem->setDatePicker($tmpElem);
        $this->addElement($tmpElem);

        // Verificato
        $tmpElem = new Zend_Form_Element_Checkbox('Verificato');
        $tmpElem->setDecorators($this->checkboxDecorators)
            ->setLabel('Verificato');
        $this->addElement($tmpElem);

        // Completo
        $tmpElem = new Zend_Form_Element_Checkbox('Completo');
        $tmpElem->setDecorators($this->checkboxDecorators)
            ->setLabel('Completo');
        $this->addElement($tmpElem);

        // Note
        $tmpElem = new Zend_Form_Element_Textarea('Note',
                        array('rows' => 3));
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