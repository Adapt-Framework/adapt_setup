<?php

namespace applications\adapt_setup{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class controller_root extends controller{
        
        public function __construct(){
            parent::__construct();
            $this->add_view(new html_h1('Adapt framework'));
            $this->add_view(new html_h2('Setup'));
        }
        
        public function view_default(){
            
            $this->add_view(new html_p("Adapt framework has been successfully installed."));
            $this->add_view(new html_p("So lets customise it to meet your needs."));
            $this->add_view(new html_label('So what do you want to do?'));
            
            $this->add_view(
                new html_a(
                    array(
                        new html_h3('I want to install a web app'),
                        new html_p('Choose this option if you wish to install an app from the adapt repository or upload your own app bundle.')
                    ),
                    array('class' => 'option', 'href' => '/choose-web-app')
                )
            );
            
            $this->add_view(new html_br());
            
            $this->add_view(
                new html_a(
                    array(
                        new html_h3('I\'m a developer and I want to write a web app'),
                        new html_p('Choose this option if you wish to write your own web app.'),
                        new html_p('You can also publish your app to adapt repository and make it available to others with or without a fee.')
                    ),
                    array('class' => 'option', 'href' => '/write-app')
                )
            );
            
            $this->add_view(new html_br());
            
            $this->add_view(
                new html_a(
                    array(
                        new html_h3('I\'m a developer and I want to write a new extension'),
                        new html_p('Choose this option if you wish to write your own extension.'),
                        new html_p('You can also publish your extension to adapt repository and make it available to others with or without a fee.')
                    ),
                    array('class' => 'option', 'href' => '/write-extension')
                )
            );
        }
        
        public function view_write_app(){
            $this->add_view(new html_h3('New Web App'));
            $this->add_view(new html_p(array('Great! If you\'re new to Adapt we recommend you view this super quick ', new html_a('tutorial', array('href' => '/app-tutorial')), '.')));
        }
    }
    
}

?>