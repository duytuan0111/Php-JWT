<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/API_Controller.php';

class Api_Test extends API_Controller
{
    public function __construct() {
        parent::__construct();
        $this->load->model('api_model');
        $this->load->library('form_validation');
    }

    public function demo()
    {
        header("Access-Control-Allow-Origin: *");

        // API Configuration
        $this->_apiConfig([
            /**
             * By Default Request Method `GET`
             */
            'methods' => ['POST'], // 'GET', 'OPTIONS'

            /**
             * Number limit, type limit, time limit (last minute)
             */
            'limit' => [5, 'ip', 'everyday'],

            /**
             * type :: ['header', 'get', 'post']
             * key  :: ['table : Check Key in Database', 'key']
             */
            'key' => ['POST', $this->key() ], // type, {key}|table (by default)
        ]);
        
        // return data
        $this->api_return(
            [
                'status' => true,
                "result" => "Return API Response",
            ],
        200);
    }

    /**
     * Check API Key
     *
     * @return key|string
     */
    private function key()
    {

        return 1452;
    }

    public function login()
    {
        header("Access-Control-Allow-Origin: *");
        $email      = strip_tags($_POST['email']);
        $password   = strip_tags($_POST['password']);
        $user_type  = strip_tags($_POST['user_type']); // 0: Phụ huynh, 1: Gia sư
        // config api
        $this->_apiConfig([
            'methods' => ['POST'],
        ]);
             
        if (!empty($email) &&  !empty($password)) {
             //  Check login
            $checkUser = $this->api_model->checkUser($email, md5($password), $user_type);
            $this->load->library('authorization_token');
            if (count($checkUser) > 0) { 
                $payload = [
                    'id'            => $checkUser['UserID'],
                    'ep_email'      => $checkUser['Email'],
                    'ep_name'       => $checkUser['Name'],
                    'ep_phone'      => $checkUser['Phone'],
                    'ep_logo'       => $checkUser['ep_logo'],
                    'ep_address'    => $checkUser['Address'],
                    'ep_birthday'   => $checkUser['Birth'],
                    'create_time'   => $checkUser['CreateDate'],
                    'update_time'   => $checkUser['UpdateDate'],
                    'active'        => $checkUser['Active'],
                    'ep_lat'        => $checkUser['ep_lat'],
                    'ep_long'       => $checkUser['ep_long'],
                ];
                $token = $this->authorization_token->generateToken($payload);

        // return data
                $this->api_return(
                    [
                        'status' => true,
                        'result' => [
                            'token' => $token,
                        ],
                        'messages' => 'Đăng nhập thành công'

                    ],
                    200);
            } else {
                $this->api_return(
                    [
                        'status' => false,
                        "messages" => "Tên tài khoản hoặc mật khẩu không chính xác"

                    ],
                    200);
            }
        } else {
            $this->api_return(
                [
                    'status' => false,
                    "messages" => "Vui lòng điền đầy đủ thông tin đăng nhập"
                ],
                200);
        }


    }

    public function parentsRegister() { // Phụ huynh đăng ký
        header("Access-Control-Allow-Origin: *");
        $email              = strip_tags($_POST['email']);
        $password           = strip_tags($_POST['password']);
        $password_confirm   = strip_tags($_POST['password_confirm']);
        $name               = strip_tags($_POST['name']);
        $phone              = strip_tags($_POST['phone']);

         // config api
        $this->_apiConfig([
            'methods' => ['POST'],
        ]);

        $this->form_validation->set_rules('name', 'name', 'required|trim');
        $this->form_validation->set_rules('password', 'password', 'trim|min_length[6]|max_length[20]|required');
        $this->form_validation->set_rules('password_confirm', 'password confirm', 'required|matches[password]');
        $this->form_validation->set_rules('email', 'email', 'required|trim|valid_email');
        $this->form_validation->set_rules('phone', 'phone', 'required|numeric|min_length[10]|max_length[11]');

        if ($this->form_validation->run()  == true) {
            $check_email = $this->api_model->checkEmail();
        }  else {
            $this->api_return(
                [
                    'status' => false,
                    "messages" => strip_tags(validation_errors())
                ],
                200);
        }
       

   }  

    public function view()
    {
        header("Access-Control-Allow-Origin: *");

        $user_data = $this->_apiConfig([
            'methods' => ['POST'],
            'requireAuthorization' => true,
        ]);

        // return data
        $this->api_return(
            [
                "status" => true,
                "result" => [
                    'user_info' => $user_data['token_data']
                ],
            ],
        200);
    }
}

?>