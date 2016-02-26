<?php

namespace Ephemeral\Interfaces;

use Silex\Application;

interface UserInterface
{

    public function __construct(Application $app, $username = "");
    public function __call($name, $params);
    public function get($username = "");
    public function set($data);
    public function update($username, $data);
    public function delete($username);
    public function all();

}