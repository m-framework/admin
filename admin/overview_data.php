<?php

namespace modules\admin\admin;

use m\core;
use modules\pagination\client\pagination;
use m\config;
use m\registry;
use m\model;
use m\dynamic_view;

class overview_data {

    public static function items($model, array $fields, array $cond, dynamic_view $overview, dynamic_view $tpl, $options = null)
    {
        $items = [];

        $per_page = config::get('per_page') ? config::get('per_page') : 10;
        $page = registry::get('page_num') && empty($cond['page']) ? registry::get('page_num') : 1;
        $limit = [(($page * $per_page) - $per_page), $per_page];
        $sort = null;

        if (isset($options['sort']) && is_array($options['sort'])) {
            $sort = $options['sort'];
            unset($options['sort']);
        }

//        if (empty($data))
//            return false;

        $count = $model::call_static()->count($cond);

        if (!empty($count)) {
            new pagination($count);

            $data = $model::call_static()->s([], $cond, $limit, $sort)->all('object');
        }

        $w = empty($fields) ? 10 : round(90 / count($fields));

        $fields_th = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $fields_th[] = '<th width="' . $w . '%">' . $field . '</th>';
            }
        }

        $links_fields = ['path'];

        if (!empty($data)) {
            $n = 1;
            foreach ($data as $item) {

                $fields_data = [];

                if (!empty($fields)) {
                    foreach ($fields as $k => $field) {

                        $val = $item->{$k};

                        $fields_data[] = '<td width="' . $w . '%">' . (in_array($k, $links_fields) ? '<a href="' . $val . '">' . $val . '</a>' : $val) . '</td>';
                    }
                }

                $item->columns = implode('', $fields_data);

                if (!empty($options)) {
                    foreach ($options as $k => $v) {
                        $item->$k = (string)$v;
                    }
                }

                $item->n = $n;

                $items[] = $tpl->prepare($item);

                $n++;
            }
        }

        $overview_arr = [
            'fields_th' => implode('', $fields_th),
            'items' => implode('', $items),
            'count' => $count,
        ];

        if (!empty($options)) {
            $overview_arr = array_merge($overview_arr, $options);
        }

        return $overview->prepare($overview_arr);
    }
}
