<?php

namespace extensions\forms;
use \frameworks\adapt as adapt;

/* Prevent direct access */
defined('ADAPT_STARTED') or die;

$adapt = $GLOBALS['adapt'];

/*
 * Include  css & javascript
 */
$adapt->dom->head->add(new adapt\html_link(array('type' => 'text/css', 'rel' => 'stylesheet', 'href' => '/adapt/applications/adapt_setup/static/css/setup.css')));
$adapt->dom->head->add(new html_script(array('type' => 'text/javascript', 'src' => '/adapt/applications/adapt_setup/static/js/master.js')));

?>