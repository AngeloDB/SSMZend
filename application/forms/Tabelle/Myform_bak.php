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
class Form_Myform extends Zend_Dojo_Form
{

    protected $elementDecorators = array(
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

    protected $dojoElementDecorators = array('DijitElement', 'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'td', 'class'=>'input_cell')),
        array('Label', array('tag'=>'td', 'class'=>'label_cell')),
        array(array('row'=>'HtmlTag'), array('tag'=>'tr')));
    protected $dojoLeftElementDecorators = array(
        'Label',
        array(array('labelTd'=>'HtmlTag'),
            array('tag'=>'td', 'class'=>'label_cell')),
        array(array('elemTdOpen'=>'HtmlTag'),
            array('tag'=>'td', 'openOnly'=>true,
                'class'=>'input_cell', 'placement'=>'append')),
        'DijitElement',
        'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'div', 'class'=>'input_cell')));
    protected $dojoRightElementDecorators = array('DijitElement', 'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'td', 'closeOnly'=>true)),
        array(array('row'=>'HtmlTag'), array('tag'=>'tr', 'closeOnly'=>true)));
    protected $checkboxDecorators = array(
        'ViewHelper',
        'Errors',
        'Description',
        array('HtmlTag', array('tag' => 'td')),
        array('Label', array('tag' => 'td', 'class' =>'label_cell')),
        array('Description', array('tag' => 'span')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')));
    protected $buttonDecorators = array('ViewHelper',
        array('HtmlTag', array('tag' => 'td')),
        //array('Label',array('tag' => 'td')), NO LABELS FOR BUTTONS
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')));
    protected $selectNewDecorators = array(
        'Label',
        array(array('labelTd'=>'HtmlTag'),
            array('tag'=>'td', 'class'=>'label_cell')),
        array(array('elemTdOpen'=>'HtmlTag'),
            array('tag'=>'td', 'openOnly'=>true,
                'class'=>'input_cell', 'placement'=>'append')),
        'ViewHelper',
        'Errors',
        array('Description', array('tag' => 'div')));
    protected $buttonNewDecorators = array('ViewHelper',
        array('HtmlTag', array('tag' => 'td', 'closeOnly'=>true)),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)));
    protected $dojoNewElementDecorators = array(
        'Label',
        array(array('labelTd'=>'HtmlTag'),
            array('tag'=>'td', 'class'=>'label_cell')),
        array(array('elemTdOpen'=>'HtmlTag'),
            array('tag'=>'td', 'openOnly'=>true,
                'class'=>'input_cell', 'placement'=>'append')),
        'DijitElement',
        'Errors',
        array(array('data'=>'HtmlTag'), array('tag'=>'div', 'class'=>'input_cell')));

    protected $allDecorators = array();
/*
    protected $allDecorators = array(
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