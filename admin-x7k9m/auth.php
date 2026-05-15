<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

function authenticate($username, $password) {
    return login($username, $password);
}
