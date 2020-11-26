<?php

/**
 * Description of XmlForm
 *
 * @author Italo
 */
class Custom_XmlForm
    {

    private $currXml;
    private $db;
    private $form;
    private $currTable;
    private $currKey;
    private $currId;
    private $formValues;
    private $formFiles            = array();
    private $formWarnings         = array();
    private $uploadPath           = '';
    private $tmpPath              = '';
    private $customFun            = array();
    private $notToDisable         = array();
    private $currParameters       = array();
    private $OnFileNameChange     = 'checkFn(this);';
    private $OnRwSubmit           = 'history.back();return false';
    private $OnRwSubmitAddon      = 'history.back();';
    private $savingAlertCallAddonMode = true;
    protected $selectAlert          = '';
    protected $multiSelectAlert     = '';
    protected $savingAlertCall      = '';
    protected $savingAlertCallAddon = '';
//    private $selectAlert          = '<h4 class="aiutosuriga">Selezionare dalla lista</h4>';
//    private $multiSelectAlert     = '<h4 class="aiutosuriga">
//        Premere il pulsante <span class="aiutopulsantepiu"> + </span>
//        nella colonna a destra per selezionare dall\'elenco </h4>';
//    private $savingAlertCall      = '
//creatediv(this, "attendereprego", 
//    "<H1><CENTER>Attendere prego, trasferimento in corso...</CENTER></H1>");return true;';
//    private $savingAlertCallAddon = '
//creatediv(this, "attendereprego", 
//    "<H1><CENTER>Attendere prego, trasferimento in corso...</CENTER></H1>");';
    private $warningAlertScript   = '
$(document).ready(function() 
    {
    $( "#dialog" ).dialog(
        {
        modal: true,
        buttons: 
           {
           Ok: function() 
               {
               $( this ).dialog( "close" );
               }
           }
        });
    });';
    private $savingAlertScript    = '
            
function creatediv(caller, id, html) 
    {
    var newdiv = document.createElement("div");
    newdiv.setAttribute("id", id);
    newdiv.style.position = "fixed";
    newdiv.style.top = "20%";   
    newdiv.style.zindex = "200000000!important";
    newdiv.style.background = "#FEFECE";
    newdiv.style.border = "4px solid #000";
    newdiv.style.padding = "1.2em";
    newdiv.innerHTML = html;
    $(caller.form).parent("div").append(newdiv);
    var newLeft = $(window).scrollLeft()+($(window).width()-$(newdiv).width())/2;
    if(newLeft<0) newLeft=0;
    var newTop = $(window).scrollTop()+($(window).height()-$(newdiv).height())/2;
    if(newTop<0) newTop=0;    
//   $(newdiv).css("top",newTop);
    $(newdiv).css("left",newLeft);
    }             
';

//'
//            
//function creatediv(caller, id, html) 
//    {
//    var newdiv = document.createElement("div");
//    newdiv.setAttribute("id", id);
//    newdiv.style.position = "absolute";       
//    newdiv.style.zindex = "200000000!important";
//    newdiv.style.background = "#FEFECE";
//    newdiv.style.border = "4px solid #000";
//    newdiv.style.padding = "1.2em";
//    newdiv.innerHTML = html;
//    $(caller.form).parent("div").append(newdiv);
//    var newLeft = ($(caller.form).parent("div").width()-$(newdiv).width())/2;
//    if(newLeft<0) newLeft=0;
//    var newTop = ($(caller.form).parent("div").height()-$(newdiv).height())/2;
//    if(newTop<0) newTop=0;    
//    $(newdiv).css("top",newTop);
//    $(newdiv).css("left",newLeft);
//    }             
//'    ;    


    private function doTranslate($toTranslate)
        {
        $translate = Zend_Registry::get('translate');
        return $translate->_($toTranslate);
        }


    public function init()
        {
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
        }

    public function initForm($db, $currEdit, $currId = '', $params = array())
        {
        $this->selectAlert          = '<h4 class="aiutosuriga">'.
                $this->doTranslate('Selezionare dalla lista').'</h4>';
        $this->multiSelectAlert     = '<h4 class="aiutosuriga">'.
        $this->doTranslate('Premere il pulsante <span class="aiutopulsantepiu"> + </span>nella colonna a destra per selezionare dall\'elenco').
                '</h4>';
        $this->savingAlertCall      = '
creatediv(this, "attendereprego", 
    "<H1><CENTER>'.$this->doTranslate('Attendere prego&#44; trasferimento in corso...').'</CENTER></H1>");return true;';
        if($this->savingAlertCallAddonMode)
            {
            $this->savingAlertCallAddon = '
    creatediv(this, "attendereprego", 
        "<H1><CENTER>'.$this->doTranslate('Attendere prego&#44; trasferimento in corso...').'</CENTER></H1>");';
            }
        $value            = '';
        $formData         = '';
        $this->RWmode     = true;
        $config           = Zend_Registry::get('config');
        $registry         = Zend_Registry::getInstance();
        $this->db         = $db;
        $this->uploadPath = $config->uploads->get('localPath');
        $this->tmpPath    = $config->uploads->get('tmpPath');
        $this->currTable  = $currEdit;
        $this->currKey    = $currId;
        $this->javascript = '';
        $savingAlert      = false;
        $tabGuestKey      = '';
        $maxFiles         = 3;
        $maxFileSize      = 1048576;
        $haveSimpleUploads= false;
        $haveUploads      = false;
        $addinfo          = array();
        $helpElem         = '';
        $fileExtensions   = 'jpg,png,gif';
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl          = $defaultNamespace->baseUrl;
        $configMenu       = new Zend_Config_Xml(APPLICATION_PATH .
                '/configs/edits/' . $currEdit . '.xml', $currEdit);
        $currXml          = $configMenu->toArray();
//        $this->addWarning("starting");
        $this->currXml    = $currXml;
        if (key_exists('parameters', $currXml))
            {
            $parameterX = $currXml['parameters'];
            foreach ($parameterX as $key => $currPar)
                {
                switch (trim($currPar['from']))
                    {
                    case 'arrdefaultnamespace':
                        $tmp                        = $defaultNamespace->__get($currPar['array']);
                        $this->currParameters[$key] = $tmp[$currPar['offset']];
                        break;

                    case 'arrpar':
                        $this->currParameters[$key] = $value[$currPar['value']];
                        break;

                    case 'constant':
                        $this->currParameters[$key] = $currPar['value'];
                        break;

                    case 'defaultnamespace':
                        $this->currParameters[$key] = $defaultNamespace->__get($key);
                        break;

                    case 'pararr':
                        if (key_exists($key, $params))
                            {
                            $this->currParameters[$key] = $params[$key];
                            }
                        break;

                    case 'parameter':
                        if (key_exists($key, $params))
                            {
                            $this->currParameters[$key] = $params[$key];
                            }
                        break;

                    case 'registry':
                        $this->currParameters[$key] = $registry[$currPar['offset']];
                        break;

                    case 'cond':
                        $resTrue  = 1;
                        $resFalse = 0;
                        if (key_exists('iftrue', $currPar))
                            {
                            $resTrue = $this->substPar($currPar['iftrue'], $this->currParameters);
                            }
                        if (key_exists('iffalse', $currPar))
                            {
                            $resFalse = $this->substPar($currPar['iffalse'], $this->currParameters);
                            }
                        switch (trim($currPar['type']))
                            {
                            case 'eq':
                                if ($this->substPar($currPar['op1'], $this->currParameters) ==
                                        $this->substPar($currPar['op2'], $this->currParameters))
                                    {
                                    $this->currParameters[$key] = $resTrue;
                                    }
                                else
                                    {
                                    $this->currParameters[$key] = $resFalse;
                                    }
                                break;

                            case 'in':
                                if (strpos($this->substPar($currPar['op1'], $this->currParameters), $this->substPar($currPar['op2'], $this->currParameters)) !== false)
                                    {
                                    $this->currParameters[$key] = $resTrue;
                                    }
                                else
                                    {
                                    $this->currParameters[$key] = $resFalse;
                                    }
                                break;

                            case 'neq':
                                if ($this->substPar($currPar['op1'], $this->currParameters) !=
                                        $this->substPar($currPar['op2'], $this->currParameters))
                                    {
                                    $this->currParameters[$key] = $resTrue;
                                    }
                                else
                                    {
                                    $this->currParameters[$key] = $resFalse;
                                    }
                                break;
                            }
                        break;

                    default:
                        break;
                    }
                }
            }
//        $warningsAlert = false;
//        if (count($this->formWarnings) > 0)
//            {
//            $warningsAlert = true;
//            if (!key_exists('javascript', $this->currXml))
//                $this->currXml['javascript'] = '';
//            }

        if (key_exists('savingalert', $this->currXml))
            {
            $savingAlert                 = true;
            if (!key_exists('javascript', $this->currXml))
                $this->currXml['javascript'] = '';
            }
        if (key_exists('javascript', $this->currXml))
            {
            $this->javascript = '<script type="text/javascript"> ' .
                    htmlspecialchars_decode($this->substPar(
                                    $this->currXml['javascript'], $this->currParameters));
//            if ($warningsAlert)
//                $this->javascript.=$this->warningAlertScript;
            if ($savingAlert)
                $this->javascript.=$this->savingAlertScript;
            $this->javascript.='</script> ';
            }
        if (key_exists('customcalls', $this->currXml))
            {
            $currCall = $this->currXml['customcalls'];
            if (is_array($currCall))
                {
                foreach ($currCall as $key => $currCallback)
                    {
                    $this->customFun[$key] = $currCallback;
                    }
                }
            }
        $currXml = $this->substIf($this->currXml, $this->currParameters);
        if (key_exists('mode', $currXml))
            {
            $this->RWmode = $this->substPar($currXml['mode'], $this->currParameters);
            }
        else if ($defaultNamespace->aclReadonly == 1)
            {
            $this->RWmode = 0;
            }

//        echo "Rwmode " . $this->RWmode;
        if (key_exists('onRwSubmit', $currXml))
            {
            $this->OnRwSubmit = $this->substPar($currXml['onRwSubmit'], $this->currParameters);
            }
        $currKey          = $currXml['currkey'];
        $currSql          = $currXml['currsql'];
        $formDef          = $currXml['form']['elements'];
        $this->form       = new Form_Myjqform();
        $this->formValues = array();
        $this->form->setName($currXml['form']['name'])
                ->setMethod('post')
                ->setAttrib('accept-charset', 'utf-8');
        if ($currId == '')
            {
            $this->form->setAction($baseUrl . '/crud/new/edt/' . $currEdit);
            }
        else
            {
            $this->form->setAction($baseUrl . '/crud/edit/id/' . $currId .
                    '/edt/' . $currEdit);
            $sql       = $currSql . $currKey . '=' . $currId;
            $sql       = $this->substPar($sql, $this->currParameters);
            $arrFields = $db->fetchRow($sql);
            }
        foreach ($formDef as $elemId => $elemDef)
            {
            if (key_exists('value', $elemDef))
                {
                $this->formValues[$elemId] = $elemDef['value'];
                }

            if (!key_exists('type', $elemDef))
                {
                continue;
                }
            switch ($elemDef['type'])
                {
                case 'button':
                    $tmpElem = new Zend_Form_Element_Button($elemId, array('label' => $elemDef['label']));
                    $tmpElem->setDecorators($this->form->buttonDecorators);
                    break;

                case 'checkbox':
                    $tmpElem = new Zend_Form_Element_Checkbox($elemId);
                    $tmpElem->setDecorators($this->form->checkboxDecorators)
                            ->setLabel($elemDef['label']);
                    break;

                case 'custom':
                    $tmpElem = new CustomElement($elemId);
                    $tmpElem->setDecorators($this->form->customElementDecorator);
                    if (key_exists('label', $elemDef))
                        $tmpElem->setLabel($elemDef['label']);
                    if (key_exists('description', $elemDef))
                        $tmpElem->setDescription($this->doTranslate($this->substPar($elemDef['description'], $this->currParameters)));
                    break;

                case 'date':
                    $altElem = new Custom_AlternateDate($elemId);
                    $this->form->addElement($altElem);

                    $tmpElem = new ZendX_JQuery_Form_Element_DatePicker(
                            $elemId . '-alternate', array('Decorators'   => $this->form->jqueryElementDecorators,
                        'Locale'       => 'it-IT',
                        'FormatLength' => 'long',
                        'DatePattern'  => 'dd/MM/yy'));
                    $tmpElem->setJQueryParams(array('dateFormat'             => 'dd/mm/yy',
                        'altField'               => '#' . $elemId,
                        'regional'               => 'it',
                        'altFormat'              => 'yy-mm-dd',
                        'changeMonth'            => true,
                        'changeYear'             => true,
                        'navigationAsDateFormat' => true));
                    $tmpElem->setLabel($elemDef['parameters']['Label']);
//                    $tmpElem->addValidator(new Zend_Validate_Callback(
//                            array($tmpElem,'reverseDate')));
                    $altElem->setDatePicker($tmpElem);
                    break;

                case 'file' :
                    $haveUploads = true;
                    if ($tabGuestKey == '')
                        {
                        $tabGuestKey = $this->findTableGuest(
                                $currXml['maintable']);
                        }
                    if (key_exists('parameters', $elemDef))
                        {
                        $params = $elemDef['parameters'];
                        if (key_exists('MultiFile', $params))
                            {
                            $maxFiles = $params['MultiFile'];
                            }
                        else
                            {
                            $maxFiles = 1;
                            }
                        if (key_exists('Size', $params))
                            {
                            $maxFileSize = $params['Size'];
                            }
                        if (key_exists('Extensions', $params))
                            {
                            $fileExtensions = $params['Extensions'];
                            }
                        }
                    if (key_exists('addinfo', $elemDef))
                        {
                        $addinfo = $elemDef['addinfo'];
                        }
                    $guestFiles = $this->findGuestFiles($tabGuestKey, $elemId, $currId);
                    foreach ($guestFiles as $key => $currFile)
                        {
                        $altElem = new Zend_Form_Element_Hidden('todel_' . $elemId .
                                '_' . substr($currFile['StoreName'], 0, 4));
                        $altElem->setValue('+#' . $currFile['idAllegato'] . '#' .
                                $currFile['StoreName']);
                        $this->form->addElement($altElem);
                        }
                    //$tmpElem->setAttrib('onChange', 'addAttach(this.value)');
                    $mfElem  = new Custom_AlternateDate('maxfiles_' . $elemId);
                    $mfElem->setValue($maxFiles);
                    $this->form->addElement($mfElem);
                    $tmpElem = new Zend_Form_Element_File($elemId);
                    $tmpElem->setLabel($this->doTranslate($elemDef['label']))
                            ->setDestination($this->tmpPath);
                    $tmpElem->setDecorators($this->form->fileElementDecorators);
                    $tmpElem->addValidator('Count', false, array('min' => 1,
                        'max' => $maxFiles));
                    $tmpElem->setMultiFile($maxFiles);
                    $tmpElem->addValidator('Size', false, $maxFileSize);
                    $tmpElem->addValidator('Extension', false, $fileExtensions);
//                    $tmpElem->addValidator('Callback', true, array('messages' =>
//                        array(Zend_Validate_Callback::INVALID_VALUE =>
//                            'Nome file non valido'),
//                        array('callback' => array($this, 'checkCf')),
//                        array('callbackOptions' => false)));
                    $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
                    $tmpElem->addDecorator('Callback', array('placement' => 'prepend',
                        'callback'  => array('Custom_CbDecorators', 'file'),
                        'options'   => array('files'   => $guestFiles,
                            'label'   => $elemDef['label'],
                            'addinfo' => $addinfo)));

//                    $tmpElem->setDescription(
//                            '<script type="text/javascript">this.numMax_'.
//                            $elemId.' = '.$maxFiles.';</script>');
//                    $this->form->addElement($tmpElem);
                    //$tmpElem->setValueDisabled(true);
                    if (!key_exists('javascript', $elemDef))
                        {
                        $elemDef['javascript'] = array();
                        }
                    if (key_exists('onChange', $elemDef['javascript']))
                        {
                        $elemDef['javascript']['onChange'] = $this->OnFileNameChange .
                                $elemDef['javascript']['onChange'];
                        }
                    else
                        {
                        $elemDef['javascript']['onChange'] = $this->OnFileNameChange;
                        }
                    $this->form->setAttrib('enctype', 'multipart/form-data');
                    $this->formFiles[] = $tmpElem;
                    break;

                case 'simplefile' :
                    $haveSimpleUploads = true;
//                    if ($tabGuestKey == '')
//                        {
//                        $tabGuestKey = $this->findTableGuest(
//                                $currXml['maintable']);
//                        }
                    if (key_exists('parameters', $elemDef))
                        {
                        $params = $elemDef['parameters'];
                        if (key_exists('MultiFile', $params))
                            {
                            $maxFiles = $params['MultiFile'];
                            }
                        else
                            {
                            $maxFiles = 1;
                            }
                        if (key_exists('Size', $params))
                            {
                            $maxFileSize = $params['Size'];
                            }
                        if (key_exists('Extensions', $params))
                            {
                            $fileExtensions = $params['Extensions'];
                            }
                        }
                    if (key_exists('addinfo', $elemDef))
                        {
                        $addinfo = $elemDef['addinfo'];
                        }
//                    $guestFiles = $this->findGuestFiles($tabGuestKey, $elemId, $currId);
//                    foreach ($guestFiles as $key => $currFile)
//                        {
//                        $altElem = new Zend_Form_Element_Hidden('todel_' . $elemId .
//                                '_' . substr($currFile['StoreName'], 0, 4));
//                        $altElem->setValue('+#' . $currFile['idAllegato'] . '#' .
//                                $currFile['StoreName']);
//                        $this->form->addElement($altElem);
//                        }
                    //$tmpElem->setAttrib('onChange', 'addAttach(this.value)');
                    $mfElem  = new Custom_AlternateDate('maxfiles_' . $elemId);
                    $mfElem->setValue($maxFiles);
                    $this->form->addElement($mfElem);
                    $tmpElem = new Zend_Form_Element_File($elemId);
                    $tmpElem->setLabel($this->doTranslate($elemDef['label']))
                            ->setDestination($this->tmpPath);
                    $tmpElem->setDecorators($this->form->fileElementDecorators);
                    $tmpElem->addValidator('Count', false, array('min' => 1,
                        'max' => $maxFiles));
                    $tmpElem->setMultiFile($maxFiles);
                    $tmpElem->addValidator('Size', false, $maxFileSize);
                    $tmpElem->addValidator('Extension', false, $fileExtensions);
//                    $tmpElem->addValidator('Callback', true, array('messages' =>
//                        array(Zend_Validate_Callback::INVALID_VALUE =>
//                            'Nome file non valido'),
//                        array('callback' => array($this, 'checkCf')),
//                        array('callbackOptions' => false)));
                    $tmpElem->addDecorator(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'Row' . $tmpElem->getId()));
//                    $tmpElem->addDecorator('Callback', array('placement' => 'prepend',
//                        'callback'  => array('Custom_CbDecorators', 'file'),
//                        'options'   => array('files'   => $guestFiles,
//                            'label'   => $elemDef['label'],
//                            'addinfo' => $addinfo)));

//                    $tmpElem->setDescription(
//                            '<script type="text/javascript">this.numMax_'.
//                            $elemId.' = '.$maxFiles.';</script>');
//                    $this->form->addElement($tmpElem);
                    //$tmpElem->setValueDisabled(true);
                    if (!key_exists('javascript', $elemDef))
                        {
                        $elemDef['javascript'] = array();
                        }
                    if (key_exists('onChange', $elemDef['javascript']))
                        {
                        $elemDef['javascript']['onChange'] = $this->OnFileNameChange .
                                $elemDef['javascript']['onChange'];
                        }
                    else
                        {
                        $elemDef['javascript']['onChange'] = $this->OnFileNameChange;
                        }
                    $this->form->setAttrib('enctype', 'multipart/form-data');
                    $this->formFiles[] = $tmpElem;
                    break;

                case 'hidden':
                    $tmpElem = new Custom_AlternateDate($elemId);
                    break;

                case 'hiddendate':
                    $tmpElem = new Zend_Form_Element_Hidden($elemId);
                    break;

                case 'linecustom':
                    $tmpElem = new CustomElement($elemId);
                    $tmpElem->setDecorators($this->form->lineCustomElementDecorator)
                            ->setDescription($this->doTranslate($elemDef['description']));
                    break;

                case 'multicheck':
                    $sql       = $this->substPar($elemDef['selsql'], $this->currParameters);
                    $resSelect = $db->fetchAll($sql);
                    $tmpElem   = new Zend_Form_Element_MultiCheckbox($elemId);
                    //$tmpElem->setLabel($elemDef['label']);
                    //$tmpElem->setDecorators($this->form->buttonDecorators);
                    $tmpElem->setDecorators($this->form->elementDecorators);
                    $tmpElem->setAttrib('checkedValue', '1');
                    $tmpElem->setAttrib('uncheckedValue', '0');
                    foreach ($resSelect as $selValue)
                        {
                        $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Value']);
                        }
                    break;

                case 'multiselect':
                    $tmpElem   = new Zend_Form_Element_Multiselect($elemId);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->form->elementDecorators);
                    $tmpElem->setAttrib('class', 'multiselect');
                    if (key_exists('selsql', $elemDef))
                        {
                        $sql       = $this->substPar($elemDef['selsql'], $this->currParameters);
                        $resSelect = $db->fetchAll($sql);
                        foreach ($resSelect as $selValue)
                            {
                            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Value']);
                            }
                        }
                    else
                        {
                        $selValues = $elemDef['values'];
                        foreach ($selValues['value'] as $elem)
                            {
                            $tmpElem->addMultiOption($elem['id'], $elem['elem']);
                            }
                        }
                    if (key_exists('aiuto', $elemDef))
                        {
                        $helpElem = new CustomElement($elemId . '_aiuto');
                        $helpElem->setDecorators($this->form->customElementDecorator);
                        $currHelp = $elemDef['aiuto'];
                        if ($currHelp == 'standard')
                            $currHelp = $this->multiSelectAlert;
                        $helpElem->setDescription($currHelp);
                        }
                    break;

                case 'password':
                    $tmpElem = new Zend_Form_Element_Password($elemId, $elemDef['parameters']);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->form->elementDecorators);

                    break;

                case 'select':
                    $tmpElem = new Zend_Form_Element_Select($elemId);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->form->elementDecorators);
                    $tmpElem->addMultiOption('', '');
                    if (key_exists('selsql', $elemDef))
                        {
                        $sql       = $this->substPar($elemDef['selsql'], $this->currParameters);
                        $resSelect = $db->fetchAll($sql);
                        foreach ($resSelect as $selValue)
                            {
                            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Value']);
                            //                                mb_convert_encoding($selValue['Value'], 
                            //                                        'HTML-ENTITIES', "UTF-8"));
                            }
                        }
                    else
                        {
                        $selValues = $elemDef['values'];
                        foreach ($selValues['value'] as $elem)
                            {
                            $tmpElem->addMultiOption($elem['id'], $elem['elem']);
                            }
                        }
                    if (key_exists('aiuto', $elemDef))
                        {
                        $helpElem = new CustomElement($elemId . '_aiuto');
                        $helpElem->setDecorators($this->form->customElementDecorator);
                        $currHelp = $elemDef['aiuto'];
                        if ($currHelp == 'standard')
                            $currHelp = $this->selectAlert;
                        $helpElem->setDescription($this->doTranslate($currHelp));
                        }
                    break;

                case 'radio':
                    $tmpElem = new Zend_Form_Element_Radio($elemId);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->form->elementDecorators);
                    $tmpElem->setValue(-1);
                    if (key_exists('separator', $elemDef))
                        {
                        $tmpElem->setSeparator($elemDef['separator']);
                        }
