<?php

namespace Ephemeral;

use Doctrine\MongoDB\Collection;
use Doctrine\DBAL\Connection;
use Ephemeral\Interfaces\UserInterface;
use Silex\Application;

class UserDB implements UserInterface {

    protected $app;
    protected $mongo;

    public function __construct(Application $app, $username="") {
        $this->app = $app;
    }

    public function __call($name, $params) {
        if (array_key_exists($name, $this->userinfo)) {
            if (is_scalar($this->userinfo[$name])) {
                return $this->userinfo[$name];
            } elseif (is_array($this->userinfo[$name])) {
                return $this->userinfo[$name][$params[0]];
            }
        }
        return null;
    }

    public function get($username = "") {
        // The template
        $user_template = [
            "username" => '',
            "email" => '',
            "fullname" => "",
            "bio" => "",
            "subscribers" => 0,
            "subscribed" => [
                "count" => 0,
                "users" => [
                ],
            ],
        ];

        $collection = $this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users');
        $user = $collection->findOne(array("username" => $username));
        $user = is_null($user) ? $user_template : $user;
        return $user;
    }

    public function set($payload) {
        //get the user if it exists traverse the payload and replace values in the user then reset in the database.
        $user = $this->getUser($payload['username']);
        foreach ($payload as $key=>$value) {
            $user[$key] = $value;
        }
        unset($user['password']);
        unset($user['secure']);

        $user = $this->updateUser($this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users'), $user);
        return $user;
    }

    public function updateUser(Collection $collection, $user) {
        dump($collection);
        $collection->update(array('username' => $user['username']), $user, array("upsert" => true) );
        return $collection->findOne(array('username' => $user['username']));
    }
}