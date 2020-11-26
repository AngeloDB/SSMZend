<?php

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
/**
 * Description of Myform
 *
 * @author Italo
 */
class Form_Myjqform extends ZendX_JQuery_Form
{

    public $elementDecorators = array(
//            'Button',
//            array(array('buttonTd'=>'HtmlTag'),
//                array('tag'=>'td', 'class'=>'button_cell')),
            'Label',
            array(array('labelTd'=>'HtmlTag'),
                array('tag'=>'td', 'class'=>'label_cell')),
            array(array('elemTdOpen'=>'HtmlTag'),
                array('tag'=>'td', 'openOnly'=>true,
                    'class'=>'input_cell', 'placement'=>'append')),
            'ViewHelper',
            'Errors',
            array('Description', array('tag' => 'div')),
            array(array('elemTdClose'=>'HtmlTag'),
                array('tag'=>'td', 'closeOnly'=>true, 'placement'=>'append')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    public $fileElementDecorators = array(
            'Label',
            array(array('labelTd'=>'HtmlTag'),
                array('tag'=>'td', 'class'=>'label_cell')),
            array(array('elemTdOpen'=>'HtmlTag'),
                array('tag'=>'td', 'openOnly'=>true,
                    'class'=>'input_cell', 'placement'=>'append')),
            'File',
            'Errors',
            array('Description', array('escape' => false, 'tag' => 'div')),
            array(array('elemTdClose'=>'HtmlTag'),
                array('tag'=>'td', 'closeOnly'=>true, 'placement'=>'append')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr')));
//            'ViewHelper',

    public $jqueryElementDecorators = array('UiWidgetElement', 'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'input_cell')),
        array('Label', array('tag' => 'td', 'class' => 'label_cell')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'id' => 'RowData')));
    public $dijitElementDecorators = array('UiWidgetElement', 'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'td', 'class'=>'input_cell')),
        array('Label', array('tag'=>'td', 'class'=>'label_cell')),
        array(array('row'=>'HtmlTag'), array('tag'=>'tr')));
    public $dojoElementDecorators = array('DijitElement', 'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'td', 'class'=>'input_cell')),
        array('Label', array('tag'=>'td', 'class'=>'label_cell')),
        array(array('row'=>'HtmlTag'), array('tag'=>'tr')));
    public $dojoLeftElementDecorators = array(
        'Label',
        array(array('labelTd'=>'HtmlTag'),
            array('tag'=>'td', 'class'=>'label_cell')),
        array(array('elemTdOpen'=>'HtmlTag'),
            array('tag'=>'td', 'openOnly'=>true,
                'class'=>'input_cell', 'placement'=>'append')),
        'DijitElement',
        'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'div', 'class'=>'input_cell')));
    public $dojoRightElementDecorators = array('DijitElement', 'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'td', 'closeOnly'=>true)),
        array(array('row'=>'HtmlTag'), array('tag'=>'tr', 'closeOnly'=>true)));
    public $checkboxDecorators = array(
        'ViewHelper',
        'Errors',
        'Description',
        array('HtmlTag', array('tag' => 'td')),
        array('Label', array('tag' => 'td', 'class' =>'label_cell')),
        array('Description', array('tag' => 'span')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')));
    public $buttonDecorators = array('ViewHelper',
        array('HtmlTag', array('tag' => 'td', 'class'=>'button_cell')),
        //array('Label',array('tag' => 'td')), NO LABELS FOR BUTTONS
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')));
//    public $multiSelectDecorators = array(
//        'Label',
//        array(array('labelTd'=>'HtmlTag'),
//            array('tag'=>'td', 'class'=>'label_cell')),
//        array(array('elemTdOpen'=>'HtmlTag'),
//            array('tag'=>'td', 'openOnly'=>true,
//                 'class'=>'multiselect', 'placement'=>'append')),
//            'ViewHelper',
//            'Errors',
//            array('Description', array('tag' => 'div')),
//            array(array('elemTdClose'=>'HtmlTag'),
//                array('tag'=>'td', 'closeOnly'=>true, 'placement'=>'append')),
//            array(array('row' => 'HtmlTag'), array('tag' => 'tr')));

    public $resetDecorators = array('ViewHelper',
        array(array('elemTdOpen'=>'HtmlTag'), 
            array('tag' => 'td', 'class'=>'button_row', 'colspan'=>'2', 'openOnly' => true)),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr',  'openOnly' => true)));
    public $resSubmitDecorators = array('ViewHelper',
        array(array('elemTdClose'=>'HtmlTag'), 
            array('tag' => 'td', 'closeOnly' => true)),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)));
    public $submitDecorators = array('ViewHelper',
        array(array('elemTdClose'=>'HtmlTag'), 
            array('tag' => 'td', 'class'=>'button_row')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')));
    public $selectNewDecorators = array(
        'Label',
        array(array('labelTd'=>'HtmlTag'),
            array('tag'=>'td', 'class'=>'label_cell')),
        array(array('elemTdOpen'=>'HtmlTag'),
            array('tag'=>'td', 'openOnly'=>true,
                'class'=>'input_cell', 'placement'=>'append')),
        'ViewHelper',
        'Errors',
        array('Description', array('tag' => 'div')));
    public $buttonNewDecorators = array('ViewHelper',
        array('HtmlTag', array('tag' => 'td', 'closeOnly'=>true)),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)));
    public $dojoNewElementDecorators = array(
        'Label',
        array(array('labelTd'=>'HtmlTag'),
            array('tag'=>'td', 'class'=>'label_cell')),
        array(array('elemTdOpen'=>'HtmlTag'),
            array('tag'=>'td', 'openOnly'=>true,
                'class'=>'input_cell', 'placement'=>'append')),
        'DijitElement',
        'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'div', 'class'=>'input_cell')));

    public $jqElementDecorators = array('UiWidgetElement', 'Errors',
            array(array('data'=>'HtmlTag'), array('tag'=>'td', 'class'=>'input_cell')),
            array('Label', array('tag'=>'td', 'class'=>'label_cell')),
            array(array('row'=>'HtmlTag'), array('tag'=>'tr')));

    public $customElementDecorator = array('ViewHelper', 'Errors', 
            array('ViewHelper'),
            array('Description', array('escape' => false)),
            array('Errors'),
            array('HtmlTag', array('tag' => 'td')),
            array('Label', array('tag' => 'td')),
            array(array('row'=>'HtmlTag'), array('tag'=>'tr'))
            );    

    public $lineCustomElementDecorator = array('ViewHelper', 'Errors', 
            array('ViewHelper'),
            array('Description', array('escape' => false)),
            array('Errors'),
            array('HtmlTag', array('tag' => 'td', 'class'=>'button_row', 'colspan' => 2)),
            array(array('row'=>'HtmlTag'), array('tag'=>'tr'))
            );    
//            array('Label', array('tag' => 'td')),
    
    public $allDecorators = array();
/*
    public $allDecorators = array(
        'elementDecorators' => $elementDecorators);
    ,
        'dojoElementDecorators' => $dojoElementDecorators,
        'dojoLeftElementDecorators' => $dojoLeftElementDecorators,
        'dojoRightElementDecorators' => $dojoRightElementDecorators,
        'checkboxDecorators' => $checkboxDecorators,
        'buttonDecorators' => $buttonDecorators,
        'selectNewDecorators' => $selectNewDecorators,
        'buttonNewDecorators' => $buttonNewDecorators);
*/
    //'dojoNewElementDecorators' => $dojoNewElementDecorators);
/*
    public function init()
    {
        $allDecorators['elementDecorators']=$elementDecorators;
        $allDecorators['dojoElementDecorators']=$dojoElementDecorators;
        $allDecorators['dojoLeftElementDecorators']=$dojoLeftElementDecorators;
        $allDecorators['dojoRightElementDecorators']=$dojoRightElementDecorators;
        $allDecorators['checkboxDecorators']=$checkboxDecorators;
        $allDecorators['buttonDecorators']=$buttonDecorators;
        $allDecorators['selectNewDecorators']=$selectNewDecorators;
    }

 */
}

class CustomElement extends Zend_Form_Element_Xhtml
{    
    public $helper = 'formNote';
}