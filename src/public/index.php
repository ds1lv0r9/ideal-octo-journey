<?php

mb_internal_encoding("UTF-8");
header('Content-Type: text/html; charset=utf-8');

require_once 'app/manuload.php';

$loc = mb_stripos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);

$requested = '';
if ($loc !== false)
{
    $requested = mb_strcut($_SERVER['REQUEST_URI'], mb_strlen($_SERVER['SCRIPT_NAME']) + 1);
}
else
{
    $requested = mb_strcut($_SERVER['REQUEST_URI'], 1);
}

$parameters = explode('/', $requested);

//delete empty params
$parameters = array_filter($parameters, fn($param) => !is_null($param) && $param !== '');
//reindex
$parameters = array_values($parameters);

$iParameters = count($parameters);
if ($iParameters > 2)
{
    $iParameters = 2;
}

$req = '';
for ($i = 0; $i < $iParameters; $i++)
{
    $req .= $parameters[$i];
    $req .= ($i === $iParameters - 1) ? '' : '/';
}

$id = 0;
if (count($parameters) >= 3)
{
    $id = $parameters[2];
}

switch ($req)
{
    case 'setup':
        $user->setup();
        break;
    case 'users/create':
        $user->create();
        break;
    case 'users/show':
        $user->show($id);
        break;
    case 'users/edit':
        $user->edit($id);
        break;
    case 'users/delete':
        $user->delete($id);
        break;
    case 'datatables/users':
        $user->getUsersDatatables();
        break;
    case 'users/index':
    default:
        $user->index();
        break;
}
