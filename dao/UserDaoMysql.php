<?php
require_once (dirname(dirname(__FILE__)) . '/models/User.php');
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/PostDaoMysql.php';

class UserDaoMysql implements UserDAO 
{
    private $pdo;

    public function __construct(PDO $driver) 
    {
        $this->pdo = $driver;
    }

    private function generateUser($array, $full = false) 
    { //função para montar um usuário e retornar os dados desse usuário
        $u = new User();
        $u->id = $array['id'] ?? 0;
        $u->email = $array['email'] ?? '';
        $u->password = $array['password'] ?? '';
        $u->name = $array['name'] ?? '';
        $u->birthdate = $array['birthdate'] ?? '';
        $u->city = $array['city'] ?? '';
        $u->work = $array['work'] ?? '';
        $u->avatar = $array['avatar'] ?? '';
        $u->cover = $array['cover'] ?? '';
        $u->token = $array['token'] ?? '';

        if($full) {
            $urDaoMysql = new UserRelationDaoMysql($this->pdo);
            $postDaoMySql = new PostDaoMysql($this->pdo);

            //Quem segue o usuário
            $u->followers = $urDaoMysql->getFollowers($u->id);
            foreach($u->followers as $key => $follower_id) {
                $newUser = $this->findById($follower_id);
                $u->followers[$key] = $newUser;
            }

            //quem o usuário segue
            $u->following = $urDaoMysql->getFollowing($u->id);
            foreach($u->following as $key => $follower_id) {
                $newUser = $this->findById($follower_id);
                $u->following[$key] = $newUser;
            }

            //fotos
            $u->photos = $postDaoMySql->getPhotosFrom($u->id);
        }

        return $u;
    }

    public function findByToken($token) 
    { //manda o token
        if(!empty($token)) { //verifica o token
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE token = :token");
            $sql->bindValue(':token', $token);
            $sql->execute();

            if($sql->rowCount() > 0) { //se o token for ok, monta o objeto e retorna ele
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data); //aqui vai ser o usuário que foi criado
                return $user;
            }
        }
        
        return false;
    }

    public function findByEmail($email) 
    { //manda o token
        if(!empty($email)) { //verifica o token
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
            $sql->bindValue(':email', $email);
            $sql->execute();

            if($sql->rowCount() > 0) { //se o token for ok, monta o objeto e retorna ele
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data); //aqui vai ser o usuário que foi criado
                return $user;
            }
        }
        
        return false;
    }

    public function findById($id, $full = false) //$full para pegar as informações completas do usuário, se existirem
    {
        if(!empty($id)) { //verifica o token
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $sql->bindValue(':id', $id);
            $sql->execute();

            if($sql->rowCount() > 0) { //se o token for ok, monta o objeto e retorna ele
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                $user = $this->generateUser($data, $full); //aqui vai ser o usuário que foi criado e as informações completas
                return $user;
            }
        }
    }

    public function findByName($name) 
    {
        $array = [];

        if(!empty($name)) {
            $sql = $this->pdo->prepare("SELECT * FROM users WHERE name LIKE :name");
            $sql->bindValue(':name', '%'.$name.'%');
            $sql->execute();

            if($sql->rowCount() > 0) {
                $data = $sql->fetchAll(PDO::FETCH_ASSOC);

                foreach($data as $item) {
                    $array[] = $this->generateUser($item);
                }
            }
        }

        return $array;
    }

    public function update(User $u) 
    {
        $sql = $this->pdo->prepare("UPDATE users SET 
            email = :email,
            password = :password,
            name = :name,
            birthdate = :birthdate,
            city = :city,
            work = :work,
            avatar = :avatar,
            cover = :cover,
            token = :token
            WHERE id = :id");
        $sql->bindValue('email', $u->email);
        $sql->bindValue('password', $u->password);
        $sql->bindValue('name', $u->name);
        $sql->bindValue('birthdate', $u->birthdate);
        $sql->bindValue('city', $u->city);
        $sql->bindValue('work', $u->work);
        $sql->bindValue('avatar', $u->avatar);
        $sql->bindValue('cover', $u->cover);
        $sql->bindValue('token', $u->token);
        $sql->bindValue('id', $u->id);
        $sql->execute();

        return true;
    }

    public function insert(User $u) 
    {
        $sql = $this->pdo->prepare("INSERT INTO users (
            email, password, name, birthdate, token
        ) VALUES (
            :email, :password, :name, :birthdate, :token
        )");
        // $query = <<<SQL
        //     INSERT INTO users (email, password, name, birthdate, token)
        //     VALUES (:email, :password, :name, :birthdate, :token)
        // SQL;

        // $sql = $this->pdo->prepare($sql);

        $sql->bindValue(':email', $u->email);
        $sql->bindValue(':password', $u->password);
        $sql->bindValue(':name', $u->name);
        $sql->bindValue(':birthdate', $u->birthdate);
        $sql->bindValue(':token', $u->token);
        $sql->execute();
        
        return true;
    }
}