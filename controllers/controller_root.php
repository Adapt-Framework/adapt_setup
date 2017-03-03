<?php

namespace adapt\setup{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    use \bootstrap\views as bs;
    use \font_awesome\views as fa;
    
    class controller_root extends controller{
        
        protected $_container;
        
        public function __construct(){
            parent::__construct();
            
            $this->_container = new bs\view_cell(null, 12, 12, 12, 12);
            parent::add_view(new bs\view_container(new bs\view_row($this->_container)));
            
            $this->add_view(new bs\view_page_header('Adapt ', 'Setup'));
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
                
                /* Set the data source */
                $this->bundles->set_global_setting("datasource.driver", array("\\adapt\\data_source_mysql"));
                
                /* Set the host */
                $this->bundles->set_global_setting("datasource.host", array($this->request['host']));
                
                /* Set the schema */
                $this->bundles->set_global_setting("datasource.schema", array($this->request['schema']));
                
                /* Set the username */
                $this->bundles->set_global_setting("datasource.username", array($this->request['username']));
                
                /* Set the password */
                $this->bundles->set_global_setting("datasource.password", array($this->request['password']));
                
                /* Set the writable */
                $this->bundles->set_global_setting("datasource.writable", array("Yes"));
                
                /* Save the settings */
                $this->bundles->save_global_settings();
                
                header('Location: /start');
                //exit(1);
            }
        }
        
        
        public function action_upload_bundle(){
            $response = array();
            //$this->respond('upload-bundle', $this->files['file']['tmp_name']);
            //$this->respond('upload-bundle', array('files' => $this->files));
            if (isset($this->files['file'])){
                $bundles = new \frameworks\adapt\bundles();
                if ($bundle_name = $bundles->unbundle($this->files['file']['tmp_name'][0])){
                    $response['status'] = 'success';
                    $response['bundle_name'] = $bundle_name;
                    $response['display'] = "Analyzing bundle <strong>{$bundle_name}</strong>, this may take a moment...";
                    $response['next_action'] = "/bundle-details?actions=resolve-dependencies&bundle={$bundle_name}";
                    $this->respond('upload-bundle', $response);
                }else{
                    $errors = $bundles->errors(true);
                    
                    if (count($errors)){
                        $response['status'] = 'error';
                        $response['errors'] = $errors;
                        $this->respond('upload-bundle', $response);
                    }else{
                        $response['status'] = 'error';
                        $response['errors'] = array('This bundle has previously been installed.');
                        $this->respond('upload-bundle', $response);
                    }
                }
            }
            
        }
        
        public function action_resolve_dependencies(){
            $response = array();
            if (isset($this->request['bundle'])){
                $bundles = new \frameworks\adapt\bundles();
                
                if ($bundles->has($this->request['bundle'])){
                    $bundle = $bundles->get($this->request['bundle']);
                    
                    if ($dependency = $this->get_next_unresolved_dependency($bundles, $bundle)){
                        
                        /* Lets attempt to get it */
                        if ($dependency_bundle = $bundles->get($dependency)){
                            $response['status'] = "success";
                            $response['display'] = "Downloading dependency: <strong>{$dependency_bundle->label}</strong>";
                            $response['next_action'] = "/bundle-details?actions=resolve-dependencies&bundle={$this->request['bundle']}";
                        }else{
                            $response['status'] = "error";
                            $response['errors'] = $bundles->errors(true);
                        }
                        
                        
                    }else{
                        $response['status'] = "success";
                        $response['display'] = "Preparing to install...";
                        $response['next_action'] = "/bundle-details?actions=install-bundle&bundle={$this->request['bundle']}";
                    }
                    
                    
                }else{
                    $response['status'] = "error";
                    $response['errors'] = array("Unable to find bundle {$this->request['bundle']}");
                }
                
                
            }else{
                $response['status'] = "error";
                $response['errors'] = array("Unknown bundle");
            }
            
            $this->respond('resolve-dependencies', $response);
        }
        
