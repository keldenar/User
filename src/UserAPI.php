<?php
/**
 * @author Bret Combast <keldenar@gmail.com>
 * @link https://www.linkedin.com/in/bretcombast
 */
namespace Ephemeral;

use Ephemeral\Interfaces\UserInterface;
use Silex\Application;

/**
 * Class UserAPI
 * @package Ephemeral
 */
class UserAPI implements UserInterface
{

    protected $data;

    protected $app;
    protected $userinfo = array();

    /**
     * @param Application $app
     * @param string $username
     */
    public function __construct(Application $app, $username = "") {
        $this->app = $app;
    }

    /**
     * @param $name
     * @param $params
     * @return string|null
     */
    public function __call($name, $params)
    {
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
     * @return bool
     */
    public function logged()
    {
        if ($this->app['session']->has("token_info")) {
            $token_info = $this->app['session']->get('token_info');
            if (array_key_exists("refresh_token", $token_info)) {
                return true;
            }
        }
    }

    /**
     * @param string $username
     * @return array|mixed
     */
    public function get($username="")
    {
        if ($username == "") {
            $response = $this->app['api']->get('profile', '/v1/profile');
        } else {
            $response = $this->app['api']->get('profile', "/v1/profile/$username");
        }
        $this->userinfo = json_decode($response->getBody(true), true);
        return $this->userinfo;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function set($data)
    {
        $endpoint = sprintf("/v1/profile/%s", $this->app['user']->username());
        $response = $this->app['api']->post('profile',$endpoint, $data);
        return json_decode($response->getBody(true), true);
    }

    public function update($username, $data)
    {

    }

    public function delete($username)
    {

    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->userinfo;
    }

    /**
     * @param $username
     * @param $target
     *
     * @todo Still needs some robust checking on the return value... or not
     */
    public function subscribe($username, $target)
    {
        $return = $app['api']->post("profile", "/v1/subscribe");
    }

    /**
     * @param $username
     * @param $target
     *
     * @todo Still needs some robust checking on the return value... or not
     */
    public function unsubscribe($username, $target)
    {
        $return = $app['api']->post("profile", "/v1/unsubscribe");
    }
}