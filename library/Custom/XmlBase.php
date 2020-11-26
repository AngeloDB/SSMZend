<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
abstract class Custom_XmlBase
{
    
    private $currXml;
    private $currParameters;
    private abstract function baseDir(){}
    
    public function init($currDb)
    {
        /* Initialize action controller here */
        $this->db = $currDb;
        Zend_Db_Table::setDefaultAdapter($this->db);
        $this->currParameters=array();
    }
        
    public function getXml($currId, $value='')
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl=$defaultNamespace->baseUrl;
        $grid = $this->MyGrid($currId);
        $configMenu = new Zend_Config_Xml(APPLICATION_PATH . 
                        $this->baseDir() . $currId.'.xml', $currId);
        $this->currXml = $configMenu->toArray();
        if (key_exists('parameters', $this->currXml))
        {
            $parameterX = $this->currXml['parameters'];
            foreach ($parameterX  as $key => $currPar)
            {
                switch (trim($currPar['from']))
                {
                    case 'arrdefaultnamespace':
                        $tmp=$defaultNamespace->__get($currPar['array']);
                        $this->currParameters[$key]=$tmp[$currPar['offset']];
                        break;
                    
                    case 'arrpar':
                        $this->currParameters[$key]=$value[$currPar['value']];
                        break;

                    case 'constant':
                        $this->currParameters[$key]=$currPar['value'];
                        break;

                    case 'defaultnamespace':
                        $this->currParameters[$key]=$defaultNamespace->__get($key);
                        break;
                    
                    case 'parameter':
                        $this->currParameters[$key]=$value;
                        break;
                     
                    case 'cond':
                        $resTrue=1;
                        $resFalse=0;
                        if (key_exists('iftrue', $currPar))
                        {
                            $resTrue=$this->substPar($currPar['iftrue']);                            
                        }
                        if (key_exists('iffalse', $currPar))
                        {
                            $resFalse=$this->substPar($currPar['iffalse']);                            
                        }                                
                        switch (trim($currPar['type']))
                        {
                            case 'eq':
                                if($this->substPar($currPar['op1']) ==
                                        $this->substPar($currPar['op2']))
                                {
                                    $this->currParameters[$key] = $resTrue;
                                }
                                else
                                {
                                    $this->currParameters[$key] = $resFalse;
                                }
                                break;
                            
                            case 'neq':
                                if($this->substPar($currPar['op1']) !=
                                        $this->substPar($currPar['op2']))
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
            $this->javascript='<script type="text/javascript"> ' .
                 htmlspecialchars_decode($this->substPar(
                            $this->currXml['javascript'])) . '</script> ';
        }
        return $this->currXml;
    }

private function doSubst()
    {
        $this->currXml = $this->substIf($this->currXml);
    }
    
private function substIf($currXml) 
    {
        $retXml=array();
        foreach($currXml as $currKey => $currIf)
        {
        if ($currKey==='if')
            {
                $resTrue='';
                $resFalse='';
                if($this->substPar($currIf['cond'], $this->currParameters))
                {
                    if (key_exists('iftrue', $currIf))
                    {                          
                        $resTrue=$currIf['iftrue'];
                        if(is_array($resTrue))
                        {
                            foreach($resTrue as $key=>$value)
                            {
                                $retXml[$key]=$value;                                
                            }
                        }
                    }
                }
                else
                {
                    if (key_exists('iffalse', $currIf))
                    {                          
                        $resFalse=$currIf['iffalse'];
                        if(is_array($resFalse))
                        {
                            foreach($resFalse as $key=>$value)
                            {
                                $retXml[$key]=$value;                                
                            }
                        }
                    }
                }
            }
        else if ($currKey==='include')
            {
                $configMenu = new Zend_Config_Xml(APPLICATION_PATH . 
                            $this->baseDir() . $currIf.'.xml', $currIf);
                $newXml = $configMenu->toArray();
                if(is_array($newXml))
                {
                    foreach($newXml as $newKey=>$newValue)
                    {
                        $retXml[$newKey]=$this->substIf($newValue);                                
                    }
                }
            }
        else if(is_array($currIf))
            {
                $retXml[$currKey]=$this->substIf($currIf, $this->currParameters);
            }
        else
            {
                $retXml[$currKey]=$this->substPar($currIf);
            }
        }
        return $retXml;
    }

    
    private function substPar($funct)
    {
        $out='';
        if (strpos($funct, '#[')===false)
        {
            return $funct;
        }
        else
        {
            $arrTmp=explode('#[', $funct);
            foreach ($arrTmp as $currChunk)
            {
                if (strpos($currChunk, ']#')===false)
                {
                    $out.=$currChunk;
                }
                else
                {
                    $arrChunk=explode(']#', $currChunk);
                    $out.=$this->currParameters[$arrChunk[0]].$arrChunk[1];
                }                
            }
            return $out;
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
}