        public function action_install_bundle(){
            
            /* Completely disable caching */
            $this->setting('adapt.disable_caching', 'Yes');
            
            $response = array();
            if (isset($this->request['bundle'])){
                $bundles = new \frameworks\adapt\bundles();
                
                if ($bundles->has($this->request['bundle'])){
                    $bundle = $bundles->get($this->request['bundle']);
                    
                    if ($dependency = $this->get_next_uninstalled_dependency($bundles, $bundle)){
                        
                        /* Lets attempt to get it */
                        if ($dependency_bundle = $bundles->get($dependency)){
                            $response['status'] = "success";
                            $response['display'] = "Installing: <strong>{$dependency_bundle->label}</strong>";
                            $response['next_action'] = "/bundle-details?actions=install-bundle&bundle={$this->request['bundle']}";
                            $dependency_bundle->install();
                            $dependency_bundle->boot();
                        }else{
                            $response['status'] = "error";
                            $response['errors'] = $bundles->errors(true);
                        }
                        
                        
                    }else{
                        $response['status'] = "success";
                        $response['display'] = "<strong>Installed :)</strong>";
                    }
                    
                    
                }else{
                    $response['status'] = "error";
                    $response['errors'] = array("Unable to find bundle {$this->request['bundle']}");
                }
                
                
            }else{
                $response['status'] = "error";
                $response['errors'] = array("Unknown bundle");
            }
            
            $this->respond('install-bundle', $response);
            
            /* Enable caching */
            $this->setting('adapt.disable_caching', 'No');
        }
        
        
        public function get_next_unresolved_dependency($bundle_manager, $bundle){
            $out = null;
            foreach($bundle->depends_on as $dependency){
                if ($bundle_manager->has($dependency)){
                    $out = $this->get_next_unresolved_dependency($bundle_manager, $bundle_manager->get($dependency));
                    
                    if ($out) return $out;
                    
                }else{
                    return $dependency;
                }
            }
            
            return $out;
        }
        
        public function get_next_uninstalled_dependency($bundle_manager, $bundle){
            $out = null;
            foreach($bundle->depends_on as $dependency){
                if ($bundle_manager->has($dependency)){
                    $out = $this->get_next_uninstalled_dependency($bundle_manager, $bundle_manager->get($dependency));
                    
                    if ($out) return $out;
                    
                }
            }
            
            if (is_null($out) && $bundle->is_installed == false){
                $out = $bundle->name;
            }
            
            return $out;
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
            
            $control = new bs\view_select('driver', array('MySQL / Maria DB'));
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
            $right->add(new bs\view_form_group($control, 'Default schema', "If the database doesn't exist it will be created."));
            
            $controls = new html_div(null, array('class' => 'controls'));
            $right->add($controls);
            
            $button = new bs\view_button('Skip database support');
            $button->attr('onclick', "window.location='/start'; return void(0);");
            $controls->add($button);
            
            $button = new bs\view_button('Test connection');
            $button->attr('onclick', "alert('Ever so sorry, haven't written this bit yet :/'); return void(0);");
            $controls->add($button);
            
            
            $button = new bs\view_button('Save and continue', bs\view_button::NORMAL, bs\view_button::PRIMARY);
            $button->attr('onclick', 'submit()');
            $controls->add($button);
            
            
        }
        
        public function view_start(){
            $this->add_view(new bs\view_h2('Getting started'));
            $this->add_view(new bs\view_p("Every great thing that has ever happened started somewhere.", true));
            
            $jumbotron = new bs\view_jumbotron(array(/*'Getting started...'*/), true);
            $jumbotron->find('h1')->detach();
            //$jumbotron->add(new html_p("Every great thing that has ever happened started somewhere...", array('class' => 'lead')));
            
            $row = new bs\view_row();
            $jumbotron->add($row);
            
            
            
            $cell = new bs\view_cell($panel, 12, 12, 6);
            $cell->add_class('text-center');
            $row->add($cell);
            
            $cell->add(new html_h2("Deploy a website"));
            $cell->add(new html_p("Choose a website from the repository or upload your own.", array('class' => 'lead')));
            $repo = new html_button(array(new fa\view_icon('cloud-download'), " From repository"), array('class' => 'btn btn-success btn-lg', 'role' => 'button'));
            $upload = new html_button(array(new fa\view_icon('upload'), " Upload bundle"), array('class' => 'btn btn-success btn-lg control file-picker', 'role' => 'button'));
            $cell->add(new html_div(array($repo, $upload), array('class' => 'btn-group')));
            $cell->add(new html_input(array('class' => 'hidden bundle-picker', 'type' => 'file', 'name' => 'bundle-picker')));
            
            $cell = new bs\view_cell($panel, 12, 12, 6);
            $cell->add_class('text-center');
            $row->add($cell);
            
            $cell->add(new html_h2("Develop with Adapt"));
            $cell->add(new html_p("Create your own web application or extension with the features you need.", array('class' => 'lead')));
            $web_app = new html_button(array(new fa\view_icon('sitemap'), " Web app"), array('class' => 'btn btn-success btn-lg', 'role' => 'button'));
            $extension = new html_button(array(new fa\view_icon('cogs'), " Extension"), array('class' => 'btn btn-success btn-lg', 'role' => 'button'));
            $cell->add(new html_div(array($web_app, $extension), array('class' => 'btn-group')));
            
            
            
            //$cell->add(new html_a(array(new fa\view_icon('play'), " Install a pre written web app"), array('href' => '/apps', 'title' => 'Pick a pre written web app', 'class' => 'btn btn-success btn-lg', 'role' => 'button')));
            
            
            $this->add_view($jumbotron);
            
            /*$panel = new bs\view_panel(null, "I'd like to install a web app", bs\view_panel::NORMAL);
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
            $this->add_view($panel);*/
            
        }
        
