<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\PixelModel;
use App\Config\ApiResponse;
use Firebase\JWT\JWT;

class AuthController extends BaseController {

    private $secret_key, $token_life, $issue_at, $expration_time;

    public function __construct(PixelModel $model) {
        parent::__construct();
        $this->model = $model;
        $this->secret_key = $_ENV['secret_key'];
        $this->token_life = $_ENV['TOKEN_LIFE'];

        $this->issue_at = time();
        $this->expration_time = $this->issue_at + (3600 * (int) $this->token_life);
    }

    public function login(Request $request, Response $response, $args) {
        /* {"username":"saqibahmaad@gmail.com","password":"zain","meta_info":"{\"ip\":\"127.0.0.1\",\"browser\":\"Chrome\",\"browserVersion\":\"131.0.0.0\",\"isMobile\":0,\"mobile\":\"\",\"osName\":\"Windows 10\",\"lang\":\"en\"}"} */
        $data = json_decode($request->getBody(), true);
        $username = $data['username'];
        $password = $data['password'];

        $donor = $this->model->isAuthorized($username, $password);

        if ($donor !== null) {

            $meta_info = $data['meta_info'] ?? NULL;
            $this->model->updatelastLogin((object) ["last_meta_info" => $meta_info, "donor_id" => $donor->id]);

            $payload = [
                'iat' => $this->issue_at,
                'exp' => $this->expration_time,
                'user_id' => $donor->id,
            ];

            // Encode JWT token
            $jwt = JWT::encode($payload, $this->secret_key, 'HS256');
            $response->getBody()->write(json_encode(ApiResponse::success(['token' => $jwt, 'donor' => $donor])));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(ApiResponse::unauthorized()));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function serverToken(Request $request, Response $response) {

        try {

            $payload = [
                'role' => 'server',
                'type' => 'non-expiring'
            ];
            $serverToken = JWT::encode($payload, $this->secret_key, 'HS256');

            $response->getBody()->write(json_encode(['server_token' => $serverToken]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function oAuthRegisterDonor(Request $request, Response $response, $args) {
        $data = json_decode($request->getBody(), true);
        try {

            $donor = $this->model->getDonorByUsername($data['email']);
            if ($donor === null) {
                $data['email_status'] = 1;
                $data['status'] = 1;
                $this->saveDonor($data);
                $donor = $this->model->getDonorByUsername($data['email']);
            } else {//update
                $donor->provider = $data['provider'];
                $donor->provider_id = $data['provider_id'];
                $donor->access_token = $data['access_token'];
                $donor->refresh_token = $data['refresh_token'];
                $this->model->updateDonorProvider($donor);
            }
            unset($donor->provider_id, $donor->access_token, $donor->refresh_token);

            $meta_info = $data['meta_info'] ?? NULL;
            $this->model->updatelastLogin((object) ["last_meta_info" => $meta_info, "donor_id" => $donor->id]);

            $payload = [
                'iat' => $this->issue_at,
                'exp' => $this->expration_time,
                'user_id' => $donor->id,
            ];

            // Encode JWT token
            $jwt = JWT::encode($payload, $this->secret_key, 'HS256');
            $response->getBody()->write(json_encode(ApiResponse::success(['token' => $jwt, 'donor' => $donor])));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }

            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function registerDonor(Request $request, Response $response, $args) {
        $data = json_decode($request->getBody(), true);
        try {

            $donor = $this->model->getDonorByUsername($data['email']);
            if ($donor === null) {
                $data['email_status'] = 0;
                $data['status'] = 0;
                $this->saveDonor($data);
                $donor = $this->model->getDonorByUsername($data['email']);

                /*$payload = [
                    'iat' => $this->issue_at,
                    'exp' => $this->expration_time,
                    'user_id' => $donor->id,
                ];*/

                $this->registrationEmail($donor);

                //$jwt = JWT::encode($payload, $this->secret_key, 'HS256');
                $response->getBody()->write(json_encode(ApiResponse::success(null, "An activation email has been sent to you. Please activate your account.")));
                return $response->withHeader('Content-Type', 'application/json');
            } else {//already exist
                $response->getBody()->write(json_encode(ApiResponse::error("The email address provided is already associated with an existing account. If you have forgotten your password, please use the 'Forgot Password' feature to reset it.", 409)));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("Error occured while processing your request.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function registrationEmail($donor) {
        $token = bin2hex(random_bytes(16));
        $link = $_ENV['APP_URL'] . "activate-account/" . $token;
        $this->model->updateActivationToken($token, $donor->id);

        $heading = 'Activate Your Humanity First Canada Account Now';
        $name = $donor->first_name;
        $message = 'Thank you for registering with Humanity First Canada!<br>
            <p>To complete your registration and gain full access to our services, please click the button below to verify your email address:<br>
            <a href="' . $link . '" class="button">Reset Password</a></p>
                <p class="note">If the button above is not clickable, please copy and paste the following link into your browser:</p>
                <p class="note">' . $link . '<br><br>If you did not request this email, please disregard this message.</p>';

        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => $name, 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
        $emailObject = new \stdClass();
        $emailObject->to = $donor->username;
        $emailObject->subject = $heading;
        $emailObject->hasAttachment = false;
        $emailObject->body = $html;

        return \App\Config\Pixel::sendEmailWithSendGrid($emailObject);
    }

    public function activateDonor(Request $request, Response $response, $args) {
        /* {
          "token":"975540406c4441f46898d881eb7b51e5",
          } */
        $data = json_decode($request->getBody(), true);
        try {
            $donor = $this->model->getDonorByToken(trim($data['token']));
            if ($donor !== null) {
                $this->model->activationEmail(trim($data['token']));
                $this->activationEmail($donor);
                $response->getBody()->write(json_encode(ApiResponse::success(null, "Your email has been successfully activated.")));
                return $response->withHeader('Content-Type', 'application/json');
            } else {//token expired
                $response->getBody()->write(json_encode(ApiResponse::success(null, "This activation link has been expired or not correct.", 400)));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function activationEmail($donor) {
        $link = $_ENV['APP_URL'] . "sign-in";
        
        $heading = 'Your Humanity First Canada Account is Now Active';
        $name = $donor->first_name;
        $message = 'Congratulations! Your account with Humanity First Canada has been successfully activated. Thank you for joining our community of compassionate individuals. We are thrilled to have you on board!<br><br>'
                . 'We look forward to working together to make a positive impact on the world.'
                . '<br><br><a href="' . $link . '" class="button">Sign in</a><br>to your account.</p>';

        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => $name, 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
        $emailObject = new \stdClass();
        $emailObject->to = $donor->username;
        $emailObject->subject = $heading;
        $emailObject->hasAttachment = false;
        $emailObject->body = $html;

        return \App\Config\Pixel::sendEmailWithSendGrid($emailObject);
    }

    public function forgotPassword(Request $request, Response $response, $args) {
        /*
          {
          "email":"saqibahmaad45@gmail.com",
          "ip": "127.0.0.1",
          "meta_info":"{\"ip\":\"127.0.0.1\",\"browser\":\"Chrome\",\"browserVersion\":\"131.0.0.0\",\"isMobile\":0,\"mobile\":\"\",\"osName\":\"Windows 10\",\"lang\":\"en\"}"
          }
         *          */
        $data = json_decode($request->getBody(), true);

        try {
            $limitExeded = $this->model->isIpRateLimited($data['ip']);
            if ($limitExeded === false) {//limit attack
                $email = $data['email'];
                $donor = $this->model->getDonorByUsername($email);
                if ($donor !== null) {
                    $token = $this->model->resetPwdRequest($data);
                    $link = $_ENV['APP_URL'] . "reset-password/" . $token;
                    $this->resetPwdEmail($donor, $link);
                }//not found still have to through same message
            }
            $response->getBody()->write(json_encode(ApiResponse::success(null, "If an account with that email/username exists, you will receive an email with instructions to reset your password.")));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function resetPwdEmail($donor, $link) {
        $heading = 'Password Reset Request | Humanity First Canada';
        $name = $donor->first_name;
        $message = '<p>We received a request to reset your password for your account associated with this email address. If you did not make this request, you can safely ignore this email.</p>
                                <p>If you would like to proceed with resetting your password, please click the link below:</br>
                                <a href="' . $link . '" class="button">Reset Password</a></p>
                                <p class="note">If the button above is not clickable, please copy and paste the following link into your browser:</p>
                                <p class="note">' . $link . '</p>                                
                                <p>This link will expire in 1 hour for your security. If you have any questions or need further assistance, feel free to contact our support team.</p>';
        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => $name, 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
        $emailObject = new \stdClass();
        $emailObject->to = $donor->email;
        $emailObject->subject = $heading;
        $emailObject->body = $html;
        $emailObject->hasAttachment = false;
        //$emailObject->pdfFilename = "HFC-" . $donation->receipt_id . ".pdf";
        //$emailObject->pdfContent = $pdfContent;

        return \App\Config\Pixel::sendEmailWithSendGrid($emailObject);
    }

    public function resetPassword(Request $request, Response $response, $args) {
        /* {
          "token":"975540406c4441f46898d881eb7b51e5",
          "password": "7thPixel"
          } */
        $data = json_decode($request->getBody(), true);
        try {

            $tokenRow = $this->model->getRowByToken($data['token']);
            if ($tokenRow !== null) {
                $donor = $this->model->getDonorByUsername($tokenRow->username);
                if ($donor !== null) {

                    $object = new \stdClass();
                    $object->token_id = $tokenRow->id;
                    $object->donor_id = $donor->id;
                    $object->password_hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
                    $this->model->updatePwdRequest($object);
                    $this->model->updateTokenRow($object);

                    $response->getBody()->write(json_encode(ApiResponse::success(null, "Your password has been successfully updated. Click here to {SIGNIN}.")));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {//token not found
                    $response->getBody()->write(json_encode(ApiResponse::success(null, "This password reset link has expired. Please try reseting password.", 404)));
                    return $response->withHeader('Content-Type', 'application/json');
                }
            } else {//token expired
                $response->getBody()->write(json_encode(ApiResponse::success(null, "This password reset link has expired. The valid time for password resets is 1 hour.", 400)));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function changePassword(Request $request, Response $response, $args) {
        /* {
          "old_password": "7thPixel",
          "password": "7thPixel"
          } */
        $donor_id = $request->getAttribute('user_id');

        try {
            $donor = $this->model->getDonor($donor_id);
            $data = json_decode($request->getBody(), true);

            if ($donor !== null) {
                $donorAuthorized = $this->model->isAuthorized($donor->username, $data['old_password']);
                if ($donorAuthorized !== null) {

                    $object = new \stdClass();
                    $object->donor_id = $donor->id;
                    $object->password_hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 10]);
                    $this->model->updatePwdRequest($object);

                    $response->getBody()->write(json_encode(ApiResponse::success(null, "Your password has been successfully updated.")));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {//old password doesnot match not found
                    $response->getBody()->write(json_encode(ApiResponse::error("The current password you entered is incorrect. Please double-check and try again.")));
                    return $response->withHeader('Content-Type', 'application/json');
                }
            } else {
                $response->getBody()->write(json_encode(ApiResponse::success(null, "donor not found.", 404)));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function updateProfile(Request $request, Response $response, $args) {

        $donor_id = $request->getAttribute('user_id');

        try {
            $donor = $this->model->getDonor($donor_id);
            $data = json_decode($request->getBody(), true);
            if ($donor !== null) {
                $data['donor_id'] = $donor->id;
                $this->model->updateDonor((object) $data);
                $donor = $this->model->getDonor($donor_id);
                $response->getBody()->write(json_encode(ApiResponse::success(["donor" => $donor], "Your profile has been successfully updated.")));
                return $response->withHeader('Content-Type', 'application/json');
            } else {//donor not found
                $response->getBody()->write(json_encode(ApiResponse::notFound("donor not found.")));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function volunteerRegistration(Request $request, Response $response, $args) {
        $data = json_decode($request->getBody(), true);
        try {

            $volunteer_id = $this->model->saveVolunteerRegistration($data);

            $availibility = $data['availibility'];
            if (@count($availibility) > 0) {
                foreach ($availibility as $a) {
                    $this->model->saveVolunteerAvailability($a, $volunteer_id);
                }
            }

            $response->getBody()->write(json_encode(ApiResponse::success()));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("Error occured while processing your request.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /* subscription */

    public function subscribe(Request $request, Response $response, $args) {
        /*
          {
          "email":"saqibahmaad45@gmail.com",
          "meta_info":"{\"ip\":\"127.0.0.1\",\"browser\":\"Chrome\",\"browserVersion\":\"131.0.0.0\",\"isMobile\":0,\"mobile\":\"\",\"osName\":\"Windows 10\",\"lang\":\"en\"}"
          }
         *          */
        $data = json_decode($request->getBody(), true);
        try {
            
            $isExist = $this->model->getSubscriptionByEmail($data['email']);
            
            if($isExist === null){
                $data['verification_token'] = bin2hex(random_bytes(16));
                $this->model->subscribeUser($data);
            }else{
                if((int)$isExist->status === 0){
                    $this->subscriptionEmail($data['email'], $isExist->verification_token);
                }
            }
            
            $response->getBody()->write(json_encode(ApiResponse::success(null, "Thank you for subscribing!. An activation email has been sent to you. Please activate your subscription.")));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            echo $e->getMessage();
            echo $e->getTraceAsString();
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            $response->getBody()->write(json_encode(ApiResponse::error("Error occured while processing your request.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function subscriptionEmail($email, $token) {

        $link = $_ENV['APP_URL'] . "activate-subscription/" . $token;
        $heading = 'Activate Your Humanity First Canada Subscription Now';
        $message = 'Thank you for subscribing with Humanity First Canada!<br>
            <p>To complete your subscription, please click the button below to activate your email address:<br>
            <a href="' . $link . '" class="button">Activate Email</a></p>
                <p class="note">If the button above is not clickable, please copy and paste the following link into your browser:</p>
                <p class="note">' . $link . '<br><br>If you did not request this email, please disregard this message.</p>';

        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => '', 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
        $emailObject = new \stdClass();
        $emailObject->to = $email;
        $emailObject->subject = $heading;
        $emailObject->hasAttachment = false;
        $emailObject->body = $html;

        return \App\Config\Pixel::sendEmailWithSendGrid($emailObject);
    }

    public function activateSubscription(Request $request, Response $response, $args) {
        /* {
          "token":"91e8b8dc58a146bd590b0f912bbc141d",
         * "meta_info":"{\"ip\":\"127.0.0.1\",\"browser\":\"Chrome\",\"browserVersion\":\"131.0.0.0\",\"isMobile\":0,\"mobile\":\"\",\"osName\":\"Windows 10\",\"lang\":\"en\"}"
          } */
        $data = json_decode($request->getBody(), true);
        try {
            $subScription = $this->model->getSubscriptionByToken(trim($data['token']));
            if ($subScription !== null) {
                $result = $this->model->activateSubscription($data['token'], $data['meta_info']);
                if ((int) $result > 0) {
                    if ((int) $subScription->status === 0) {
                        $this->subscriptionActivationEmail($subScription->email);
                    }
                    $response->getBody()->write(json_encode(ApiResponse::success(null, "Your email has been successfully activated.")));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {//token expired
                    $response->getBody()->write(json_encode(ApiResponse::success(null, "This activation link has been expired or not correct.", 400)));
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function subscriptionActivationEmail($email) {


        $heading = 'Your Humanity First Canada Subscription is Active Now';
        $message = 'Thank you for subscribing with Humanity First Canada!<br>
            <p>You have successfully subscribed to our newsletter. You will now receive updates from Humanity First Canada.</p>';

        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => '', 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
        $emailObject = new \stdClass();
        $emailObject->to = $email;
        $emailObject->subject = $heading;
        $emailObject->hasAttachment = false;
        $emailObject->body = $html;

        return \App\Config\Pixel::sendEmailWithSendGrid($emailObject);
    }

    public function un_subscribe(Request $request, Response $response, $args) {
        /*
          {
          "token":"token",
          "meta_info":"{\"ip\":\"127.0.0.1\",\"browser\":\"Chrome\",\"browserVersion\":\"131.0.0.0\",\"isMobile\":0,\"mobile\":\"\",\"osName\":\"Windows 10\",\"lang\":\"en\"}"
          }
         *          */
        $data = json_decode($request->getBody(), true);
        try {
            $subScrition = $this->model->getSubscriptionByToken($data['token']);
            if ($subScrition !== null) {
                $result = $this->model->unsubscribeUser($subScrition->email, $data['meta_info']);
                if ((int) $result > 0) {
                    if ((int) $subScrition->status === 1) {
                        $this->unSubscriptionEmail($subScrition->email);
                    }
                    $response->getBody()->write(json_encode(ApiResponse::success(null, "We're sorry to see you go! You have successfully unsubscribed from our email list")));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {//token expired
                    $response->getBody()->write(json_encode(ApiResponse::success(null, "This activation link has been expired or not correct.", 400)));
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
        } catch (ApiException $ex) {
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred while processing your request. Please try again later.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (\Exception $e) {
            // Handle any other exceptions\
            $this->logger->error(__CLASS__ . "\\" . __FUNCTION__ . ": " . $e->getMessage() . " Line: " . $e->getLine());
            if ((int) $_ENV['APP_DEBUG'] === 1) {
                $this->logger->error($e->getTraceAsString());
            }
            // Handle any other exceptions
            $response->getBody()->write(json_encode(ApiResponse::error("An unexpected error occurred on the server. Please try again later. If the problem persists, please contact support.")));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private function unSubscriptionEmail($email) {


        $heading = 'Unsubscribed from Humanity First Canada Emails';
        $message = '<p>We\'re sorry to see you go! You have successfully unsubscribed from our email list.<br><br>You will no longer receive emails from us.</p>';

        $html = \App\Config\Pixel::renderView(__DIR__ . '/../Views/email_template.php', ['heading' => $heading, 'name' => '', 'message' => $message, 'app_url' => $_ENV['APP_URL']]);
        $emailObject = new \stdClass();
        $emailObject->to = $email;
        $emailObject->subject = $heading;
        $emailObject->hasAttachment = false;
        $emailObject->body = $html;

        return \App\Config\Pixel::sendEmailWithSendGrid($emailObject);
    }
}
