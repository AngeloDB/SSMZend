<?php


class Custom_GridCallback
    {

    public function format_date($param)
        {
        if($param == '')
            {
            return ''; 
            }
        else
            {
            $date = new DateTime($param);
            return $date->format('d/m/Y');
            }        
        }        

    public function cbtest($param)
        {
        if(is_array($param))
            {
            return 'Is array '.implode(',', $param); 
            }
        else
            {
            return "No array ".$param;
            }        
        }        

    public function semaforo($param)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;

        if($param)
            {
            return '<img src="'.$baseUrl.'/images/doc_status_1.png" alt="OK" border="0">'; 
            }
        else
            {
            return '<img src="'.$baseUrl.'/images/doc_status_2.png" alt="NO" border="0">'; 
            }        
        }        

    public function semaforo2($param)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;

        if($param)
            {
            return '<img src="'.$baseUrl.'/images/ico-vtp-2.png" alt="OK" border="0">'; 
            }
        else
            {
            return '<img src="'.$baseUrl.'/images/ico-vtp-1.png" alt="NO" border="0">'; 
            }        
        }        

    public function semaforo_nostop($param)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;

        if($param)
            {
            return '<img src="'.$baseUrl.'/images/flag-1.png" alt="OK" border="0">'; 
            }
        else
            {
            return '<img src="'.$baseUrl.'/images/doc_status_NULL.png" alt="NO" border="0">'; 
            }        
        }        

    public function swapActive($param)
        {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;

        if(is_array($param) && count($param)>=2)
            {
            $retVal = '<a href="'.$baseUrl.$param[0].$param[1].
                '"><img src="'.$baseUrl.'/images/'.
                "ico-vtp-".($param[1] ? "1" : "2").'.png" ';
            if(count($param)>=3 && isset($param[2]))
                {
                $retVal.='alt="'.$param[2].'" ';
                }
            if(count($param)==4 && isset($param[3]))
                {
                $retVal.='title="'.$param[3].'" ';
                }
            $retVal .= 'border="0"></a>';
            return $retVal; 
            }
        else
            {
            return "Error";
            }        
        }        

    public function canDelete($param)
        {        
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;

        if(is_array($param) && count($param)>=1)
            {
            if($param[0])
                {
                if(count($param) == 3)
                    {
                    $retVal = '<a href="'.$baseUrl.$param[1].$param[2].'" '.
                        'onclick="return(confirm('."'Confirm delete?'));".
                        '"><img src="'.$baseUrl.'/images/fechar.png" '.
                        'alt="Delete" title="Delete" border="0"></a>';    
                    }
                else
                    {
                    $retVal = 'Error';
                    }    
                }
            else
                {
                $retVal = '<a href="#"> onclick="alert('."'Can`t delete'".
                    '); return false;"><img src="'.$baseUrl.'/images/del-dis.png" '.
                    'alt="Delete" title="Delete" border="0"></a>';
                }    
            return $retVal; 
            }
        else
            {
            return "Error";
            }        
        }        

    public function tipoScheda($param)
        {        
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl = $defaultNamespace->baseUrl;

        if(is_array($param) && count($param) == 2)
            {
            switch ($param[0]) 
                {
            case 'Mappa':
                $retVal = '<a href="'.$baseUrl.'/punti/list/id/'.$param[1].'">'.
                    '<img src="'.$baseUrl.'/images/location_pin.png" '.
                    'alt="Mappa" title="Mappa" border="0"></a>';    
                break;

            case 'Matrice':
            case 'Tabella':
                $retVal = '<a href="'.$baseUrl.'/colonne/list/id/'.$param[1].'">'.
                    '<img src="'.$baseUrl.'/images/griglia.gif" '.
                    'alt="Griglia" title="Griglia" border="0"></a>';    
                break;

            default:
                $retVal = '';
                break;                
                }
            return $retVal; 
            }
        else
            {
            return "Error";
            }        
        }        
    }