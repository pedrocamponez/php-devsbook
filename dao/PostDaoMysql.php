<?php
require_once 'models/Post.php';
require_once 'dao/UserRelationDaoMysql.php';
require_once 'dao/UserDaoMysql.php';

class PostDaoMysql implements PostDAO
{
    private $pdo;

    public function __construct(PDO $driver)
    {
        $this->pdo=$driver;
    }

    public function insert(Post $p)
    {
        $sql = $this->pdo->prepare('INSERT INTO posts (
            id_user, type, created_at, body
        ) VALUES (
            :id_user, :type, :created_at, :body
        )');

        $sql->bindValue(':id_user', $p->id_user);
        $sql->bindValue(':type', $p->type);
        $sql->bindValue(':created_at', $p->created_at);
        $sql->bindValue(':body', $p->body);
        $sql->execute();
    }

    public function getHomeFeed($id_user)
    {
        $array = [];
        //para inserir o feed na home, precisamos de:
        //1. feed das minhas postagens, e dos usuários que eu sigo (tabela userrelations)
        $urDao = new UserRelationDaoMysql($this->pdo);
        $userList = $urDao->getFollowing($id_user);
        $userList[] = $id_user;

        //2. pegar os posts ordenado pela data (pegar o array de usuários, implodir em uma virgula e separar aqui dentro)
        $sql = $this->pdo->query("SELECT * FROM posts 
        WHERE id_user IN (".implode(',', $userList).") 
        ORDER BY created_at DESC
        ");
        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);

            //3. transformar o resultado em objetos
            $array = $this->_postListToObject($data, $id_user);

        }

        return $array;
    }

    public function getPhotosFrom($id_user)
    {
        $array = [];

        $sql = $this->pdo->prepare("SELECT * FROM posts
        WHERE id_user = :id_user AND type = 'photo'
        ORDER BY created_at DESC
        ");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);
            $array = $this->_postListToObject($data, $id_user);

        }

        return $array;
    }

    public function getUserFeed($id_user)
    {
        $array = [];
        //1. pegar os posts ordenado pela data (pegar o array de usuários, implodir em uma virgula e separar aqui dentro)
        $sql = $this->pdo->prepare("SELECT * FROM posts 
        WHERE id_user = :id_user 
        ORDER BY created_at DESC
        ");
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();

        if($sql->rowCount() > 0){
            $data = $sql->fetchAll(PDO::FETCH_ASSOC);

            //1. transformar o resultado em objetos
            $array = $this->_postListToObject($data, $id_user);

        }

        return $array;
    }

    private function _postListToObject($post_list, $id_user) //função auxiliar pra transformar os dados em objeto
    {
        $posts = [];
        $userDao = new UserDaoMysql($this->pdo);

        foreach($post_list as $post_item) {
            $newPost = new Post();
            $newPost->id = $post_item['id'];
            $newPost->type = $post_item['type'];
            $newPost->created_at = $post_item['created_at'];
            $newPost->body = $post_item['body'];
            $newPost->mine = false;

            if($post_item['id_user'] == $id_user) { //se user q fez o post é o mesmo q ta logado, marca como post meu
                $newPost->mine = true;
            }

            //pegar info do usuário:
            $newPost->user = $userDao->findById($post_item['id_user']);

            //quantidade de likes e se eu dei like ou não naquele post específico
            $newPost->likeCount = 0;
            $newPost->liked = false;

            //informações sobre comentários aqui
            $newPost->comments = [];

            $posts[] = $newPost;
        }

        return $posts;
    }
}