<?php
/**
 * @author Bret Combast <keldenar@gmail.com>
 * @link https://www.linkedin.com/in/bretcombast
 */

namespace Ephemeral;

use Doctrine\MongoDB\Collection;
use Doctrine\DBAL\Connection;
use Ephemeral\Interfaces\UserInterface;
use Silex\Application;

/**
 * Class UserDB
 * @package Ephemeral
 */
class UserDB implements UserInterface
{

    protected $app;
    protected $mongo;
    protected $userinfo = array();

    /**
     * @param Application $app
     * @param string $username
     */
    public function __construct(Application $app, $username="")
    {
        $this->app = $app;
    }

    /**
     * @param $name
     * @param $params
     * @return string|array|null
     */
    public function __call($name, $params) {
        if (array_key_exists($name, $this->userinfo)) {
            if (is_scalar($this->userinfo[$name])) {
                return $this->userinfo[$name];
            } elseif (is_array($this->userinfo[$name])) {
                if ($params == []) {
                    return $this->userinfo[$name];
                } else {
                    return $this->userinfo[$name][$params[0]];
                }
            }
        }
        return null;
    }

    /**
     * @param string $username
     * @return array
     */
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

    /**
     * @param string $id
     * @return array
     */
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

    /**
     *
     * Sets a user into the database.
     *
     * Notes: Run a get change some values run a set... Simple
     *
     * @param $user
     * @return array
     */
    public function set($user)
    {
        $collection = $this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users');
        $collection->update(array('username' => $user['username']), $user, array("upsert" => true) );
        return $collection->findOne(array('username' => $user['username']));
    }

    /**
     * @param $username
     * @param $data
     * @return array
     */
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

    /**
     * @param Collection $collection
     * @param $user
     * @return mixed
     */
    public function updateUser(Collection $collection, $user)
    {
        $collection->update(array('username' => $user['username']), $user, array("upsert" => true) );
        return $collection->findOne(array('username' => $user['username']));
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->userinfo;
    }

    /**
     * @param $query
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function findUser($query, $offset = 0, $limit = 10)
    {
        $collection = $this->app['mongodb']->selectDatabase("ephemeral")->selectCollection('users');
        $users=[];
        $this->findByUsername($collection, $query, $users, $offset, $limit);
        $this->findByFullname($collection, $query, $users, $offset, $limit);
        return $users;
    }

    /**
     * @param Collection $collection
     * @param $query
     * @param $users
     * @param $offset
     * @param $limit
     */
    public function findByUsername(Collection $collection, $query, &$users, $offset, $limit)
    {
        $results = $collection->find(array('username' => new \MongoRegex("/^$query/")))->limit($limit)->skip($offset);
        $this->mergeResults($results, $users);
    }

    /**
     * @param Collection $collection
     * @param $query
     * @param $users
     * @param $offset
     * @param $limit
     */
    public function findByFullname(Collection $collection, $query, &$users, $offset, $limit)
    {
        $results = $collection->find(array('fullname' => new \MongoRegex("/^$query/")))->limit($limit)->skip($offset);
        $this->mergeResults($results, $users);
    }

    /**
     * @param $results
     * @param $users
     */
    public function mergeResults($results, &$users)
    {
        foreach ($results as $result) {
            $users[$result['username']] = $result;
        }
    }

    /**
     * @param $requested
     * @param $user
     * @return mixed
     */
    public function pruneUser($requested, $user)
    {
        foreach ($user as $key=>$value) {
            if (in_array($key, $requested)) continue;
            unset($user[$key]);
        }
        return $user;
    }

    /**
     *
     * Adds the target to the users subscription list
     *
     * @param $username
     * @param $target
     */
    public function subscribe($username, $target)
    {
        // Get the user
        $user = $this->get($payload['username']);
        $subscribed = $user['subscribed'];
    }

    /**
     *
     * Removes the target from the users subscription list
     *
     * @param $username
     * @param $target
     */
    public function unsubscribe($username, $target)
    {

    }
}