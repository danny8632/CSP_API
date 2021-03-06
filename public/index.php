<?php

declare(strict_types=1);


// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

/**
 * This sections is to force the use of the frontend
 * but only if the uri does not contain api
 */
$serverRequestPath = $_SERVER['REQUEST_URI'] ?? '/';
$isApiCall = strpos($serverRequestPath, 'api');

if($isApiCall === false) {
    echo json_encode(["Error" => "Try using api route"]);
    return;
}



use app\controllers\AuthController;
use app\controllers\DepartmentController;
use app\controllers\DepartmentRelationController;
use app\controllers\HourReportController;
use app\controllers\SealedShiftsController;
use app\controllers\UserController;
use app\controllers\ShiftController;
use app\controllers\ShiftTradeController;
use app\controllers\TimestampController;
use app\controllers\VacantController;
use app\core\Application;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$config = [
    'userClass'  => \app\models\User::class,
    'token_salt' => $_ENV['TOKEN_SALT'],
    'db' => [
        'dsn'      => $_ENV['DB_DSN'],
        'user'     => $_ENV['DB_USRNAME'],
        'password' => $_ENV['DB_PASSWORD'],
    ],
    'auth_key' => $_ENV['AUTH_KEY']
];


$app = new Application(dirname(__DIR__), $config);

$app->router->post('/login', [AuthController::class, 'login']);
$app->router->post('/register', [AuthController::class, 'register']);
$app->router->post('/tokenRefresh', [AuthController::class, 'tokenRefresh']);

$app->router->get('/departmentRelation', [DepartmentRelationController::class, 'get']);
$app->router->post('/departmentRelation', [DepartmentRelationController::class, 'post']);
$app->router->delete('/departmentRelation', [DepartmentRelationController::class, 'delete']);

$app->router->get('/department', [DepartmentController::class, 'get']);
$app->router->post('/department', [DepartmentController::class, 'post']);
$app->router->put('/department', [DepartmentController::class, 'put']);
$app->router->delete('/department', [DepartmentController::class, 'delete']);


$app->router->get('/user', [UserController::class, 'get']);
$app->router->post('/user', [UserController::class, 'post']);
$app->router->put('/user', [UserController::class, 'put']);
$app->router->delete('/user', [UserController::class, 'delete']);

$app->router->get('/shift', [ShiftController::class, 'get']);
$app->router->post('/shift', [ShiftController::class, 'post']);
$app->router->put('/shift', [ShiftController::class, 'put']);
$app->router->delete('/shift', [ShiftController::class, 'delete']);

$app->router->get('/vacant', [VacantController::class, 'get']);
$app->router->post('/vacant', [VacantController::class, 'post']);
$app->router->put('/vacant', [VacantController::class, 'put']);
$app->router->delete('/vacant', [VacantController::class, 'delete']);


$app->router->get('/shiftTrade', [ShiftTradeController::class, 'get']);
$app->router->post('/shiftTrade', [ShiftTradeController::class, 'post']);
$app->router->delete('/shiftTrade', [ShiftTradeController::class, 'delete']);
$app->router->post('/acceptTrade', [ShiftTradeController::class, 'acceptTrade']);
$app->router->post('/declineTrade', [ShiftTradeController::class, 'declineTrade']);
$app->router->get('/findPeople', [ShiftTradeController::class, 'findPeople']);


$app->router->get('/timestamp', [TimestampController::class, 'get']);
$app->router->post('/timestamp', [TimestampController::class, 'post']);
$app->router->put('/timestamp', [TimestampController::class, 'put']);

$app->router->get('/sealedShifts', [SealedShiftsController::class, 'get']);
$app->router->post('/sealedShifts', [SealedShiftsController::class, 'post']);
$app->router->delete('/sealedShifts', [SealedShiftsController::class, 'delete']);

$app->run();