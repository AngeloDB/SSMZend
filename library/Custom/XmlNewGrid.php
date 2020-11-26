<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_XmlNewGrid extends Custom_XmlBase
{
    
    private function baseDir()
    {
        return '/configs/grids/';
    }
    
    public function MyGrid($title='')
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH .
            '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $config);
        $grid->setExport(array('pdf', 'word', 'excel', 'print'));
        $grid->addTemplateDir('My/Template/Table', 'My_Template_Table', 'table');
        $grid->addFormatterDir('My/Formatter', 'My_Formatter');
        $grid->setImagesUrl(APPLICATION_PATH . '../public/images/');
        $grid->setEscapeOutput(false);
        $grid->setUseKeyEventsOnFilters(true);
//        $grid->setAlwaysShowOrderArrows(true);
//        $grid->setNoFilters(1);
//        $grid->setNoOrder(0);

        $grid->setParam('deploy.pdf.title', $title)
             ->setParam('deploy.pdf.subtitle', '')
             ->setParam('deploy.print.title', $title);
        return $grid;
    }

    
    public function getGrid($callerClass, $currId, $value='')
    {
        $this->getXml($currId, $value);
        $this->doSubst();
        $db = $callerClass->db;
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $baseUrl=$defaultNamespace->baseUrl;
        $grid = $this->MyGrid($currId);
        $selectX = $this->currXml['select'];
        $select = $db->select()->from($selectX['from']['aliasname'],
                        $selectX['from']['fields']);
        if (key_exists('distinct', $selectX))
        {
            $select->distinct();
        }
        if (key_exists('join', $selectX))
        {
            foreach ($selectX['join'] as $key => $currJoin)
            {
                if (is_array($currJoin) && array_key_exists('aliasname', $currJoin))
                {
                    $sql = $currJoin['cond'];
                    if (array_key_exists('type', $currJoin))
                    {
                        switch (trim($currJoin['type']))
                        {
                            case 'inner':
                                $select->join($currJoin['aliasname'], 
                                     $sql, $currJoin['fields']);
                                break;

                            case 'left':
                                $select->joinLeft($currJoin['aliasname'], 
                                     $sql, $currJoin['fields']);
                                break;

                            case 'right':
                                $select->joinRight($currJoin['aliasname'], 
                                     $sql, $currJoin['fields']);
                                break;

                            case 'full':
                                $select->joinFull($currJoin['aliasname'], 
                                     $sql, $currJoin['fields']);
                                break;

                            case 'cross':
                                $select->joinCross($currJoin['aliasname'], 
                                     $currJoin['fields']);
                                break;

                            case 'natural':
                                $select->joinNatural($currJoin['aliasname'], 
                                     $currJoin['fields']);
                                break;

                            default:
                                $select->join($currJoin['aliasname'], 
                                    $sql, $currJoin['fields']);
                                break;
                        }
                    }
                    else
                    {
                        $select->join($currJoin['aliasname'], $sql,
                        $currJoin['fields']);
                    }
                }
            }
        }

        if (key_exists('group', $selectX) && $selectX['group'] != '')
        {
            $select->group(trim($selectX['group']));
        }

        if (key_exists('order', $selectX) && $selectX['order'] != '')
        {
            $select->order(trim($selectX['order']));
        }

        if (key_exists('orderdesc', $selectX) && $selectX['orderdesc'] != '')
        {
            $select->order(trim($selectX['orderdesc']));
        }

        if (key_exists('where', $selectX) && $selectX['where'] != '')
        {
            $currWhere=$selectX['where'];
            if (key_exists('param', $currWhere) && $currWhere['param'] != '')
            {
                $select->where($currWhere['from'], 
                        $this->currParameters[trim($currWhere['param'])]);
            }   
            else
            {
                $select->where($currWhere['from']);
            }
        }
        $tmp=$select->__toString();
        $grid->query($select);
        $updateColumns = $this->currXml['updatecolumns'];
        foreach ($updateColumns as $columnId => $updArr)
        {
            $grid->updateColumn($columnId, $updArr);
        }
        $extraColumns = $this->currXml['extracolumns'];

        foreach ($extraColumns as $columnId => $updArr)
        {
            $editCol = new Bvb_Grid_Extra_Column();
            $editCol->_field['name'] = $columnId;
            foreach ($updArr as $name => $args)
            {
                if ($name == 'decorator')
                {
                    if (is_array($args))
                    {
                        $tmpArg='<a href="';
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
                        if (key_exists('linkalt', $args) && $args['linkalt'] != '')
                        {
                            $tmpArg .= 'alt="' . trim($args['linkalt']) . '" ' .
                                'title="' . trim($args['linkalt']) . '" ';
                        }
                        $tmpArg .= ' border="0"></a>';
                        $args=$tmpArg;
                    }
                    else
                    {
                        $parArr = explode('#', $args);
                        $args = '<a href="'. $baseUrl . trim($parArr[0]) .
                                '"><img src="' . $baseUrl . trim($parArr[1]) . 
                                '" alt="' . $parArr[2] .
                                '" border="0"></a>';
                    }
                    $editCol->_field[$name] = $args;
                }
                else
                {                    
                    $editCol->_field[$name] = $args;
                }
            }
            $grid->addExtraColumns($editCol);
        }
        $grid->setAjax($currId);
        return $grid;
    }
    
    public function getTitle()
    {
        return $this->getKey('title');
    }


    public function getLink()
    {
        return $this->getKey('addlink');
    }


    public function getLinkTitle()
    {
        return $this->getKey('addtitle');
    }

    
}