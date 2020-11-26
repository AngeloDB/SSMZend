<?php

class Form_Eventisub extends Form_Myform
{

    public function init()
    {
//        parent::init();
        $config = Zend_Registry::get('config');
        $uploadPath = $config->uploads->get('localPath');
        $tmpPath = $config->uploads->get('tmpPath');

        $db = Zend_Registry::get('db');

        // idEvento
        $tmpElem = new Custom_AlternateDate('idEvento');
        $this->addElement($tmpElem);

        // idCantiere
        $tmpElem = new Custom_AlternateDate('idCantiere');
        $this->addElement($tmpElem);

        // idTecnico
        $tmpElem = new Custom_AlternateDate('idTecnico');
        $this->addElement($tmpElem);

        // Titolo        
        $tmpElem = new Zend_Form_Element_Text('Titolo',
                        array('size' => 50,
                            'maxLenght' => 50));
        $tmpElem->setLabel('Titolo');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // Descrizione        
        $tmpElem = new Zend_Form_Element_Text('Descrizione',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Descrizione');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // idEvento
        $tmpElem = new Zend_Form_Element_Hidden('Data');
        $this->addElement($tmpElem);

        // Data
//        $this->addElement('DateTextBox', 'Data',
//                array('Decorators'   => $this->dojoElementDecorators,
//                    'Label'        => 'Data inizio',
//                    'Locale'       => 'it-IT',
//                    'FormatLength' => 'long',
//                    'DatePattern'  => 'dd/MM/yyyy'));

        $tmpElem = new ZendX_JQuery_Form_Element_DatePicker(
                        'Data_alternate',
                        array('Decorators' => array('UiWidgetElement',
                                'Errors',
                                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'input_cell')),
                                array('Label', array('tag' => 'td', 'class' => 'label_cell')),
                                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'RowData'))
                            ),
                            'Label' => 'Data:',
                            'Locale' => 'it-IT',
                            'FormatLength' => 'long',
                            'DatePattern' => 'dd/MM/yy')
        );
        $tmpElem->setJQueryParams(array('dateFormat' => 'dd/mm/yy',
            'altField' => '#Data',
            'altFormat' => 'yy-mm-dd'));

        $this->addElement($tmpElem);

        // OraInizio

        $tmpElem = new Zend_Form_Element_Select('OraInizio');
        $tmpElem->setLabel('Ora inizio');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));

        $tmpElem->addMultiOption('', '');
        $tmpElem->addMultiOption('24:00', 'Tutto il giorno');
        for ($i = 360; $i < 1440; $i+=30)
        {
            $time = sprintf('%02.2d:%02.2d', trim($i / 60), $i % 60);
            $tmpElem->addMultiOption($time, $time);
        }
        $this->addElement($tmpElem);

        // OraFine

        $tmpElem = new Zend_Form_Element_Select('OraFine');
        $tmpElem->setLabel('Ora fine');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));

        $tmpElem->addMultiOption('', '');
        $tmpElem->addMultiOption('24:00', 'Tutto il giorno');
        for ($i = 360; $i < 1440; $i+=30)
        {
            $time = sprintf('%02.2d:%02.2d', trim($i / 60), $i % 60);
            $tmpElem->addMultiOption($time, $time);
        }
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
        $tmpElem->addValidator('Size', false, 102400000);
        // only JPEG, PNG, and GIFs
        $tmpElem->addValidator('Extension', false, 'jpg,png,gif');
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->addElement($tmpElem, 'currFile');

        // TransferFiles
        $tmpElem = new Custom_AlternateDate('transFiles');
        $this->addElement($tmpElem);

        // idTipoEvento
        $sql = 'SELECT idTipoEvento AS KeyId,
                TipoEvento AS Nome FROM tab_eventi';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idTipoEvento');
        $tmpElem->setLabel('Tipo');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $tmpElem->setAttrib('onChange', 'selectPage(this.selectedIndex)');

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // idDitta
        $sql = 'SELECT idImpresa AS KeyId,
                Impresa AS Nome FROM imprese';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idImpresa');
        $tmpElem->setLabel('Impresa');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // Luogo
        $tmpElem = new Zend_Form_Element_Textarea('Luogo',
                        array('rows' => 3));
        $tmpElem->setLabel('Luogo');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // Persone
        $tmpElem = new Zend_Form_Element_Textarea('Persone',
                        array('rows' => 3));
        $tmpElem->setLabel('Persone');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // Strumenti
        $tmpElem = new Zend_Form_Element_Textarea('Strumenti',
                        array('rows' => 3));
        $tmpElem->setLabel('Strumenti');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // Note
        $tmpElem = new Zend_Form_Element_Textarea('Note',
                        array('rows' => 3));
        $tmpElem->setLabel('Note');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // idLivAlert
        $sql = 'SELECT idTab_Livalert AS KeyId,
                Descrizione AS Nome FROM tab_livalert';
        $resSelect = $db->fetchAll($sql);

        $tmpElem = new Zend_Form_Element_Select('idLivAlert');
        $tmpElem->setLabel('Liv. Alert');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));

        $tmpElem->addMultiOption('', '');
        foreach ($resSelect as $selValue)
        {
            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Nome']);
        }
        $this->addElement($tmpElem);

        // Risultato        
        $tmpElem = new Zend_Form_Element_Text('Risultato',
                        array('size' => 80,
                            'maxLenght' => 80));
        $tmpElem->setLabel('Risultato');
        $tmpElem->setDecorators($this->elementDecorators);
        $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
        $this->addElement($tmpElem);

        // Conferma
        $tmpElem = new Zend_Form_Element_Submit('Conferma',
                        array('label' => 'Conferma'));
        $tmpElem->setDecorators($this->buttonDecorators);
        $tmpElem->setAttrib('onClick', 
                '$("#form2container").dialog("close"); $("#calendar").fullCalendar("prev"); $("#calendar").fullCalendar("next"); return true;');
        $this->addElement($tmpElem);


        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
        $this->addAttribs(array('id' => 'respform',
            'name' => 'respform'));
//        'onSubmit' => 'setResp();'));
//        Zend_Dojo::enableForm($this);
    }

    public function isValid($data)
    {

        $oldname = pathinfo($this->currFile->getFileName());
        $newname = uniqid('', true) . '.' . $oldname['extension'];
        $this->currFile->addFilter('Rename', $newname);
        return parent::isValid($data);
    }

}