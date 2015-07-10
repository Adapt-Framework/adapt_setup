<?php

namespace applications\adapt_setup{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    use \extensions\bootstrap_views as bs;
    use \extensions\font_awesome_views as fa;
    
    class controller_root extends controller{
        
        protected $_container;
        
        public function __construct(){
            parent::__construct();
            
            $this->_container = new bs\view_cell(null, 12, 12, 12, 12);
            parent::add_view(new bs\view_container(new bs\view_row($this->_container)));
            
            $this->add_view(new bs\view_page_header('Adapt framework', 'Setup'));
        }
        
        public function add_view($view){
            $this->_container->add($view);
        }
        
        /*
         * Actions
         */
        public function action_configure_database(){
            /*
             * We are going to write to the global
             * settings file (/adapt/settings.xml)
             */
            if (isset($this->request['host']) && isset($this->request['username'])){
                $xml = new \frameworks\adapt\xml_document('adapt_framework');
                $settings = new xml_settings();
                $xml->add($settings);
                
                $setting = new xml_setting();
                $settings->add($setting);
                $setting->add(new xml_name('datasource.driver'));
                $setting->add(new xml_values(new xml_value("\\frameworks\\adapt\\data_source_mysql")));
                
                $setting = new xml_setting();
                $settings->add($setting);
                $setting->add(new xml_name('datasource.host'));
                $setting->add(new xml_values(new xml_value($this->request['host'])));
                
                $setting = new xml_setting();
                $settings->add($setting);
                $setting->add(new xml_name('datasource.schema'));
                $setting->add(new xml_values(new xml_value($this->request['schema'])));
                
                $setting = new xml_setting();
                $settings->add($setting);
                $setting->add(new xml_name('datasource.username'));
                $setting->add(new xml_values(new xml_value($this->request['username'])));
                
                $setting = new xml_setting();
                $settings->add($setting);
                $setting->add(new xml_name('datasource.password'));
                $setting->add(new xml_values(new xml_value($this->request['password'])));
                
                $setting = new xml_setting();
                $settings->add($setting);
                $setting->add(new xml_name('datasource.writable'));
                $setting->add(new xml_values(new xml_value('Yes')));
                
                $fp = fopen(ADAPT_PATH . "settings.xml", "w");
                if ($fp){
                    fwrite($fp, $xml);
                    fclose($fp);
                }
                
                ///*
                // * We need to connect the source and install the
                // * adapt tables.
                // */
                //$this->data_source = new \frameworks\adapt\data_source_mysql($this->request['host'], $this->request['username'], $this->request['password'], $this->request['schema'], false);
                //
                ///* Get the adapt bundle */
                //$bundles = new \frameworks\adapt\bundles();
                //$adapt = $bundles->get_bundle('adapt');
                //$adapt->install();
                
                header('Location: /what-do-you-want-to-do');
                exit(1);
            }
        }
        
        
        /*
         * Views
         */
        public function view_default(){
            $this->add_view(new bs\view_h2('Would you like database support?'));
            $this->add_view(new bs\view_p("If you need database support please complete the form below or press skip to continue without database support.", true));
            
            
            $form = new bs\view_form('/', 'post');
            $form->add(new html_input(array('type' => 'hidden', 'name' => 'actions', 'value' => 'configure-database')));
            
            $row = new bs\view_row();
            $form->add($row);
            $this->add_view($form);
            
            $left = new bs\view_cell(null, 12, 6, 6, 6);
            $right = new bs\view_cell(null, 12, 6, 6, 6);
            $row->add(array($left, $right));
            
            $control = new bs\view_select('driver', array('MySQL', 'Maria DB'));
            $left->add(new bs\view_form_group($control, 'Database driver'));
            
            $control = new bs\view_input('text', 'host');
            $left->add(new bs\view_form_group($control, 'Host'));
            
            $control = new bs\view_input('text', 'port');
            $left->add(new bs\view_form_group($control, 'Port', 'Leave blank to use the default for this database type.'));
            
            $control = new bs\view_input('text', 'username');
            $right->add(new bs\view_form_group($control, 'Username'));
            
            $control = new bs\view_input('text', 'password');
            $right->add(new bs\view_form_group($control, 'Password'));
            
            $control = new bs\view_input('text', 'schema');
            $right->add(new bs\view_form_group($control, 'Default schema'));
            
            $button = new bs\view_button('Skip database support');
            $button->attr('onclick', "window.location='/what-do-you-want-to-do'; return void(0);");
            $form->add($button);
            
            $button = new bs\view_button('Test connection');
            $button->attr('onclick', "alert('Ever so sorry, haven't written this bit yet :/'); return void(0);");
            $form->add($button);
            
            $button = new bs\view_button('Save and continue', bs\view_button::NORMAL, bs\view_button::PRIMARY);
            $button->attr('onclick', 'submit()');
            $form->add($button);
            
            
        }
        
        public function view_what_do_you_want_to_do(){
            $this->add_view(new bs\view_h2('What do you want to do?'));
            
            $panel = new bs\view_panel(null, "I'd like to install a web app", bs\view_panel::NORMAL);
            $this->add_view($panel);
            $panel->add(new html_p("Choose this option if you would like to install a web application from the repository or if you'd like to upload your own application."));
            
            $button = new bs\view_button(array(new fa\view_icon('database'), ' Pick from the repository'), bs\view_button::NORMAL, bs\view_button::STANDARD);
            $panel->add($button);
            
            $button = new bs\view_button(array(new fa\view_icon('upload'), ' Uploaded my own bundle'), bs\view_button::NORMAL, bs\view_button::STANDARD);
            $panel->add($button);
            
            
            $panel = new bs\view_panel(null, "I'd like to write a new web app");
            $panel->add(new html_p("I am a developer and I'd like to develop my own web app."));
            $button = new bs\view_button(array(new fa\view_icon('code'), ' New web app'));
            $panel->add($button);
            $this->add_view($panel);
            
            $panel = new bs\view_panel(null, "I'd like to write a new extension");
            $panel->add(new html_p("I am a developer and I'd like to develop a new extension."));
            $button = new bs\view_button(array(new fa\view_icon('cogs'), ' New extension'));
            $panel->add($button);
            $this->add_view($panel);
            
        }
        
        public function view_write_app(){
            $this->add_view(new html_h2('New Web App'));
            $this->add_view(new html_p(array('Great! If you\'re new to Adapt we recommend you view this super quick ', new html_a('tutorial', array('href' => '/app-tutorial')), '.')));
        }
    }
    
}

?>