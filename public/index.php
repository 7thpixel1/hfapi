<?php
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Config\Database;
use App\Config\JwtAuthMiddleware;
use App\Models\PixelModel;
use App\Controllers\DonationController;
use App\Controllers\AuthController;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = new Container();

// Register dependencies
$container->set(Database::class, function() {
    return new Database();
});
$container->set(PixelModel::class, function($c) {
    return new PixelModel($c->get(Database::class));
});
$container->set(DonationController::class, function($c) {
    return new DonationController($c->get(PixelModel::class));
});

$container->set(AuthController::class, function($c) {
    // Return new AuthController with injected PixelModel
    return new AuthController($c->get(PixelModel::class));
});


AppFactory::setContainer($container);
$app = AppFactory::create();

$jwtMiddleware = new JwtAuthMiddleware($_ENV['secret_key']);



$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});



$app->post('/login', [AuthController::class, 'login']);
$app->get('/donations[/{page}]', [DonationController::class, 'getDonations'])->add($jwtMiddleware);
$app->get('/donation/{donation_id}', [DonationController::class, 'getDonation'])->add($jwtMiddleware);


//$app->get('/donations[/{page}]', [DonationController::class, 'getDonations']);

$app->run();