//                    $tmpElem->addMultiOption('', '');
                    if (key_exists('selsql', $elemDef))
                        {
                        $sql       = $this->substPar($elemDef['selsql'], $this->currParameters);
                        $resSelect = $db->fetchAll($sql);
                        foreach ($resSelect as $selValue)
                            {
                            $tmpElem->addMultiOption($selValue['KeyId'], $selValue['Value']);
                            //                                mb_convert_encoding($selValue['Value'], 
                            //                                        'HTML-ENTITIES', "UTF-8"));
                            }
                        }
                    else
                        {
                        $selValues = $elemDef['values'];
                        foreach ($selValues['value'] as $elem)
                            {
                            $tmpElem->addMultiOption($elem['id'], $elem['elem']);
                            }
                        }
                    if (key_exists('aiuto', $elemDef))
                        {
                        $helpElem = new CustomElement($elemId . '_aiuto');
                        $helpElem->setDecorators($this->form->customElementDecorator);
                        $currHelp = $elemDef['aiuto'];
                        if ($currHelp == 'standard')
                            $currHelp = $this->selectAlert;
                        $helpElem->setDescription($currHelp);
                        }
                    break;

//                case 'submitresetdelete':
//                    if (key_exists('dellabel', $elemDef))
//                        $resetLabel = $elemDef['dellabel'];
//                    else
//                        $resetLabel = 'Delete';
//                    $tmpElem = new Zend_Form_Element_Reset($elemId . '-delete', array('label' => $resetLabel));
//                    if (!key_exists('jsdelete', $elemDef))
//                        $resetScript = $this->OnRwSubmit;
//                    else
//                        $resetScript = $elemDef['jsdelete'];
//                    $tmpElem->setDecorators($this->form->resetDecorators)
//                            ->setAttrib('onClick', $resetScript);
//                    $this->notToDisable[] = $elemId . '-reset';
//                    $this->form->addElement($tmpElem);
                case 'submitreset':
                    if (key_exists('resetlabel', $elemDef))
                        $resetLabel = $elemDef['resetlabel'];
                    else
                        $resetLabel = 'Annulla';
                    $tmpElem = new Zend_Form_Element_Reset($elemId . '-reset', array('label' => $resetLabel));
                    if (!key_exists('jsreset', $elemDef))
                        $resetScript = $this->OnRwSubmit;
                    else
                        $resetScript = $elemDef['jsreset'];
                    $tmpElem->setDecorators($this->form->resetDecorators)
                            ->setAttrib('onClick', $resetScript);
                    $this->notToDisable[] = $elemId . '-reset';
                    $this->form->addElement($tmpElem);
                case 'submit':
                    $tmpElem = new Zend_Form_Element_Submit($elemId, array('label' => $elemDef['label']));
                    $tmpElem->setDecorators($this->form->resSubmitDecorators);
                    $this->notToDisable[] = $elemId;
                    if (!key_exists('javascript', $elemDef))
                        {
                        $elemDef['javascript'] = array();
                        }
                    if (!$this->RWmode)
                        {
                        if (key_exists('onClick', $elemDef['javascript']))
                            {
                            $elemDef['javascript']['onClick'] = $this->OnRwSubmitAddon .
                                    $elemDef['javascript']['onClick'];
                            }
                        else
                            {
                            $elemDef['javascript']['onClick'] = $this->OnRwSubmit;
                            }
                        }
                    else
                        {
                        if (key_exists('onClick', $elemDef['javascript']))
                            {
                            $elemDef['javascript']['onClick'] = $this->savingAlertCallAddon .
                                    $elemDef['javascript']['onClick'];
                            }
                        else
                            {
                            $elemDef['javascript']['onClick'] = $this->savingAlertCall;
                            }
                        }
                    break;

