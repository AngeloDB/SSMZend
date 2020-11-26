<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_Crud
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

    public function Edit($title='')
    {
        $form = new Form_Cantieri();
        $currKey = "idCantiere";
        $request = $this->getRequest();
        $currId = $request->getParam('id');
        $form->setName('formCantiere')
                ->setMethod('post')
                ->setAction($this->view->baseUrl() . '/cantieri/edit/id/' . $currId);

        //        $this->view->form = $form;
        if ($this->_request->isPost())
        {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData))
            {
                if ($formData['Conferma'] == 'Conferma')
                {
                    $cliTable = new Zend_Db_Table('cantieri');
                    $tableCols = $cliTable->info('cols');
                    $currId = $formData[$currKey];
                    $toDelete = array_diff_key($formData, array_flip($tableCols));
                    foreach ($toDelete as $key => $value)
                    {
                        unset($formData[$key]);
                    }
                    unset($formData[$currKey]);
                    $where = $cliTable->getAdapter()->quoteInto($currKey .
                                    ' =? ', $currId);
                    $cliTable->update($formData, $where);
                    $this->_redirect('/cantieri/list');
                }
            }
        }
        else
        {
            $sql = 'SELECT * FROM cantieri WHERE ' . $currKey .
                    '=' . $currId;
            $arrFields = $this->db->fetchRow($sql);
            $form->populate($arrFields);
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() .
                    '/styles/styles.css');
            $this->view->form = $form;
        }
    }
}