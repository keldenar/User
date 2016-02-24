<?php

namespace Ephemeral;

use Silex\Application;

class User {

    protected $data;

    protected $app;
    protected $userinfo;

    public function __construct(Application $app, $username = "") {
        $this->app = $app;

    }

    // THis is naive and will need to be updated to be more flexible
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

    public function logged() {
        if ($this->app['session']->has("token_info")) {
            $token_info = $this->app['session']->get('token_info');
            if (array_key_exists("refresh_token", $token_info)) {
                return true;
            }
        }
    }

    public function apiFetch($username="") {
        if ($username == "") {
            $response = $this->app['api']->get('profile', '/v1/profile');
        } else {
            $response = $this->app['api']->get('profile', "/v1/profile/$username");
        }
        $this->userinfo = json_decode($response->getBody(true), true);
        return $this->userinfo;
    }

    public function apiSet($data) {
        $endpoint = sprintf("/v1/profile/%s", $this->app['user']->username());
        $response = $this->app['api']->post('profile',$endpoint, $data);
        return json_decode($response->getBody(true), true);
    }

    public function fromJson() {

    }

    public function toJson() {

    }

}