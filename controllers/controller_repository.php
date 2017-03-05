<?php

namespace adapt\setup{
    
    defined('ADAPT_STARTED') or die;
    
    class controller_repository extends \adapt\controller{
        
        public function __construct($parent = null) {
            parent::__construct($parent);
        }
        
        public function view_default(){
            $this->add_view(new html_h2("Choose application..."));
            $this->add_view(new html_p("Pick an application from the Adapt Repository to install, login to access your private applications.", ['class' => 'lead']));
            
            $repo = new \adapt\repository("http://repository.matt.wales/v1");
            $this->add_view(new html_pre(print_r($repo->get_bundle_types(), true)));
        }
        
    }
    
}

