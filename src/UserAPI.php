<?php

namespace Ephemeral;

use Ephemeral\Interfaces\UserInterface;
use Silex\Application;

class UserAPI implements UserInterface
{

    protected $data;

    protected $app;
    protected $userinfo = array();

    public function __construct(Application $app, $username = "") {
        $this->app = $app;
    }

    // THis is naive and will need to be updated to be more flexible
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

    public function logged()
    {
        if ($this->app['session']->has("token_info")) {
            $token_info = $this->app['session']->get('token_info');
            if (array_key_exists("refresh_token", $token_info)) {
                return true;
            }
        }
    }

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

    public function all()
    {
        return $this->userinfo;
    }

    public function subscribe($username, $target)
    {
        $return = $app['api']->post("profile", "/v1/subscribe");
    }

    public function unsubscribe($username, $target)
    {
        $return = $app['api']->post("profile", "/v1/unsubscribe");
    }
}