<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_CbDecorators
{
    private $form;
    private $request;
    private $currKey;
    private $currTable;
    private $currView;

    public function init()
    {
        /* Initialize action controller here */
        $this->db = Zend_Registry::get('db');
        Zend_Db_Table::setDefaultAdapter($this->db);
    }

    public static function text($content, $element, array $options)
    {
        return '<span class="label">' . $element->getLabel() . "</span>";
    }

    public static function button($content, $element, array $options)
    {
        return '<tr><td>Prova nuova riga</td><td>' . $element->getLabel() . '</td></tr>'.$content;
    }

    public static function file($content, $element, array $options)
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $translate = Zend_Registry::get('translate');
        $op1=array_pop($options);
        $files=$op1['files'];
        $label=$translate->_($op1['label']);
        $addinfo=$op1['addinfo'];
        $numAll=count($files);
        if($label=='') $label=$translate->_('Allegati');
        if($numAll < 1)
        {
            return('');
        }
        else if ($numAll == 1)
        {
            $retVal = '<tr><td class="label_cell">'.$label.' '.$translate->_('presente').'</td><td>';
        }
        else
        {
            $retVal = '<tr><td class="label_cell">'.$label.' '.$translate->_('presenti').'</td><td>';
        }
        $retVal.='
<table border="0" width="100%">
    <thead>
        <tr>
            <th>'.$translate->_('Canc.').'</th>
            <th>'.$translate->_('Nome').'</th>';
        foreach ($addinfo as $key=>$field)
        {
            $retVal.='<th>'.$translate->_($field['title']).'</th>';
        }
        $retVal.='</tr>
    </thead>
    <tbody>';
        foreach ($files as $key => $currFile)
        {
            $retVal .= 
'        <tr>
            <td><img src="'.$defaultNamespace->baseUrl.'/images/link.gif"
                    alt="Select" onClick="setCanc(this, \'todel_'.$element->getName().
                    '_'.substr($currFile['StoreName'], 0, 4).'\')"
                    height="16px" width="16px" border="0" ></td>
            <td><a href="/public/uploads/'.$currFile['StoreName'].'" target="_blank">'.
                    $currFile['Nome'].'</a>  </td>';
            foreach ($addinfo as $keyInfo=>$field)
            {
                $tmpField = $currFile[$field['fieldname']];
                if($field['format']=='date')
                {                    
                    $arrDate=explode('-', $tmpField);
                    $retVal.='<td>'.$arrDate[2].'/'.$arrDate[1].'/'.$arrDate[0].'</td>';
                }
                else
                {
                    $retVal.='<td>'.$tmpField.'</td>';
                }
            }
            $retVal.='</tr>';
        }
        return $retVal . '</tbody></table></td></tr>';
    }
}