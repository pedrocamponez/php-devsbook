<?php
require_once 'config.php';
require_once 'models/Auth.php';
require_once 'dao/PostDaoMysql.php';

$auth = new Auth($pdo, $base); //preciso passar pro Auth o $pdo pra acessar o banco de dados e a $base do config
$userInfo = $auth->checkToken(); //usando o Auth para verificar a sessão
$activeMenu = 'friends';
$user = [];
$feed = [];

//verificando se o id foi enviado
$id = filter_input(INPUT_GET, 'id');
if(!$id) {
    $id = $userInfo->id;
}

if($id != $userInfo->id) {
    $activeMenu = '';
}

$postDao = new PostDaoMysql($pdo);
$userDao = new UserDaoMysql($pdo);

// preciso pegar informações do usuário (logado ou enviado) e verificar se ele existe
$user = $userDao->findById($id, true);
if(!$user) {
    header("Location: ".$base);
    exit;
}

//calculando a idade do usuário para exibir
$dateFrom = new DateTime($user->birthdate);
$dateTo = new DateTime('today');
$user->ageYears = $dateFrom->diff($dateTo)->y;

//verificar se eu sigo esse usuário


require 'partials/header.php';
require 'partials/menu.php';
?>
<section class="feed">

    <div class="row">
        <div class="box flex-1 border-top-flat">
            <div class="box-body">
                <div class="profile-cover" style="background-image: url('<?=$base;?>/media/covers/<?=$user->cover;?>');"></div>
                <div class="profile-info m-20 row">
                    <div class="profile-info-avatar">
                        <img src="<?=$base;?>/media/avatars/<?=$user->avatar;?>" />
                    </div>
                    <div class="profile-info-name">
                        <div class="profile-info-name-text"><?=$user->name;?></div>
                        <?php if(!empty($user->city)): ?>
                            <div class="profile-info-location"><?=$user->city;?></div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info-data row">
                        <div class="profile-info-item m-width-20">
                            <div class="profile-info-item-n"><?=count($user->followers); ?></div>
                            <div class="profile-info-item-s">Seguidores</div>
                        </div>
                        <div class="profile-info-item m-width-20">
                            <div class="profile-info-item-n"><?=count($user->following); ?></div>
                            <div class="profile-info-item-s">Seguindo</div>
                        </div>
                        <div class="profile-info-item m-width-20">
                            <div class="profile-info-item-n"><?=count($user->photos); ?></div>
                            <div class="profile-info-item-s">Fotos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

    <div class="row">

<div class="column">
    
    <div class="box">
        <div class="box-body">

            <div class="tabs">
                <div class="tab-item" data-for="followers">
                    Seguidores
                </div>
                <div class="tab-item active" data-for="following">
                    Seguindo
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-body" data-item="followers">
                    
                    <div class="full-friend-list">

                        <?php foreach($user->followers as $item): ?>
                            <div class="friend-icon">
                                <a href="<?=$base;?>/perfil.php?id=<?=$item->id;?>">
                                    <div class="friend-icon-avatar">
                                        <img src="<?=$base;?>/media/avatars/<?=$item->avatar;?>" />
                                    </div>
                                    <div class="friend-icon-name">
                                        <?=$item->name;?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
                <div class="tab-body" data-item="following">
                    
                    <div class="full-friend-list">
                        <?php foreach($user->following as $item): ?>
                            <div class="friend-icon">
                                <a href="<?=$base;?>/perfil.php?id=<?=$item->id;?>">
                                    <div class="friend-icon-avatar">
                                        <img src="<?=$base;?>/media/avatars/<?=$item->avatar;?>" />
                                    </div>
                                    <div class="friend-icon-name">
                                        <?=$item->name;?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

</div>
    </div>
</section>
<?php
require 'partials/footer.php';
?>