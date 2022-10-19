<?php
require_once(dirname(dirname(__FILE__)) . '/dao/UserDaoMysql.php');

class Auth 
{
    //teremos um método para identificar se o usuário está logado ou não

    private $pdo;
    private $base;
    private $dao;

    public function __construct(PDO $pdo, $base) 
    {
        $this->pdo = $pdo;
        $this->base = $base;
        $this->dao = new UserDaoMysql($this->pdo); //manda o token para o banco de dados e verifica se existe
    }

    public function checkToken() 
    {
        if(!empty($_SESSION['token'])) { //verifica se tem a sessão com aquele token
            $token = $_SESSION['token'];

            $user = $this->dao->findByToken($token);

            if($user) {
                return $user; //se existir, retorna o usuário que está logado
            }
        }

        header("Location: ".$this->base."/login.php"); //redirecionando caso não tenha token com usuário vinculado
        exit;
    }

    public function validateLogin($email, $password) 
    {
        $user = $this->dao->findByEmail($email);
        if($user) {

            if(password_verify($password, $user->password)) {
                $token = md5(time().rand(0, 9999));

                $_SESSION['token'] = $token;
                $user->token = $token;
                $this->dao->update($user);

                return true;
            }
        }

        return false;
    }

    public function emailExists($email) 
    { //verificando se o e-mail existe
        return $this->dao->findByEmail($email) ? true : false;
    }

    public function registerUser($name, $email, $password, $birthdate) 
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = md5(time().rand(0, 9999));

        $newUser = new User(); //preenchendo o novo usuário
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->password = $hash;
        $newUser->birthdate = $birthdate;
        $newUser->token = $token;

        $this->dao->insert($newUser);

        $_SESSION['token'] = $token;
    }
}