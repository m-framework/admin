<?php

namespace modules\admin\client;

use m\config;
use m\i18n;
use m\module;
use m\view;
use modules\modules\models\modules_sequence;

class dashboard_menu extends module {

//    protected $js = [
//        '/js/dashboard_menu.js'
//    ];

    private $module_json;

    protected $css = [
        '/css/dashboard_menu.css'
    ];

    protected $js = [
        '/js/admin_menu_item.js'
    ];

    public function _init()
    {
        $links = [];

        $modules_path = '/m-framework/modules/';

        $modules_sequence = modules_sequence::call_static()->s(['*'], [], [10000])->all();
        $sequence = [];

        if (!empty($modules_sequence)) {
            foreach ($modules_sequence as $module_sequence) {
                $sequence[$module_sequence['module']] = $module_sequence['sequence'];
            }
        }

        $this->process_modules_dir($modules_path, $sequence, $links);
        if (config::get('application_path')) {
            $this->process_modules_dir(config::get('application_path') . 'modules/', $sequence, $links);
        }

        ksort($links);

        i18n::init($modules_path . 'admin/admin/i18n/');

        view::set('dashboard_menu', $this->view->dashboard_menu->prepare([
            'links' => implode('', $links),
            'min_class' => !empty($_COOKIE['toggle_admin_menu']) && $_COOKIE['toggle_admin_menu'] == 'min' ? 'min' : '',
        ]));
    }

    private function get_sub_links($module, $path)
    {
        $sub_menu_links = [];

        if (!is_dir(config::get('root_path') . $path . $module))
            return $sub_menu_links;

        $module_files = array_diff(scandir(config::get('root_path') . $path . $module), ['.', '..']);

        if (!in_array('admin', $module_files) || !in_array('module.json', $module_files)
            || !is_file(config::get('root_path') . $path . $module . '/module.json')
            || !isset($this->view->dashboard_menu_link))
            return $sub_menu_links;

        $this->module_json = json_decode(file_get_contents(config::get('root_path') . $path . $module . '/module.json'), true);

        if (is_file(config::get('root_path') . $path . $module . '/admin/i18n/' . $this->language . '.php')) {
            i18n::init($path . $module . '/admin/i18n/');
        }

        /**
         * Prepare a sub-menu
         */
        if (!empty($this->module_json)
            && is_array($this->module_json)
            && !empty($this->module_json['dashboard_menu'])
            && is_array($this->module_json['dashboard_menu'])
            && isset($this->view->dashboard_submenu_link))
        {
            foreach ($this->module_json['dashboard_menu'] as $address => $name) {
                $sub_menu_links[] = $this->view->dashboard_submenu_link->prepare([
                    'address' => '/' . config::get('admin_panel_alias') . '/' . $module . $address,
                    'name' => $name,
                    'link_title' => strip_tags($name),
                ]);
            }

            if (is_dir(config::get('root_path') . $path . $module . '/client/i18n/')) {
                $sub_menu_links[] = $this->view->dashboard_submenu_link->prepare([
                    'address' => '/' . config::get('admin_panel_alias') . '/translations/overview/' . $module,
                    'name' => '<i class="fa fa-language"></i> ' . i18n::get('Clients translations'),
                    'link_title' => '',
                ]);
            }
        }

        return $sub_menu_links;
    }

    private function process_modules_dir($path, $sequence, &$links)
    {
        $modules = array_diff(scandir(config::get('root_path') . $path), ['.', '..']);

        if (empty($modules)) {
            return false;
        }

        if (!empty($modules)) {
            foreach ($modules as $module) {

                $sub_menu_links = $this->get_sub_links($module, $path);

                $application_sub_menu_links = $this->get_sub_links($module, config::get('application_path') . 'modules/');

                if (!empty($application_sub_menu_links)) {
                    $sub_menu_links = $application_sub_menu_links;
                }

                if (empty($sub_menu_links))
                    continue;

                $n = empty($sequence[$module]) ? count($links) - 1 : $sequence[$module];

                $links[$n] = $this->view->dashboard_menu_link->prepare([
                    'address' => '/' . config::get('admin_panel_alias') . '/' . $module,
//                    'address' => '#',
                    'icon' => empty($this->module_json['icon']) ? '' : $this->module_json['icon'],
                    'name' => i18n::get($this->module_json['title']),
                    'link_title' => i18n::get($this->module_json['title']),
                    'sub_menu' => implode('', $sub_menu_links),
                ]);
            }
        }

    }
}