<?php

namespace modules\admin\client;

use m\config;
use m\i18n;
use m\module;
use m\core;
use m\registry;
use m\template;
use m\view;

class authorisation extends module {

    protected $css = [
        '/css/authorisation.css'
    ];

    public function _init()
    {
        core::$template->set_template_file('blank');
        core::$template->views_parsing();

        view::set('content', $this->view->authorisation->prepare());
    }
}