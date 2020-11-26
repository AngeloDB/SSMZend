<?php

class Form_JQueryTest extends ZendX_JQuery_Form
{

    public function init()
    {
        $elementDecorators = array('UiWidgetElement', 'Errors',
            array(array('data'=>'HtmlTag'), array('tag'=>'td', 'class'=>'input_cell')),
            array('Label', array('tag'=>'td', 'class'=>'label_cell')),
            array(array('row'=>'HtmlTag'), array('tag'=>'tr')));

        $this->setMethod('post');
        $this->setName('frm');
        $this->setAction('path/to/action');

        $elem = new ZendX_JQuery_Form_Element_DatePicker(
                        'date1',
                        array('label' => 'Date:')
        );
        $elem->setDecorators($elementDecorators);
        $this->addElement($elem);
// Add Element Spinner
        $elem = new ZendX_JQuery_Form_Element_Spinner(
                        "spinner1", array('label' => 'Spinner:')
        );
        $elem->setJQueryParams(array('min' => 0, 'max' => 1000, 'value' => 100));
        $elem->setDecorators($elementDecorators);
        $this->addElement($elem);

// Add Slider Element
        $elem = new ZendX_JQuery_Form_Element_Slider(
                        "slider1", array('label' => 'Slider:')
        );
        $elem->setJQueryParams(array('min' => 0,
                                     'max' => 60,
                                     'value' => 15,
                                     'slide' => new Zend_Json_Expr(
         'function(event, ui) {$("#amount").val(\'$\' + ui.value);}')));
        $elem->setDecorators($elementDecorators);
        $this->addElement($elem);

// Add Autocomplete Element
        $elem = new ZendX_JQuery_Form_Element_AutoComplete(
                        "ac1", array('label' => 'Autocomplete:')
        );
        $elem->setJQueryParams(array('data' => array('New York',
                'Berlin',
                'Bern',
                'Boston')));
        $elem->setDecorators($elementDecorators);
        $this->addElement($elem);

// Submit Button
        $elem = new Zend_Form_Element_Submit("btn1", array('value' => 'Submit'));
        $elem->setDecorators($elementDecorators);
        $this->addElement($elem);

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }

}

