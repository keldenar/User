<?php

namespace Ephemeral\Interfaces;

interface UserInterface
{

    public function __construct(Application $app, $username = "");
    public function __call($name, $params);
    public function get($username = "");
    public function set($data);

}