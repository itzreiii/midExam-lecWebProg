<?php
require_once '../config/database.php';
require_once '../src/controllers/EventController.php';

$eventController = new EventController();
$events = $eventController->getAllEvents();

include '../src/views/layouts/header.php';
include '../src/views/events/list.php';
include '../src/views/layouts/footer.php';

$route = $_SERVER['REQUEST_URI'] ?? '/';

switch ($route){
    case 'dashboard':
    require_once '../src/controllers/UserController.php';
    $controller = new UserController();
    break;
}