//                case 'submitreset':
//                    if (key_exists('resetlabel', $elemDef))
//                        $resetLabel = $elemDef['resetlabel'];
//                    else
//                        $resetLabel = 'Annulla';
//                    $tmpElem = new Zend_Form_Element_Reset($elemId . '-reset',
//                                    array('label' => $resetLabel));
//                    if (!key_exists('jsreset', $elemDef))
//                        $resetScript = $this->OnRwSubmit;
//                    else
//                        $resetScript = $elemDef['jsreset'];
//                    $tmpElem->setDecorators($this->form->resetDecorators)
//                            ->setAttrib('onClick', $resetScript);
//                    $this->notToDisable[] = $elemId . '-reset';
//                    $this->form->addElement($tmpElem);
//                    $tmpElem = new Zend_Form_Element_Submit($elemId,
//                                    array('label' => $elemDef['label']));
//                    $tmpElem->setDecorators($this->form->submitDecorators);
//                    $this->notToDisable[] = $elemId;
//                    if (!key_exists('javascript', $elemDef))
//                        {
//                        $elemDef['javascript'] = array();
//                        }
//                    if (!$this->RWmode)
//                        {
//                        $elemDef['javascript']['onClick'] = $this->OnRwSubmit;
//                        }
//                    else
//                        {
//                        $elemDef['javascript']['onClick'] = $this->savingAlertCall;
//                        }
//                    break;

                case 'text':
                    $tmpElem = new Zend_Form_Element_Text($elemId, $elemDef['parameters']);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->form->elementDecorators);
                    break;

                case 'textarea':
                    $tmpElem = new Zend_Form_Element_Textarea($elemId, $elemDef['parameters']);
                    $tmpElem->setLabel($elemDef['label']);
                    $tmpElem->setDecorators($this->form->elementDecorators);
                    break;

                default:
                    break;
                }
            if (key_exists('notempty', $elemDef))
                {
                $cond = $elemDef['notempty'];
                if (($cond == 'true') || $cond)
                    {
                    $tmpElem->setLabel($tmpElem->getLabel() . ' *');
                    $tmpElem->setRequired(true)
                            ->addValidator('NotEmpty', true, array('messages' =>
                                array('isEmpty' => $this->doTranslate('Non può essere vuoto!'))));
                    }
                }
            if (key_exists('checkmail', $elemDef))
                {
                $cond = $elemDef['checkmail'];
                if (($cond == 'true') || $cond)
                    {
                    $errNoMail = $this->doTranslate('Indirizzo Email non valido!');
                    $tmpElem->setLabel($tmpElem->getLabel() . ' *');
                    $tmpElem->setRequired(true)
                            ->addValidator('EmailAddress', true, array('messages' =>
                                array(
                                    'emailAddressInvalid'          => $errNoMail,
                                    'emailAddressInvalidHostname'  => $errNoMail,
                                    'emailAddressInvalidMxRecord'  => $errNoMail,
                                    'emailAddressDotAtom'          => $errNoMail,
                                    'emailAddressQuotedString'     => $errNoMail,
                                    'emailAddressInvalidLocalPart' => $errNoMail)))
                            ->addErrorMessage($errNoMail);
                    }
                }
            if (key_exists('float', $elemDef))
                {
                $cond = $elemDef['float'];
                if (($cond == 'true') || $cond)
                    {
//                    $ValidateRange =  new Zend_Validate_Between(0, 999999.99);
//                    $ValidateRange->setMessage('Please enter the amount between [0-9999] ');
//                    $text->addValidator($ValidateRange);
//                    $textValidateFloat->setMessage('Please enter the amount properly');                  
                    $ValidateFloat = new Zend_Validate_Float();
                    $ValidateFloat->setLocale(new Zend_Locale('it_IT'));
                    $tmpElem->addValidator($ValidateFloat);
//                    $tmpElem->addValidator(new Zend_Validate_Float());
                    }
                }
            if (key_exists('checkiva', $elemDef))
                {
                $cond = $elemDef['checkiva'];
                if (($cond == 'true') || $cond)
                    {
                    $tmpElem->setLabel($tmpElem->getLabel() . ' *');
                    $tmpElem->setRequired(false)
                            ->addValidator('Callback', true, array('messages' =>
                                array(Zend_Validate_Callback::INVALID_VALUE =>
                                    $this->doTranslate('Partita IVA non valida')),
                                array('callback' => array($this, 'checkIva')),
                                array('callbackOptions' => false)));
                    }
                }
            if (key_exists('checkcodfisc', $elemDef))
                {
                $cond = $elemDef['checkcodfisc'];
                if (($cond == 'true') || $cond)
                    {
                    $tmpElem->setLabel($tmpElem->getLabel() . ' *');
                    $tmpElem->setRequired(false)
                            ->addValidator('Callback', true, array('messages' =>
                                array(Zend_Validate_Callback::INVALID_VALUE =>
                                    $this->doTranslate('Codice fiscale non valido')),
                                array('callback' => array($this, 'checkCf')),
                                array('callbackOptions' => false)));
                    }
                }
            if (key_exists('javascript', $elemDef))
                {
                $scripts = $elemDef['javascript'];
                foreach ($scripts as $scriptName => $scriptAction)
                    {
                    $tmpElem->setAttrib($scriptName, $this->substPar($scriptAction, $this->currParameters));
                    }
                }
            $this->form->addElement($tmpElem);
            if ($helpElem != '')
                {
                $this->form->addElement($helpElem);
                $helpElem = '';
                }
            }

        $this->form->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
        if ($haveUploads)
            {
            $this->form->setDescription('
<script type="text/javascript">
if("function"!=typeof setCanc)
{
    var setCanc=function(image, storeName) 
    {
        var currVal=$("#"+storeName).val().substr(1);
        if(image.alt=="Select")
        {
            image.src="' . $defaultNamespace->baseUrl . '/images/nao.png";
            image.alt="Cancellato";
            $("#"+storeName).val("-"+currVal);
        }
        else
        {
            image.src="' . $defaultNamespace->baseUrl . '/images/link.gif";
            image.alt="Select";
            $("#"+storeName).val("+"+currVal);
        }
    }
}

function checkFn(fileField)
    {
//    $fileField.value = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", \'\', $fileField.value);
    // Remove any runs of periods (thanks falstro!)
//    $fileField.value = mb_ereg_replace("([\.]{2,})", \'\', $fileField.value);
//    var regex=/[ab]/gi;
//    if(regex.test(fileField.value))
//        {
//        alert("Il nome del file "+fileField.value+" contiene caratteri non validi; Prego rinominare");
//        fileField.value="";
//        }
    }

</script>    
                ');
            $this->form->addDecorator('Description', array('tag'       => '',
                'openOnly'  => true,
                'placement' => 'prepend',
                'escape'    => false), 'Form');
            }
        $this->currXml = $currXml;
        foreach ($this->formValues as $elemId => $elemDef)
            {
            $this->form->getElement($elemId)->setValue($this->currParameters[trim($elemDef)]);
            }
        if ($currId != '' && count($arrFields) > 0)
                {
                foreach($arrFields as $key => $value)
                    {
                    if (key_exists($key, $this->currXml['form']['elements']))
                        {
                        $elemDef = $this->currXml['form']['elements'][$key];
                        if (key_exists('float', $elemDef))
                            {
                            $cond = $elemDef['float'];
                            if (($cond == 'true') || $cond)
                                {
                                if(!is_numeric($cond))
                                    {
                                    $arrFields[$key]= number_format($value, 2, ',', '.');
                                    }
                                else
                                    {
                                    $arrFields[$key]= number_format($value, $cond, ',', '.');                                    
                                    }
//                                $arrFields[$key]= str_replace('.', ',', $value);
                                }
                            }
                        }
                    }

            $this->form->populate($arrFields);
            }
        if (!$this->RWmode)
            {
            foreach ($this->form->getElements() as $element)
                {
                if (array_search($element->getName(), $this->notToDisable) === false)
                    {
                    $element->setAttrib('disabled', true);
                    $element->setAttrib('readonly', true);
                    //$element->helper = 'formNote';
                    }
                }
            }
//        return $this->javascript . $this->form;
        $this->callCustomType('afterinit', $formData, $this);
        return $this->form;
        }

    public function setReadOnly($roAction = '')
        {
        if ($roAction == '')
            {
            $roAction = $this->OnRwSubmit;
            }
        foreach ($this->form->getElements() as $element)
            {
            if (array_search($element->getName(), $this->notToDisable) === false)
                {
                $element->setAttrib('disabled', true);
                $element->setAttrib('readonly', true);
                }
            else
                {
                $element->setAttrib('onClick', $roAction);
                }
            }
        return $this->form;
        }

    private function substIf($currXml, $currParameters)
        {
        $retXml = array();
        foreach ($currXml as $currKey => $currIf)
            {
            if ($currKey === 'if')
                {
                $resTrue  = '';
                $keyTrue  = '';
                $resFalse = '';
                $keyFalse = '';
                $tmpKey   = '';
                if (key_exists('iftrue', $currIf))
                    {
//                    $resTrue=$this->substPar($currPar['iftrue'], $currParameters);                            
                    $resTrue = $currIf['iftrue'];
                    if (is_array($resTrue))
                        {
                        $tmpKey  = array_keys($resTrue);
                        $keyTrue = $tmpKey[0];
                        $resTrue = $resTrue[$keyTrue];
                        }
                    }
                if (key_exists('iffalse', $currIf))
                    {
//                    $resFalse=$this->substPar($currPar['iffalse'], $currParameters);                            
                    $resFalse = $currIf['iffalse'];
                    if (is_array($resFalse))
                        {
                        $tmpKey   = array_keys($resFalse);
                        $keyFalse = $tmpKey[0];
                        $resFalse = $resFalse[$keyFalse];
                        }
                    }
                if ($this->substPar($currIf['cond'], $currParameters))
                    {
                    if ($keyTrue != '')
                        $retXml[$keyTrue] = $resTrue;
                    }
                else
                    {
                    if ($keyFalse != '')
                        $retXml[$keyFalse] = $resFalse;
                    }
                }
            else if (is_array($currIf))
                {
                $retXml[$currKey] = $this->substIf($currIf, $currParameters);
                }
            else
                {
                $retXml[$currKey] = $currIf;
                }
            }
        return $retXml;
        }

    private function substPar($funct, $arrPar)
        {
        $out = '';
        if (strpos($funct, '{{') === false)
            {
            return $funct;
            }
        else
            {
            $arrTmp = explode('{{', $funct);
            foreach ($arrTmp as $currChunk)
                {
                if (strpos($currChunk, '}}') === false)
                    {
                    $out.=$currChunk;
                    }
                else
                    {
                    $arrChunk = explode('}}', $currChunk);
                    $out.=$arrPar[$arrChunk[0]] . $arrChunk[1];
                    }
                }
            return $out;
            }
        }

    public function saveData($formData)
        {
        if ($this->form->isValidPartial($formData))
            {
            $translate = Zend_Registry::get('translate');
            $compLabel = $translate->_($this->currXml['form']['elements']['Conferma']['label']);
            if ($formData['Conferma'] == $compLabel)
                {
                if (!$this->callCustomType('beforesave', $formData, $this))
                    return false;
                if (!$this->checkMaxFiles($formData, $_FILES))
                    return false;
                $this->delAlleg($formData);
                $cliTable     = new Zend_Db_Table($this->currXml['maintable']);
                $tableCols    = $cliTable->info('cols');
                $currKey      = $this->currXml['currkey'];
                $this->currId = $currId       = $formData[$currKey];
                $this->form->getValues();
                $tabGuestKey  = $this->findTableGuest($this->currXml['maintable']);
                foreach ($_FILES as $currFileName => $currFile)
                    {
                    if ($currFile['error'] == 0 && $currFile['size'] > 0)
                        {
                        $tmp     = preg_replace('/[^a-z0-9.]+/i', "_", strtolower($currFile['name']));
                        $newName = substr(md5(microtime()), 1, 4) . $tmp;
                        $answ    = rename($currFile['tmp_name'], $this->uploadPath . '/' . $newName);
                        if (!$answ)
                            {
                            $answ = rename($this->uploadPath . '/tmp/' .
                                    $currFile['name'], $this->uploadPath . '/' . $newName);
                            if (!$answ)
                                die("Non posso rinominare " . $currFile['tmp_name'] .
                                        ' in ' . $this->uploadPath . '/tmp/' . $currFile['name'] .
                                        ' in ' . $this->uploadPath . '/' . $newName);
                            }
                        chmod($this->uploadPath . '/' . $newName, 0644);
                        $allArr               = $this->findAllPar($formData, $currFileName);
                        $allArr['Nome']       = $currFile['name'];
                        $allArr['StoreName']  = $newName;
                        $allArr['Tipo']       = $currFile['type'];
                        $allArr['Dimensione'] = $currFile['size'];
                        $this->saveAllegato($currId, $tabGuestKey, $allArr, $currFileName);
//                            $currFile['name'], $currFile['name'], $newName);
                        }
                    }
                $toDelete = array_diff_key($formData, array_flip($tableCols));
                foreach ($toDelete as $key => $value)
                    {
                    unset($formData[$key]);
                    }
                unset($formData[$currKey]);
                $where = $cliTable->getAdapter()->quoteInto($currKey . ' =? ', $currId);
                if (count($formData) > 0)
                    {
                    foreach($formData as $key => $value)
                        {
                        $elemDef = $this->currXml['form']['elements'][$key];
                        if (key_exists('float', $elemDef))
                            {
                            $cond = $elemDef['float'];
                            if (($cond == 'true') || $cond)
                                {
                                $value = str_replace('.', '', $value);
                                $formData[$key]= str_replace(',', '.', $value);
                                }
                            }
                        
                        }
                    $cliTable->update($formData, $where);
                    }
                $formData[$currKey] = $currId;
                $this->callCustomType('aftersave', $formData, $this);
                return true;
                }
            }
        return false;
        }

    private function checkMaxFiles($formData, $files)
        {
        $valMax = array();
        foreach ($formData as $currName => $currValue)
            {
            if (substr($currName, 0, 6) == 'todel_')
                {
                $currFieldArray = explode('_', $currName);
                $fieldName      = $currFieldArray[1];
                if (!array_key_exists($fieldName, $valMax))
                    {
                    $valMax[$fieldName] = array('max' => 0, 'count' => 0);
                    }
                if ($currValue[0] == '-')
                    {
                    $valMax[$fieldName]['count'] --;
                    }
                else
                    {
                    $valMax[$fieldName]['count'] ++;
                    }
                }
            elseif (substr($currName, 0, 9) == 'maxfiles_')
                {
                $currFieldArray = explode('_', $currName);
                $fieldName      = $currFieldArray[1];
                if (!array_key_exists($fieldName, $valMax))
                    {
                    $valMax[$fieldName] = array('max' => 0, 'count' => 0);
                    }
                $valMax[$fieldName]['max'] = $currValue;
                }
            }
        foreach ($files as $newFileName => $newFile)
            {
            if ($newFile['error'] == 0 && $newFile['size'] > 0)
                {
                $valMax[$newFileName]['count'] ++;
                }
            }
        $retVal = true;
        foreach ($valMax as $name => $value)
            {
            if ($value['count'] > $value['max'])
                {
//                $this->form->getElement('Cognome')->addErrormessage(
//                        'Troppi file allegati!');
//                $retVal = false;
                }
            }
        return $retVal;
        }

    private function delAlleg($formData)
        {
        foreach ($formData as $currName => $currValue)
            {
            if (substr($currName, 0, 6) == 'todel_')
                {
                $arrVal = explode('#', $currValue);
                if (is_array($arrVal) && $arrVal[0] == '-')
                    {
                    $sql       = 'SELECT Nome, StoreName FROM allegati WHERE idAllegato="' .
                            $arrVal[1] . '"';
                    $arrFields = $this->db->fetchRow($sql);
                    unlink($this->uploadPath . '/' . $arrFields['StoreName']);
                    $sql       = 'DELETE FROM allegati WHERE idAllegato="' .
                            $arrVal[1] . '"';
                    $this->db->query($sql);
                    $sql       = 'DELETE FROM alleguests WHERE idAllegato="' .
                            $arrVal[1] . '"';
                    $this->db->query($sql);
                    }
                }
            }
        }

    public function insertData($formData)
        {
        if ($this->form->isValidPartial($formData))
            {
            $translate = Zend_Registry::get('translate');
            
            if ($formData['Conferma'] == $translate->_('Conferma'))
                {
                if (!$this->callCustomType('beforenew', $formData, $this))
                    return false;

                if (is_object($formData))
                    {
                    $allegData = clone($formData);
                    }
                elseif (is_array($formData))
                    {
                    $allegData = $formData;
                    }

                $cliTable  = new Zend_Db_Table($this->currXml['maintable']);
                $tableCols = $cliTable->info('cols');
                $currKey   = $this->currXml['currkey'];
                $toDelete  = array_diff_key($formData, array_flip($tableCols));
                foreach ($toDelete as $key => $value)
                    {
                    unset($formData[$key]);
                    }
                unset($formData[$currKey]);
//            if ($this->form->isValidPartial($formData))
//            {
                if (count($formData) > 0)
                    {
                    foreach($formData as $key => $value)
                        {
                        $elemDef = $this->currXml['form']['elements'][$key];
                        if (key_exists('float', $elemDef))
                            {
                            $cond = $elemDef['float'];
                            if (($cond == 'true') || $cond)
                                {
                                $value = str_replace('.', '', $value);
                                $formData[$key]= str_replace(',', '.', $value);
                                }
                            }
                        
                        }
                    }
                $this->currId       = $currId = $cliTable->insert($formData);
                $formData[$currKey] = $this->currId;
                $tabGuestKey        = $this->findTableGuest($this->currXml['maintable']);
                foreach ($_FILES as $currFileName => $currFile)
                    {
                    if ($currFile['error'] == 0 && $currFile['size'] > 0)
                        {
                        $tmp                  = preg_replace('/[^a-z0-9.]+/i', "_", strtolower($currFile['name']));
                        $newName              = substr(md5(microtime()), 1, 4) . $tmp;
                        rename($currFile['tmp_name'], $this->uploadPath . '/' . $newName);
                        chmod($this->uploadPath . '/' . $newName, 0644);
                        $allArr               = $this->findAllPar($allegData, $currFileName);
                        $allArr['Nome']       = $currFile['name'];
                        $allArr['StoreName']  = $newName;
                        $allArr['Tipo']       = $currFile['type'];
                        $allArr['Dimensione'] = $currFile['size'];
                        $this->saveAllegato($currId, $tabGuestKey, $allArr, $currFileName);
                        }
                    }
                $this->callCustomType('afternew', $formData, $this);
                return $currId;
                }
            }
        return false;
        }

    public function getJavascript()
        {
        if (count($this->formWarnings) > 0)
            return $this->javascript . '<script type="text/javascript">' .
                    $this->warningAlertScript . '</script>';
        else
            return $this->javascript;
        }

    public function getForm()
        {
        if (count($this->formWarnings) > 0)
            {
            $tmpWarning = '';
            foreach ($this->formWarnings as $currWarning)
                {
                $tmpWarning.=$currWarning . '</br>';
                }
            $tmpElem = new CustomElement("dialogWarning");
            $tmpElem->setDecorators($this->form->lineCustomElementDecorator)
                    ->setDescription('<div id="dialog" title="Attenzione!" style = "background-color:#FEFECE;"><p>' .
                            $tmpWarning . '</p></div>');
//                    ->setDescription('<div id="dialog" title="Basic dialog">
//<p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the \'x\' icon.</p>
//</div>
//');
            $this->form->addElement($tmpElem);
            }
        return $this->form;
        }

    public function getFormValues()
        {
        return $this->form->getValues();
        }

    public function validateForm($formData)
        {
        return $this->form->isValid($formData);
        }

    public function setParam($key, $value)
        {
        $this->currParameters[$key] = $value;
        }

    public function setParameters($parArr)
        {
        foreach ($parArr as $key => $value)
            {
            $this->currParameters[$key] = $value;
            }
        }

    public function setForm($form)
        {
        $this->form = $form;
        }

    public function setCurrAction($action)
        {
        $this->form->setAction($action);
        }

    public function setSavingAlertCallAddonMode($mode)
        {
        $this->savingAlertCallAddonMode = $mode;
        }

    private function getKey($key)
        {
        if (array_key_exists($key, $this->currXml))
            {
            return $this->currXml[$key];
            }
        else
            return '';
        }

    public function getTitle()
        {
        $translate = Zend_Registry::get('translate');
        return $translate->_($this->getKey('title'));
        }

    public function getEditTitle()
        {
        $translate = Zend_Registry::get('translate');
        return $translate->_($this->getKey('edittitle'));
        }

    public function getNewTitle()
        {
        $translate = Zend_Registry::get('translate');
        return $translate->_($this->getKey('newtitle'));
        }

    public function setNewAction($newAction)
        {
        if ($this->currKey == '')
            {
            $this->form->setAction($newAction . '/edt/' . $this->currTable);
            }
        else
            {
            $this->form->setAction(newAction . '/id/' . $this->currKey .
                    '/edt/' . $this->currTable);
            }
        }

    public function getElemType($elemId)
        {
        $formDef = $this->currXml['form']['elements'];
        if (array_key_exists($elemId, $formDef))
            {
            return $formDef[$elemId]['type'];
            }
        else
            {
            return('');
            }
        }

    public function isToDisable($elemId)
        {
        $toDisable = array('button', 'checkbox', 'hidden', 'hiddendate', 'submit');
        $elemType  = $this->getElemType($elemId);
        return !(array_search($elemType, $toDisable) !== false);
        }

    public function callCustom($callName, $formData, $caller)
        {
        $retval = true;
        if (key_exists($callName, $this->customFun))
            {
            $customClass  = $this->customFun['class'];
            $customMethod = $this->customFun['method'];
            $custom       = new $customClass();
            $custArr      = array($custom, $customMethod);
            if (is_callable($custArr))
                {
                $retval = $retval & call_user_func($custArr, $formData, $caller);
                }
            }
        return $retval;
        }

    public function callCustomType($type, $formData, $caller)
        {
        $retval = true;
        foreach ($this->customFun as $currFunName => $currFun)
            {
            if (is_array($currFun) && $currFun['type'] == $type)
                {
                $customClass  = $currFun['class'];
                $customMethod = $currFun['method'];
                $custom       = new $customClass();
                $custArr      = array($custom, $customMethod);
                if (is_callable($custArr))
                    {
                    $retval = $retval & call_user_func($custArr, $formData, $caller);
                    }
                }
            }
        return $retval;
        }

    private function findTableGuest($tableName)
        {
        $sql       = 'SELECT * FROM tab_guests WHERE TableName="' . $tableName . '"';
        $arrFields = $this->db->fetchRow($sql);
        return $arrFields['idTab_guest'];
        }

    private function findGuestFiles($tabGuestKey, $elemId, $currId)
        {
        $sql       = 'SELECT allegati.* FROM alleguests
            JOIN allegati ON allegati.idAllegato=alleguests.idAllegato
            WHERE alleguests.idTab_guest = "' . $tabGuestKey .
                '" AND Guest = "' . $elemId .
                '" AND idGuestKey = "' . $currId . '"';
        $arrFields = $this->db->fetchAll($sql);
        return $arrFields;
        }

    private function findAllPar($formData, $currFileName)
        {
        $retArr = array();
        foreach ($formData as $key => $par)
            {
            if (strpos($key, $currFileName, strlen($key) - strlen($currFileName)) !== false)
                {
                if (strpos($key, 'Scadenza') !== false)
                    $retArr['Scadenza'] = $par;
                elseif (strpos($key, 'Note') !== false)
                    $retArr['Note']     = $par;
                }
            }
        $retArr['Registrazione'] = date('Y-m-d');
        return $retArr;
        }

    private function saveAllegato($newKey, $tabGuestKey, $allArr, $guestField)
        {
        $allTable = new Zend_Db_Table('allegati');
        $allKey   = $allTable->insert($allArr);
        $agTable  = new Zend_Db_Table('alleguests');
        $allKey   = $agTable->insert(array('idAllegato'  => $allKey,
            'Guest'       => $guestField, 'idTab_guest' => $tabGuestKey,
            'idGuestKey'  => $newKey));
        }

    public function checkCf($cf, $context)
        {
        $somma = 0;
        $cf = strtoupper($cf);
        if (strlen($cf) != 16)
            {
            $this->form->getElement('CodFisc')->addErrormessage(
                    'Codice fiscale deve essere lungo 16 caratteri.');
            return false;
            }
        
        if(!preg_match('/^[a-z]{6}[0-9]{2}[a-z][0-9]{2}[a-z][0-9]{3}[a-z]$/i', trim($cf))) 
            {
            $this->form->getElement('CodFisc')->addErrormessage(
                    'Caratteri non validi nel codice fiscale');
            return false;
            }
        $this->form->getElement('CodFisc')->setValue($cf);
        return true;
        $stringa = strtoupper($cf);
        echo"$stringa<br>";
        for ($i = 0; $i < 15; $i++)
            {
            if (($i + 1) % 2 == 0)
                {
                switch ($stringa[$i])
                    {
                    case '0': $valore[$i] = '0';
                        break;
                    case '1': $valore[$i] = '1';
                        break;
                    case '2': $valore[$i] = '2';
                        break;
                    case '3': $valore[$i] = '3';
                        break;
                    case '4': $valore[$i] = '4';
                        break;
                    case '5': $valore[$i] = '5';
                        break;
                    case '6': $valore[$i] = '6';
                        break;
                    case '7': $valore[$i] = '7';
                        break;
                    case '8': $valore[$i] = '8';
                        break;
                    case '9': $valore[$i] = '9';
                        break;
                    case 'A': $valore[$i] = '0';
                        break;
                    case 'B': $valore[$i] = '1';
                        break;
                    case 'C': $valore[$i] = '2';
                        break;
                    case 'D': $valore[$i] = '3';
                        break;
                    case 'E': $valore[$i] = '4';
                        break;
                    case 'F': $valore[$i] = '5';
                        break;
                    case 'G': $valore[$i] = '6';
                        break;
                    case 'H': $valore[$i] = '7';
                        break;
                    case 'I': $valore[$i] = '8';
                        break;
                    case 'J': $valore[$i] = '9';
                        break;
                    case 'K': $valore[$i] = '10';
                        break;
                    case 'L': $valore[$i] = '11';
                        break;
                    case 'M': $valore[$i] = '12';
                        break;
                    case 'N': $valore[$i] = '13';
                        break;
                    case 'O': $valore[$i] = '14';
                        break;
                    case 'P': $valore[$i] = '15';
                        break;
                    case 'Q': $valore[$i] = '16';
                        break;
                    case 'R': $valore[$i] = '17';
                        break;
                    case 'S': $valore[$i] = '18';
                        break;
                    case 'T': $valore[$i] = '19';
                        break;
                    case 'U': $valore[$i] = '20';
                        break;
                    case 'V': $valore[$i] = '21';
                        break;
                    case 'W': $valore[$i] = '22';
                        break;
                    case 'X': $valore[$i] = '23';
                        break;
                    case 'Y': $valore[$i] = '24';
                        break;
                    case 'Z': $valore[$i] = '25';
                        break;
                    }
                }
            else
                {
                switch ($stringa[$i])
                    {
                    case '0': $valore[$i] = '1';
                        break;
                    case '1': $valore[$i] = '0';
                        break;
                    case '2': $valore[$i] = '5';
                        break;
                    case '3': $valore[$i] = '7';
                        break;
                    case '4': $valore[$i] = '9';
                        break;
                    case '5': $valore[$i] = '13';
                        break;
                    case '6': $valore[$i] = '15';
                        break;
                    case '7': $valore[$i] = '17';
                        break;
                    case '8': $valore[$i] = '19';
                        break;
                    case '9': $valore[$i] = '21';
                        break;
                    case 'A': $valore[$i] = '1';
                        break;
                    case 'B': $valore[$i] = '0';
                        break;
                    case 'C': $valore[$i] = '5';
                        break;
                    case 'D': $valore[$i] = '7';
                        break;
                    case 'E': $valore[$i] = '9';
                        break;
                    case 'F': $valore[$i] = '13';
                        break;
                    case 'G': $valore[$i] = '15';
                        break;
                    case 'H': $valore[$i] = '17';
                        break;
                    case 'I': $valore[$i] = '19';
                        break;
                    case 'J': $valore[$i] = '21';
                        break;
                    case 'K': $valore[$i] = '2';
                        break;
                    case 'L': $valore[$i] = '4';
                        break;
                    case 'M': $valore[$i] = '18';
                        break;
                    case 'N': $valore[$i] = '20';
                        break;
                    case 'O': $valore[$i] = '11';
                        break;
                    case 'P': $valore[$i] = '3';
                        break;
                    case 'Q': $valore[$i] = '6';
                        break;
                    case 'R': $valore[$i] = '8';
                        break;
                    case 'S': $valore[$i] = '12';
                        break;
                    case 'T': $valore[$i] = '14';
                        break;
                    case 'U': $valore[$i] = '16';
                        break;
                    case 'V': $valore[$i] = '10';
                        break;
                    case 'W': $valore[$i] = '22';
                        break;
                    case 'X': $valore[$i] = '25';
                        break;
                    case 'Y': $valore[$i] = '24';
                        break;
                    case 'Z': $valore[$i] = '23';
                        break;
                    }
                }
            }

        for ($i = 0; $i < 15; $i++)
            $somma+=$valore[$i];

        $controllo = $somma % 26;


        switch ($controllo)
            {
            case '0': $letteracontrollo = 'A';
                break;
            case '1': $letteracontrollo = 'B';
                break;
            case '2': $letteracontrollo = 'C';
                break;
            case '3': $letteracontrollo = 'D';
                break;
            case '4': $letteracontrollo = 'E';
                break;
            case '5': $letteracontrollo = 'F';
                break;
            case '6': $letteracontrollo = 'G';
                break;
            case '7': $letteracontrollo = 'H';
                break;
            case '8': $letteracontrollo = 'I';
                break;
            case '9': $letteracontrollo = 'J';
                break;
            case '10': $letteracontrollo = 'K';
                break;
            case '11': $letteracontrollo = 'L';
                break;
            case '12': $letteracontrollo = 'M';
                break;
            case '13': $letteracontrollo = 'N';
                break;
            case '14': $letteracontrollo = 'O';
                break;
            case '15': $letteracontrollo = 'P';
                break;
            case '16': $letteracontrollo = 'Q';
                break;
            case '17': $letteracontrollo = 'R';
                break;
            case '18': $letteracontrollo = 'S';
                break;
            case '19': $letteracontrollo = 'T';
                break;
            case '20': $letteracontrollo = 'U';
                break;
            case '21': $letteracontrollo = 'V';
                break;
            case '22': $letteracontrollo = 'W';
                break;
            case '23': $letteracontrollo = 'X';
                break;
            case '24': $letteracontrollo = 'Y';
                break;
            case '25': $letteracontrollo = 'Z';
                break;
            }
        if ($stringa[15] != $letteracontrollo)
            {
            $this->form->getElement('Codfisc')->addErrormessage(
                    'Codice fiscale non valido!');
            return false;
            }
        return true;
        }

