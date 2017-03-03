<?php

namespace adapt\setup{
    
    /* Prevent Direct Access */
    defined('ADAPT_STARTED') or die;
    
    class bundle_adapt_setup extends \adapt\bundle{
        
        public function __construct($data){
            parent::__construct('adapt_setup', $data);
        }
        
        public function boot(){
            if (parent::boot()){
                
                $this->dom->head->add(new html_link(array('type' => 'text/css', 'rel' => 'stylesheet', 'href' => "/adapt/adapt_setup/adapt_setup-{$this->version}/static/css/setup.css")));
                $this->dom->head->add(new html_script(array('type' => 'text/javascript', 'src' => "/adapt/adapt_setup/adapt_setup-{$this->version}/static/js/master.js")));
                return true;
            }
            
            return false;
        }
        
    }
    
    
}

?>