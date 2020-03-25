<?php

namespace modules\admin\admin;

use m\core;
use m\module;
use m\registry;
use m\view;
use m\model;

class dashboard extends module
{

    protected $css = [
        '/css/dashboard.css'
    ];

    public function _init()
    {
        /**
         * Last registered customers
         */
        $new_users = '';

        if (isset($this->view->new_users) && isset($this->view->new_users_item)
            && class_exists('\modules\users\models\users_info')) {

            $users = model::call_static('\modules\users\models\users_info')
                ->select(
                    ['users_info.*'],
                    ['users' => ['profile' => 'profile']],
                    ['users.site' => $this->site->id, 'users.confirmed' => 1],
                    [],
                    ['users_info.date' => 'DESC'],
                    [20]
                )
                ->all('object');

            $users_arr = [];

            if (!empty($users)) {
                foreach ($users as $user) {
                    $users_arr[] = $this->view->new_users_item->prepare($user);
                }

                $new_users = $this->view->new_users->prepare(['users' => implode('', $users_arr)]);
            }
        }

        /**
         * Last comments
         */
        $last_comments = '';

        if (isset($this->view->last_comments) && isset($this->view->last_comments_item)
            && class_exists('\modules\comments\models\comments')) {

            $comments = model::call_static('\modules\comments\models\comments')
                ->s([], ['site' => $this->site->id], [10])->all('object');

            $comments_arr = [];

            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    $comments_arr[] = $this->view->last_comments_item->prepare($comment);
                }
                $last_comments = $this->view->last_comments->prepare(['comments' => implode('', $comments_arr)]);
            }
        }

        /**
         * Last shop orders
         */
        $last_orders = '';

        if (isset($this->view->last_orders) && isset($this->view->last_orders_item)
            && class_exists('\modules\shop\models\shop_orders')) {

            $orders = model::call_static('\modules\shop\models\shop_orders')
                ->s([], ['site' => $this->site->id, 'status' => null], [10])->all('object');

            $orders_arr = [];

            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $orders_arr[] = $this->view->last_orders_item->prepare($order);
                }
                $last_orders = $this->view->last_orders->prepare(['orders' => implode('', $orders_arr)]);
            }
        }


        view::set('content', $this->view->dashboard->prepare([
            /**
             * Users
             */
            'all_users_amount' => class_exists('\modules\users\models\users') ?
                model::call_static('\modules\users\models\users')->count() : 0,
            'today_users_amount' => class_exists('\modules\users\models\users_info') ?
                model::call_static('\modules\users\models\users_info')
                    ->count([
                        'site' => $this->site->id,
                        "date<'" . date('Y-m-d 23:59:59') . "'",
                        "date>'" . date('Y-m-d 00:00:00') . "'"
                    ]) : 0,
            'social_networks_profiles' => class_exists('\modules\users\models\users_socials') ?
                model::call_static('\modules\users\models\users_socials')
                    ->count(['site' => $this->site->id]) : 0,
            /**
             * Visitors
             */
            'today_visitors_amount' => class_exists('\modules\users\models\visitors_history') ?
                model::call_static('\modules\users\models\visitors_history')
                    ->count_distinct('visitor',
                        [
                            'site' => $this->site->id,
                            "date<'" . date('Y-m-d 23:59:59') . "'",
                            "date>'" . date('Y-m-d 00:00:00') . "'"
                        ]) : 0,
            'products_visitors_amount' => class_exists('\modules\users\models\visitors_history') ?
                model::call_static('\modules\users\models\visitors_history')
                    ->count(['site' => $this->site->id, 'related_model' => 'shop_products',]) : 0,
            /**
             * Articles
             */
            'articles_amount' => class_exists('\modules\articles\models\articles') ?
                model::call_static('\modules\articles\models\articles')->count(['site' => $this->site->id]) : 0,
            'news_amount' => class_exists('\modules\articles\models\news') ?
                model::call_static('\modules\articles\models\news')->count(['site' => $this->site->id]) : 0,
            /**
             * Comments
             */
            'comments_amount' => class_exists('\modules\comments\models\comments') ?
                model::call_static('\modules\comments\models\comments')->count(['site' => $this->site->id]) : 0,
            'items_comments_amount' => class_exists('\modules\comments\models\comments') ?
                model::call_static('\modules\comments\models\comments')
                    ->count([
                        'site' => $this->site->id,
                        'related_model' => 'shop_products',
                    ]):0,
            /**
             * Products
             */
            'products_amount' => class_exists('\modules\shop\models\shop_products') ?
                model::call_static('\modules\shop\models\shop_products')->count(['site' => $this->site->id]) : 0,
            'baskets_products_amount' => class_exists('\modules\shop\models\shop_baskets_products') ?
                model::call_static('\modules\shop\models\shop_baskets_products')
                    ->count(['site' => $this->site->id]) : 0,
            /**
             * Sales
             */
            'sales_sum' => class_exists('\modules\shop\models\shop_sales') ?
                model::call_static('\modules\shop\models\shop_sales')->count(['site='.$this->site->id]) : 0,
            'today_sales_sum' => class_exists('\modules\shop\models\shop_baskets_products') ?
                model::call_static('\modules\shop\models\shop_baskets_products')
                    ->count([
                        'site='.$this->site->id,
                    ]) : 0,

            'new_users' => $new_users,
            'last_comments' => $last_comments,
            'last_orders' => $last_orders,
        ]));
    }
}