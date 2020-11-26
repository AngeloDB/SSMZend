<?php

/**
 * Description of Grid
 *
 * @author Italo
 */
class Custom_Grid {

    public function MyGrid($title='') {
        $config = new Zend_Config_Ini(APPLICATION_PATH .
                        '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('table', $config);
        $grid->setExport(array('pdf', 'word', 'excel', 'print'));
        $grid->addTemplateDir('My/Template/Table', 'My_Template_Table', 'table');
        $grid->addFormatterDir('My/Formatter', 'My_Formatter');
        $grid->setImagesUrl($this->getRequest()->getBaseUrl() . '/images/');
        $grid->setEscapeOutput(false);
        $grid->setUseKeyEventsOnFilters(true);
        $grid->setNumberRecordsPerPage(20);
//        $grid->setAlwaysShowOrderArrows(true);
//        $grid->setNoFilters(1);
//        $grid->setNoOrder(0);

        $grid->setParam('deploy.pdf.title', $title)
                ->setParam('deploy.pdf.subtitle', '')
                ->setParam('deploy.print.title', $title);
        return $grid;
    }

}