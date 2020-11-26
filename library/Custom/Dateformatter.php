<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_Dateformatter
{

    public function init()
    {
        /* Initialize action controller here */
//        $this->db = Zend_Registry::get('db');
//        Zend_Db_Table::setDefaultAdapter($this->db);
    }

    public function Format($currDate)
    {
        return $currDate.'00:00:00';
    }
}