<?php

namespace Ephemeral;

use Doctrine\MongoDB\Collection;
use Doctrine\DBAL\Connection;
use Ephemeral\Interfaces\UserInterface;
use Silex\Application;

class UserDB implements UserInterface
{

    protected $app;
    protected $mongo;
    protected $userinfo = array();

    public function __construct(Application $app, $username="")
    {
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

    public function get($username = "")
    {
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
        $this->userinfo = $user;
        return $user;
    }

    public function getById($id = "")
    {
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
        $user = $collection->findOne(array("_id" => $id));
        $user = is_null($user) ? $user_template : $user;
        $this->userinfo = $user;
        return $user;
    }

    public function set($payload)
    {
        //get the user if it exists traverse the payload and replace values in the user then reset in the database.
        $user = $this->get($payload['username']);
        foreach ($payload as $key=>$value) {
            $user[$key] = $value;
        }
        unset($user['password']);

        $user = $this->updateUser($this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users'), $user);
        return $user;
    }

    public function update($username, $data)
    {
        $user = $this->get($username);

        foreach ($data as $key=>$value) {
            $user[$key] = $value;
        }
        $collection = $this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users');
        $collection->update(array('_id' => $user['_id']), $user, array("upsert" => true) );
        $this->userinfo = $user;
        return $this->userinfo;

    }

    public function delete($username)
    {

    }

    public function updateUser(Collection $collection, $user)
    {
        $collection->update(array('username' => $user['username']), $user, array("upsert" => true) );
        return $collection->findOne(array('username' => $user['username']));
    }

    public function all()
    {
        return $this->userinfo;
    }

    public function findUser($query, $offset = 0, $limit = 10)
    {
        $collection = $this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users');
        $users=[];
        $this->findByUsername($collection, $query, $users, $offset, $limit);
        $this->findByFullname($collection, $query, $users, $offset, $limit);
        return $users;
    }

    public function findByUsername(Collection $collection, $query, &$users, $offset, $limit)
    {
        $results = $collection->find(array('username' => new \MongoRegex("/^$query/")))->limit($limit)->skip($offset);
        $this->mergeResults($results, $users);
    }

    public function findByFullname(Collection $collection, $query, &$users, $offset, $limit)
    {
        $results = $collection->find(array('fullname' => new \MongoRegex("/^$query/")))->limit($limit)->skip($offset);
        $this->mergeResults($results, $users);
    }

    public function mergeResults($results, &$users)
    {
        foreach ($results as $result) {
            $users[$result['username']] = $result;
        }
    }

    public function pruneUser($requested, $user)
    {
        foreach ($user as $key=>$value) {
            if (in_array($key, $requested)) continue;
            unset($user[$key]);
        }
        return $user;
    }
}