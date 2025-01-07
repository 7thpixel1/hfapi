<?php
/*
error_reporting(1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/var/www/api/public/logs/php_errors.log');
*/
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Config\Database;
use App\Config\JwtAuthMiddleware;
use App\Models\PixelModel;
use App\Controllers\DonationController;
use App\Controllers\AuthController;
use App\Controllers\PaymentController;
use Dotenv\Dotenv;


require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Toronto');

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
$container->set(PaymentController::class, function($c) {
    // Return new AuthController with injected PixelModel
    return new PaymentController($c->get(PixelModel::class));
});


AppFactory::setContainer($container);
$app = AppFactory::create();

$jwtMiddleware = new JwtAuthMiddleware($_ENV['secret_key'], $_ENV['server_token']);



$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("HF API!");
    return $response;
});


//$app->get('/server-token', [AuthController::class, 'serverToken']);
$app->post('/login', [AuthController::class, 'login'])->add($jwtMiddleware);
$app->post('/oauth-donor', [AuthController::class, 'oAuthRegisterDonor'])->add($jwtMiddleware);
$app->post('/register-donor', [AuthController::class, 'registerDonor'])->add($jwtMiddleware);
$app->post('/activate-donor', [AuthController::class, 'activateDonor'])->add($jwtMiddleware);

$app->post('/subscribe', [AuthController::class, 'subscribe'])->add($jwtMiddleware);
$app->post('/activate-subscription', [AuthController::class, 'activateSubscription'])->add($jwtMiddleware);
$app->post('/un-subscribe', [AuthController::class, 'un_subscribe'])->add($jwtMiddleware);

$app->post('/volunteer-registration', [AuthController::class, 'volunteerRegistration'])->add($jwtMiddleware);
$app->post('/forgot-password', [AuthController::class, 'forgotPassword'])->add($jwtMiddleware);
$app->post('/reset-password', [AuthController::class, 'resetPassword'])->add($jwtMiddleware);// without old password 
$app->post('/change-password', [AuthController::class, 'changePassword'])->add($jwtMiddleware);//token should be donorToken

$app->post('/update-profile', [AuthController::class, 'updateProfile'])->add($jwtMiddleware);//token should be donorToken

$app->get('/donations[/{page}]', [DonationController::class, 'getDonations'])->add($jwtMiddleware);
$app->get('/donation/{donation_id}', [DonationController::class, 'getDonation'])->add($jwtMiddleware);
$app->get('/send-donation/{donation_id}/{donor_id}', [DonationController::class, 'sendDonation'])->add($jwtMiddleware);
$app->get('/annual-statement/{year}', [DonationController::class, 'annualStatement'])->add($jwtMiddleware);
$app->get('/rec-donations[/{page}]', [DonationController::class, 'getRecDonations'])->add($jwtMiddleware);



//$app->post('/donate', [PaymentController::class, 'donate'])->add($jwtMiddleware);
$app->post('/donate-now', [PaymentController::class, 'processSales'])->add($jwtMiddleware);
$app->post('/save-card', [PaymentController::class, 'saveCard'])->add($jwtMiddleware);




//$app->get('/donations[/{page}]', [DonationController::class, 'getDonations']);

$app->run();
