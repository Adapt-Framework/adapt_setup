<?php

namespace adapt\setup{
    
    defined('ADAPT_STARTED') or die;
    
    class page extends \adapt\view{
        
        protected $_content;
        
        public function __construct($title) {
            parent::__construct('div', ['class' => 'container']);
        }
        
    }
    
}