        public function view_write_app(){
            $this->add_view(new html_h2('New Web App'));
            $this->add_view(new html_p(array('Great! If you\'re new to Adapt we recommend you view this super quick ', new html_a('tutorial', array('href' => '/app-tutorial')), '.')));
        }
        
        public function view_bundle_details(){
            return json_encode($this->response);
        }
        
        public function view_sql_testing(){
            $this->add_view(new html_h2("SQL Testing"));
            
            $this->add_view(new html_p("Work to do"));
            $this->add_view(
                new html_ul(
                    array(
                        new html_li("Reverse sql->update so it's the same way around as sql->select")
                    )
                )
            );
            
            $this->add_view(new html_h3("Complex selects"));
            $sql = $this
                ->data_source
                ->sql
                ->select(array('id' => 'bundle_version_id', 'label' => 'name', 'fullname' => new sql_concat('bv.name', sql::q(" "), 'bv.version')))
                ->from('bundle_version', 'bv')
                ->join('field', 'f', new sql_cond('field_id', sql::EQUALS, 'field_id'))
                ->left_join($this->data_source->sql->select('*')->from('field'), 's', 'field_id')
                ->where(
                    new sql_cond('date_deleted', sql::IS, new sql_null)
                )
                ->group_by('bv.name')
                ->group_by('bv.version')
                ->order_by('id')
                ->having(
                    new sql_or(
                        new sql_and(
                            new sql_cond('bv.version', sql::NOT_EQUALS, sql::q("1.0.0")),
                            new sql_cond('bv.name', sql::EQUALS, sql::q("foo"))
                        ),
                        new sql_between('id', 20, 25)
                    )
                )
                ->limit(1, 100);
            
            $this->add_view(new html_pre(new html_code($sql)));
            
            
            $this->add_view(new html_h3("Updates"));
            $sql = $this
                ->data_source
                ->sql
                ->update(array('field' => 'f', 'name' => 'n'))
                ->set('name', sql::q("Foo"))
                ->where(
                    new sql_or(
                        new sql_and(
                            new sql_cond('bv.version', sql::NOT_EQUALS, sql::q("1.0.0")),
                            new sql_cond('bv.name', sql::EQUALS, sql::q("foo"))
                        ),
                        new sql_between('id', 20, 25)
                    )
                );
            
            $this->add_view(new html_pre(new html_code($sql)));
            
            $this->add_view(new html_h3("Delete from"));
            $sql = $this
                ->data_source
                ->sql
                ->delete_from(array('field', 'bundle'))
                ->where(
                    new sql_or(
                        new sql_and(
                            new sql_cond('bv.version', sql::NOT_EQUALS, sql::q("1.0.0")),
                            new sql_cond('bv.name', sql::EQUALS, sql::q("foo"))
                        ),
                        new sql_between('id', 20, 25)
                    )
                );
            
            $this->add_view(new html_pre(new html_code($sql)));
            
            $this->add_view(new html_h3("Create database"));
            $sql = $this
                ->data_source
                ->sql
                ->create_database('fred');
            
            $this->add_view(new html_pre(new html_code($sql)));
            
            $this->add_view(new html_h3("Drop database"));
            $sql = $this
                ->data_source
                ->sql
                ->drop_database('fred');
            
            $this->add_view(new html_pre(new html_code($sql)));
            
            
            $this->add_view(new html_h3("Drop table"));
            $sql = $this
                ->data_source
                ->sql
                ->drop_table('data_type');
            
            $this->add_view(new html_pre(new html_code($sql)));
            
            
            $this->add_view(new html_h3("Alter table"));
            $sql = $this
                ->data_source
                ->sql
                ->alter_table('bundle_version')
                ->drop('type')
                ->change('name', 'bundle_name', 'varchar(24)', false)
                ->add('namespace', 'varchar(24)', false, null, null, null, 'type');
            
            $this->add_view(new html_pre(new html_code($sql)));
        }
    }
    
}

?>