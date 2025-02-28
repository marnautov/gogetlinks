<?php

namespace Amxm\Gogetlinks;


interface GogetlinksInterface {


    public function login(string $email, string $password);

    public function getSites(): array;

    public function getTasks(): array;


}