//    public function checkCf($cf, $context)
//        {
//        $somma = 0;
//        if (!isset($cf) || $cf == '')
//            {
//            if (!$this->checkIva($context['Iva'], $context))
//                {
//                $this->form->getElement('Codfisc')->addErrormessage(
//                        'Se il codice fiscale è vuoto, la partita IVA e\' obbligatoria.');
//                return false;
//                }
//            }
//        if (strlen($cf) != 16)
//            {
//            $this->form->getElement('Codfisc')->addErrormessage(
//                    'Codice fiscale deve essere lungo 16 caratteri.');
//            return false;
//            }
//        if (!eregi("^[A-Z0-9]+$", $cf))
//            {
//            $this->form->getElement('Codfisc')->addErrormessage(
//                    'Caratteri non validi nel codice fiscale');
//            return false;
//            }
//        $stringa = strtoupper($cf);
//        echo"$stringa<br>";
//        for ($i = 0; $i < 15; $i++)
//            {
//            if (($i + 1) % 2 == 0)
//                {
//                switch ($stringa[$i])
//                    {
//                    case '0': $valore[$i] = '0';
//                        break;
//                    case '1': $valore[$i] = '1';
//                        break;
//                    case '2': $valore[$i] = '2';
//                        break;
//                    case '3': $valore[$i] = '3';
//                        break;
//                    case '4': $valore[$i] = '4';
//                        break;
//                    case '5': $valore[$i] = '5';
//                        break;
//                    case '6': $valore[$i] = '6';
//                        break;
//                    case '7': $valore[$i] = '7';
//                        break;
//                    case '8': $valore[$i] = '8';
//                        break;
//                    case '9': $valore[$i] = '9';
//                        break;
//                    case 'A': $valore[$i] = '0';
//                        break;
//                    case 'B': $valore[$i] = '1';
//                        break;
//                    case 'C': $valore[$i] = '2';
//                        break;
//                    case 'D': $valore[$i] = '3';
//                        break;
//                    case 'E': $valore[$i] = '4';
//                        break;
//                    case 'F': $valore[$i] = '5';
//                        break;
//                    case 'G': $valore[$i] = '6';
//                        break;
//                    case 'H': $valore[$i] = '7';
//                        break;
//                    case 'I': $valore[$i] = '8';
//                        break;
//                    case 'J': $valore[$i] = '9';
//                        break;
//                    case 'K': $valore[$i] = '10';
//                        break;
//                    case 'L': $valore[$i] = '11';
//                        break;
//                    case 'M': $valore[$i] = '12';
//                        break;
//                    case 'N': $valore[$i] = '13';
//                        break;
//                    case 'O': $valore[$i] = '14';
//                        break;
//                    case 'P': $valore[$i] = '15';
//                        break;
//                    case 'Q': $valore[$i] = '16';
//                        break;
//                    case 'R': $valore[$i] = '17';
//                        break;
//                    case 'S': $valore[$i] = '18';
//                        break;
//                    case 'T': $valore[$i] = '19';
//                        break;
//                    case 'U': $valore[$i] = '20';
//                        break;
//                    case 'V': $valore[$i] = '21';
//                        break;
//                    case 'W': $valore[$i] = '22';
//                        break;
//                    case 'X': $valore[$i] = '23';
//                        break;
//                    case 'Y': $valore[$i] = '24';
//                        break;
//                    case 'Z': $valore[$i] = '25';
//                        break;
//                    }
//                }
//            else
//                {
//                switch ($stringa[$i])
//                    {
//                    case '0': $valore[$i] = '1';
//                        break;
//                    case '1': $valore[$i] = '0';
//                        break;
//                    case '2': $valore[$i] = '5';
//                        break;
//                    case '3': $valore[$i] = '7';
//                        break;
//                    case '4': $valore[$i] = '9';
//                        break;
//                    case '5': $valore[$i] = '13';
//                        break;
//                    case '6': $valore[$i] = '15';
//                        break;
//                    case '7': $valore[$i] = '17';
//                        break;
//                    case '8': $valore[$i] = '19';
//                        break;
//                    case '9': $valore[$i] = '21';
//                        break;
//                    case 'A': $valore[$i] = '1';
//                        break;
//                    case 'B': $valore[$i] = '0';
//                        break;
//                    case 'C': $valore[$i] = '5';
//                        break;
//                    case 'D': $valore[$i] = '7';
//                        break;
//                    case 'E': $valore[$i] = '9';
//                        break;
//                    case 'F': $valore[$i] = '13';
//                        break;
//                    case 'G': $valore[$i] = '15';
//                        break;
//                    case 'H': $valore[$i] = '17';
//                        break;
//                    case 'I': $valore[$i] = '19';
//                        break;
//                    case 'J': $valore[$i] = '21';
//                        break;
//                    case 'K': $valore[$i] = '2';
//                        break;
//                    case 'L': $valore[$i] = '4';
//                        break;
//                    case 'M': $valore[$i] = '18';
//                        break;
//                    case 'N': $valore[$i] = '20';
//                        break;
//                    case 'O': $valore[$i] = '11';
//                        break;
//                    case 'P': $valore[$i] = '3';
//                        break;
//                    case 'Q': $valore[$i] = '6';
//                        break;
//                    case 'R': $valore[$i] = '8';
//                        break;
//                    case 'S': $valore[$i] = '12';
//                        break;
//                    case 'T': $valore[$i] = '14';
//                        break;
//                    case 'U': $valore[$i] = '16';
//                        break;
//                    case 'V': $valore[$i] = '10';
//                        break;
//                    case 'W': $valore[$i] = '22';
//                        break;
//                    case 'X': $valore[$i] = '25';
//                        break;
//                    case 'Y': $valore[$i] = '24';
//                        break;
//                    case 'Z': $valore[$i] = '23';
//                        break;
//                    }
//                }
//            }
//
//        for ($i = 0; $i < 15; $i++)
//            $somma+=$valore[$i];
//
//        $controllo = $somma % 26;
//
//
//        switch ($controllo)
//            {
//            case '0': $letteracontrollo = 'A';
//                break;
//            case '1': $letteracontrollo = 'B';
//                break;
//            case '2': $letteracontrollo = 'C';
//                break;
//            case '3': $letteracontrollo = 'D';
//                break;
//            case '4': $letteracontrollo = 'E';
//                break;
//            case '5': $letteracontrollo = 'F';
//                break;
//            case '6': $letteracontrollo = 'G';
//                break;
//            case '7': $letteracontrollo = 'H';
//                break;
//            case '8': $letteracontrollo = 'I';
//                break;
//            case '9': $letteracontrollo = 'J';
//                break;
//            case '10': $letteracontrollo = 'K';
//                break;
//            case '11': $letteracontrollo = 'L';
//                break;
//            case '12': $letteracontrollo = 'M';
//                break;
//            case '13': $letteracontrollo = 'N';
//                break;
//            case '14': $letteracontrollo = 'O';
//                break;
//            case '15': $letteracontrollo = 'P';
//                break;
//            case '16': $letteracontrollo = 'Q';
//                break;
//            case '17': $letteracontrollo = 'R';
//                break;
//            case '18': $letteracontrollo = 'S';
//                break;
//            case '19': $letteracontrollo = 'T';
//                break;
//            case '20': $letteracontrollo = 'U';
//                break;
//            case '21': $letteracontrollo = 'V';
//                break;
//            case '22': $letteracontrollo = 'W';
//                break;
//            case '23': $letteracontrollo = 'X';
//                break;
//            case '24': $letteracontrollo = 'Y';
//                break;
//            case '25': $letteracontrollo = 'Z';
//                break;
//            }
//        if ($stringa[15] != $letteracontrollo)
//            {
//            $this->form->getElement('Codfisc')->addErrormessage(
//                    'Codice fiscale non valido!');
//            return false;
//            }
//        return true;
//        }

    public function checkIva($pi, $context)
        {
        if ($pi == '')
            {
            if (!$this->checkCf($context['Codfisc'], $context))
                {
                $this->form->getElement('Iva')->addErrormessage(
                        'Se la partita IVA è vuota, il codice fiscale e\' obbligatorio.');
                return false;
                }
            }
        if (strlen($pi) != 11)
            return false;
        if (!ereg("^[0-9]+$", $pi))
            return false;
        $s = 0;
        for ($i = 0; $i <= 9; $i += 2)
            $s += ord($pi[$i]) - ord('0');
        for ($i = 1; $i <= 9; $i += 2)
            {
            $c = 2 * ( ord($pi[$i]) - ord('0') );
            if ($c > 9)
                $c = $c - 9;
            $s += $c;
            }
        if (( 10 - $s % 10 ) % 10 != ord($pi[10]) - ord('0'))
            return false;
//        if($context['idImpresa'] == '')
//            return true;
        $sql       = 'SELECT * FROM imprese WHERE Iva="' . $pi . '" AND idImpresa <> "' .
                $context['idImpresa'] . '"';
        $arrFields = $this->db->fetchAll($sql);
        if (count($arrFields) == 0)
            return true;
        else
            {
            $this->form->getElement('Iva')->addErrormessage(
                    'Partita Iva già presente in archivio per l\'impresa ' .
                    $arrFields[0]['Impresa'] . '!');
            return false;
            }
        }

    public function checkFN($fn, $context)
        {
        $contextKey = $this->getContextKey();
        var_dump($fn);
        var_dump($context);
        var_dump($contextKey);
        die();
        return false;
        }

    public function addWarning($warning)
        {
        $this->formWarnings[] = $warning;
        }

    }
