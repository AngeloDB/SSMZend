<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mail2
 *
 * @author italba
 */
class Custom_Mail2
    {

    public function init()
        {
        }

    public function getSender()
        {
        $config = Zend_Registry::get('config');
        $mailSender = '';
        foreach ($config->cantiere->licenza->sender as $value)
            {
            $mailSender .= $value . '
';
            }
        return ($mailSender);
        }

    public function __toString()
        {
        return $this->getSender();
        }

    }
