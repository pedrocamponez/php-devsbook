<?php
require 'config.php';
require 'models/Auth.php';
require 'dao/PostDaoMysql.php';

$auth = new Auth($pdo, $base); //preciso passar pro Auth o $pdo pra acessar o banco de dados e a $base do config
$userInfo = $auth->checkToken(); //usando o Auth para verificar a sessão

$body = filter_input(INPUT_POST, 'body'); //pega o body - texto enviado

if($body) { //verifica se o body existe
    $postDao = new PostDaoMysql($pdo); //pego meu Dao

    $newPost = new Post(); //crio meu post novo com o conteúdo body
    $newPost->id_user = $userInfo->id;
    $newPost->type = 'text';
    $newPost->created_at = date('Y-m-d H:i:s');
    $newPost->body = $body;

    $postDao->insert($newPost); //insiro meu post no mysql
}

header("Location: ".$base);
exit;
?>