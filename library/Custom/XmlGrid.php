<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_XmlGrid
    {

    private $currXml;
    private $currParameters;
    private $javascript;
    private $printMode = false;

    public function init($currDb)
        {
        /* Initialize action controller here */
        $this->db = $currDb;
        Zend_Db_Table::setDefaultAdapter($this->db);
        $this->currParameters = array();
        $this->javascript = '';
        }

    public function MyGrid($title = '')
        {
        $config = new Zend_Config_Ini(APPLICATION_PATH .
                        '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $config);
        $grid->setExport(array('pdf', 'wordx', 'excel', 'print'));
//        $grid->setExport(array());
        $grid->addTemplateDir('My/Template/Table', 'My_Template_Table', 'table');
        $grid->addTemplateDir('My/Template/Wordx', 'My_Template_Wordx', 'wordx');
        $grid->setPaginationInterval(array(
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100 ));
        $grid->addFormatterDir('My/Grid/Formatter', 'My_Grid_Formatter');
        $grid->setImagesUrl(APPLICATION_PATH . '../public/images/');
        $grid->setEscapeOutput(false);
        $grid->setUseKeyEventsOnFilters(true);
//        $grid->setAlwaysShowOrderArrows(true);
//        $grid->setNoFilters(1);
//        $grid->setNoOrder(0);

        $grid->setParam('deploy.pdf.title', $title)
                ->setParam('deploy.wordx.title', $title)
                ->setParam('deploy.wordx.subtitle', '')
                ->setParam('deploy.word.footer', 'bho')
                ->setParam('deploy.pdf.subtitle', '')
                ->setParam('deploy.print.title', $title);
        return $grid;
        }

    public function setPrintMode($newPrintMode)
        {
        $this->printMode = $newPrintMode;
        return $this;
        }

    public function getGrid($callerClass, $currId, $value = '', $params = array())
        {
        $db = $callerClass->db;
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;
        $grid = $this->MyGrid($currId);
        $translate = Zend_Registry::get('translate');
        $configMenu = new Zend_Config_Xml(APPLICATION_PATH .
                        '/configs/grids/' . $currId . '.xml', $currId);
        $this->currXml = $configMenu->toArray();
        if(array_key_exists('title', $this->currXml))
            {                
            $this->currXml['title'] = $translate->_($this->currXml['title']);
            }
        if (key_exists('parameters', $this->currXml))
            {
            $parameterX = $this->currXml['parameters'];
            foreach ($parameterX as $key => $currPar)
                {
                switch (trim($currPar['from']))
                    {
                    case 'arrdefaultnamespace':
                        $tmp = $defaultNamespace->__get($currPar['array']);
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
                        $this->currParameters[$key] = $value;
                        break;

                    case 'cond':
                        $resTrue = 1;
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
                                if (strpos($this->substPar($currPar['op1'], $this->currParameters),
                                        $this->substPar($currPar['op2'], $this->currParameters))!==false)
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
        if (key_exists('javascript', $this->currXml))
            {
            $this->javascript = '<script type="text/javascript"> ' .
                    htmlspecialchars_decode($this->substPar(
                                    $this->currXml['javascript'], $this->currParameters)) . '</script> ';
            }
        $this->currXml = $this->substIf($this->currXml, $this->currParameters);
        $selectX = $this->currXml['select'];
        foreach($selectX['from']['fields'] as $fieldsKey => $fieldsValue)
            {
            $selectX['from']['fields'][$fieldsKey]=$this->substPar($fieldsValue, $this->currParameters);
            }
        $select = $db->select()->from($selectX['from']['aliasname'], $selectX['from']['fields']);
        if (key_exists('distinct', $selectX))
            {
            $select->distinct();
            }
        if (key_exists('join', $selectX) && is_array($selectX['join']))
            {
            foreach ($selectX['join'] as $key => $currJoin)
                {
                if (is_array($currJoin) && array_key_exists('aliasname', $currJoin))
                    {
                    $sql = $this->substPar($currJoin['cond'], $this->currParameters);
                    if (!array_key_exists('fields', $currJoin))
                        $currJoin['fields'] = array();
                    foreach($currJoin['fields'] as $joinFieldsKey => $joinFieldsValue)
                        {
                        $currJoin['fields'][$joinFieldsKey]=$this->substPar($joinFieldsValue, $this->currParameters);
                        }
                    if (array_key_exists('type', $currJoin))
                        {
                        switch (trim($currJoin['type']))
                            {
                            case 'inner':
                                $select->join($currJoin['aliasname'], $sql, $currJoin['fields']);
                                break;

                            case 'left':
                                $select->joinLeft($currJoin['aliasname'], $sql, $currJoin['fields']);
                                break;

                            case 'right':
                                $select->joinRight($currJoin['aliasname'], $sql, $currJoin['fields']);
                                break;

                            case 'full':
                                $select->joinFull($currJoin['aliasname'], $sql, $currJoin['fields']);
                                break;

                            case 'cross':
                                $select->joinCross($currJoin['aliasname'], $currJoin['fields']);
                                break;

                            case 'natural':
                                $select->joinNatural($currJoin['aliasname'], $currJoin['fields']);
                                break;

                            default:
                                $select->join($currJoin['aliasname'], $sql, $currJoin['fields']);
                                break;
                            }
                        }
                    else
                        {
                        $select->join($currJoin['aliasname'], $sql, $currJoin['fields']);
                        }
                    }
                }
            }

        if (key_exists('group', $selectX) && $selectX['group'] != '')
            {
            if(is_array($selectX['group']))
                {
                foreach ($selectX['group'] as $key => $value)
                    {
                    $selectX['group'][$key] = trim($selectX['group'][$key]);
                    }
                $select->group($selectX['group']);                
                }
            else
                {
                $select->group(trim($selectX['group']));            
                }    
            }

        if (key_exists('order', $selectX) && $selectX['order'] != '')
            {
            $select->order(trim($selectX['order']));
            }

        if (key_exists('orderdesc', $selectX) && $selectX['orderdesc'] != '')
            {
            $select->order(trim($selectX['orderdesc']) . ' DESC');
            }

        if (key_exists('where', $selectX) && $selectX['where'] != '')
            {
            $currWhere = $selectX['where'];
            if (key_exists('param', $currWhere) && $currWhere['param'] != '')
                {
                $select->where($this->substPar($currWhere['from'], $this->currParameters), $this->currParameters[trim($currWhere['param'])]);
                }
            else
                {
                $select->where($this->substPar($currWhere['from'], $this->currParameters));
                }
            }
        $tmp = $select->__toString();
//        echo $tmp;
//        die();
        $grid->query($select);
//        $updateColumns = $this->substPar($this->currXml['updatecolumns'], $this->currParameters);
        $updateColumns = $this->currXml['updatecolumns'];
        $toPrint = array();
        foreach ($updateColumns as $columnId => $updArr)
            {
//            foreach ($updArr as $key => $value)
//                {
//                $updArr[$key] = $this->substPar($value, $this->currParameters);
//                }
//            if(array_key_exists('style', $updArr))
//                {                
//                $updArr['style'] = $this->substPar($updArr['style'], $this->currParameters);
//                }
            if(array_key_exists('title', $updArr))
                {                
                $updArr['title'] = $translate->_($updArr['title']);
                }
            if(array_key_exists('print', $updArr) && (!$updArr['print']))
                {
//                unset($toPrint[$columnId]);
                }
            else
                {
                $toPrint[] = $columnId;
                }
            $grid->updateColumn($columnId, $updArr);
            }
        if (key_exists('updateCallbacks', $this->currXml) && 
                                $this->currXml['updateCallbacks'] != '')
            {
            $cbUpd=$this->currXml['updateCallbacks'];
            foreach ($cbUpd as $cbId => $cbArr)
                {
                if (is_array($cbArr))
                    {
                    if (key_exists('class', $cbArr) && $cbArr['class'] != '')
                        {
                        $tmpClass = trim($cbArr['class']);
                        }
                    if (key_exists('method', $cbArr) && $cbArr['method'] != '')
                        {
                        $tmpMet = trim($cbArr['method']);
                        }
                    if (key_exists('params', $cbArr) && $cbArr['params'] != '')
                        {
                        $tmpPar = trim($cbArr['params']);
                        }
                    }    
                $grid->updateColumn($cbId,
                        array('callback' => 
                            array('function' => array(new $tmpClass, $tmpMet),
                                  'params' => array($tmpPar)
                                 )
                              )
                           );                    
                }
            }
//        if(!$this->printMode)
        if(!$grid-> isExport())
            {
            $extraColumns = $this->currXml['extracolumns'];

            foreach ($extraColumns as $columnId => $updArr)
                {
    //            if(strcasecmp($columnId, 'edit') == 0)
    //                {
    //                if ($defaultNamespace->aclReadonly == 1)
    //                    {
    //                    continue;
    //                    }
    //                }
                $editCol = new Bvb_Grid_Extra_Column();
                $editCol->_field['name'] = $columnId;
                if(array_key_exists('title', $updArr))
                    {              
                    $currTitle = $updArr['title'];
                    if(($defaultNamespace->aclReadonly==1 || 
                            $defaultNamespace->aclViewonly==1) && 
                            (strcasecmp($currTitle, 'edit') == 0))
                        {
                        $currTitle = 'View';
                        }
                    $updArr['title'] = $translate->_($currTitle);
                    }
                foreach ($updArr as $name => $args)
                    {
                    if ($name == 'decorator')
                        {
                        if (is_array($args))
                            {
                            $tmpArg = '<a href="';
                            if (key_exists('link', $args) && $args['link'] != '')
                                {
                                $tmpArg .= $baseUrl . trim($args['link']) . '" ';
                                }
                            if (key_exists('onclick', $args) && $args['onclick'] != '')
                                {
                                $tmpArg .= 'onClick="' . trim($args['onclick']) . '" ';
                                }
                            $tmpArg .= '>';
                            if (key_exists('linkimg', $args) && $args['linkimg'] != '')
                                {
                                $tmpArg .= '<img src="' . $baseUrl .
                                        trim($args['linkimg']) . '" ';
                                }
                            if (key_exists('linkheight', $args) && $args['linkheight'] != '')
                                {
                                $tmpArg .= 'height="' .
                                        trim($args['linkheight']) . '" ';
                                }
                            if (key_exists('linkwidth', $args) && $args['linkimg'] != '')
                                {
                                $tmpArg .= 'width="' .
                                        trim($args['linkwidth']) . '" ';
                                }
                            if (key_exists('linkalt', $args) && $args['linkalt'] != '')
                                {
                                $tmpArg .= 'alt="' . trim($args['linkalt']) . '" ' .
                                        'title="' . trim($args['linkalt']) . '" ';
                                }
                            $tmpArg .= ' border="0"></a>';
                            $args = $tmpArg;
                            }
                        else
                            {
                            $parArr = explode('#', $args);
                            $args = '<a href="' . $baseUrl . trim($parArr[0]) .
                                    '"><img src="' . $baseUrl . trim($parArr[1]) .
                                    '" alt="' . $parArr[2] .
                                    '" border="0"></a>';
                            }
                        $editCol->_field[$name] = $args;
                        }
                    elseif ($name == 'customdecorator')
                        {
                        $editCol->_field['decorator'] = $this->substParGrid($args, $this->currParameters);
                        }
                    elseif ($name == 'callback')
                        {
                        if (is_array($args))
                            {
                            if (key_exists('class', $args) && $args['class'] != '')
                                {
                                $tmpClass = trim($args['class']);
                                }
                            if (key_exists('method', $args) && $args['method'] != '')
                                {
                                $tmpMet = trim($args['method']);
                                }
                            if (key_exists('params', $args) && $args['params'] != '')
                                {
                                $tmpPar = $args['params'];                                    
                                if(!is_array($tmpPar))
                                    {
                                    $tmpPar = trim($tmpPar);
                                    }
                                }
                            }    
                        $editCol->_field['callback'] =    
                                    array('function' => array(new $tmpClass, $tmpMet),
                                          'params' => array($tmpPar)
                                         );
                                               

//                        $grid->updateColumn($cbId,
//                            if(array_key_exists('cbname', $args))
//                                {
//                                $cbName=$args['cbname'];
//                                unset($args['cbname']);
//                                }
//                            $editCol->_field['callback'] = 
////                                    array('function' => array($callerClass, $cbName),
//                                    array('function' => array($grid, $cbName),
//                                      'params' => array_values($args));                                
//                            }
                        }
                    else
                        {
                        $editCol->_field[$name] = $this->substPar($args, $this->currParameters);
                        }
                    }
                $grid->addExtraColumns($editCol);
                }
            }
        else
            {            
            if (key_exists('pagemode', $this->currXml))
                {
//                $grid->setDeployOptions(array('orientation'=>"PORTRAIT"));
                $grid->setDeployOption('orientation', 
                        strtoupper($this->currXml['pagemode']));
                }
            $grid->setPdfGridColumns($toPrint);    
            $grid->setPrintGridColumns($toPrint);    
            $grid->setExcelGridColumns($toPrint);    
            $grid->setWordGridColumns($toPrint);    
            $grid->setWordxGridColumns($toPrint);    
            $grid->setWordxFooter('Prova');    
            $grid->setDeployOption('subtitle', $this->currXml['title']);
            $grid->setDeployOption('footer', date('D, d M Y H:i:s'));
            $grid->set_DeployClass_GridColumns();
            }    
//        $grid->setAjax($currId);
        $grid->setCache(array('enable' => array('form'=>false,'db'=>false), 
            'instance' => Zend_Registry::get('cache'), 'tag' => 'grid'));
        return $grid;
        }

    private function substIf($currXml, $currParameters)
        {
        $retXml = array();
        foreach ($currXml as $currKey => $currIf)
            {
            if ($currKey === 'if')
                {
                $resTrue = '';
                $resFalse = '';
                if ($this->substPar($currIf['cond'], $currParameters))
                    {
                    if (key_exists('iftrue', $currIf))
                        {
                        $resTrue = $currIf['iftrue'];
                        if (is_array($resTrue))
                            {
                            foreach ($resTrue as $key => $value)
                                {
                                $retXml[$key] = $value;
                                }
                            }
                        }
                    }
                else
                    {
                    if (key_exists('iffalse', $currIf))
                        {
                        $resFalse = $currIf['iffalse'];
                        if (is_array($resFalse))
                            {
                            foreach ($resFalse as $key => $value)
                                {
                                $retXml[$key] = $value;
                                }
                            }
                        }
                    }
                }
            else if (is_array($currIf))
                {
                $retXml[$currKey] = $this->substIf($currIf, $currParameters);
                }
            else
                {
                $retXml[$currKey] = $this->substParGrid($currIf);
                }
            }
        return $retXml;
        }

    private function substParGrid($funct)
        {
        $out = '';
        if (strpos($funct, '#[') === false)
            {
            return $funct;
            }
        else
            {
            $arrTmp = explode('#[', $funct);
            foreach ($arrTmp as $currChunk)
                {
                if (strpos($currChunk, ']#') === false)
                    {
                    $out.=$currChunk;
                    }
                else
                    {
                    $arrChunk = explode(']#', $currChunk);
                    $out.=$this->currParameters[$arrChunk[0]] . $arrChunk[1];
                    }
                }
            return $out;
            }
        }

    private function substPar($funct, $arrPar)
        {
        if(is_array($funct))
            {
            foreach($funct as $index => $currFunct)
                {
                $funct[$index] = $this->substPar($currFunct, $arrPar); 
                }
            return $funct;    
            }
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
                    if(array_key_exists($arrChunk[0], $arrPar))
                        $out.=$arrPar[$arrChunk[0]] . $arrChunk[1];
                    else
                        $out.=$arrChunk[1];
                    }
                }
            return $out;
            }
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

    public function getJavascript()
        {
        return $this->javascript;
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

    public function getLink()
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currTitle = $this->getKey('addtitle');
        if(($defaultNamespace->aclReadonly==1 || $defaultNamespace->aclViewonly==1) && (strcasecmp($currTitle, 'edit') != 0))
            {
            return '';
            }
        else
            {
            return $this->getKey('addlink');
            }
        }

    public function getLinkTitle()
        {
        $translate = Zend_Registry::get('translate');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $currTitle = $this->getKey('addtitle');
        if($defaultNamespace->aclReadonly==1 || $defaultNamespace->aclViewonly==1)
            {
            if(strcasecmp($currTitle, 'edit') == 0)
                {
                $currTitle = 'View';
                }
            else
                {
                $currTitle = '';                
                }    
            }
        return $translate->_($currTitle);
        }

    }