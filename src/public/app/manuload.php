<?php

require_once 'Controller.php';
require_once 'DB_Model.php';
require_once 'Sessions.php';
require_once 'Settings.php';
require_once 'Template.php';
require_once 'Tools.php';
require_once 'Controllers/User.php';
require_once 'Repositories/UsersRepository.php';

$settings = new Settings();
$dbModel = new DB_Model($settings);
$session = new Sessions();

$usersRepository = new UsersRepository($settings);

$template = new Template();

$user = new User($usersRepository, $template, $session);
