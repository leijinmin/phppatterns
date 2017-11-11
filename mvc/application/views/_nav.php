<nav class="navbar navbar-inverse">
<div class="container-fluid">
  <div class="navbar-header">
    <a class="navbar-brand" href="#">Jinmin's site</a>
  </div>
  <ul class="nav navbar-nav">
    <li class="active"><a href="<?=APP_CONTAINER?>home/Home/index">Accueil</a></li>
    <!-- <li><a href="views/index.php">Home</a></li> -->
    <?php 
    isset($_SESSION['role']) && in_array(ADMINISTRATOR,$_SESSION['role']) and print('<li><a href="admin.php">Administrateur</a></li>');
    isset($_SESSION['role']) && in_array(GUEST,$_SESSION['role']) and print('<li><a href="home.php">Home</a></li>');
    isset($_SESSION['role']) && in_array(AUDITOR,$_SESSION['role']) and print('<li><a href="audit.php">Auditeur</a></li>');
    isset($_SESSION['role']) && in_array(MODERATOR,$_SESSION['role']) and print('<li><a href="moderator.php">Modérateur</a></li>');
    
    ?>      
  </ul>
  <ul class="nav navbar-nav navbar-right">
    <?php
        isset($_SESSION['user']) and print('
        <li><a href="' . APP_CONTAINER . 'home/Home/logout"><span class="glyphicon glyphicon-log-in"></span> Log out</a></li>') 
        or print('
            <li><a href="' . APP_CONTAINER . 'home/Home/signin"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
            <li><a href="' . APP_CONTAINER . 'home/Home/login"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
            ');
        isset($_SESSION['role']) && in_array(ADMINISTRATOR,$_SESSION['role']) and print('
            <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Edit <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="#">Profil</a></li>
                <li><a href="#">Password</a></li>
            </ul>
        </li>');
           
    ?>     
  </ul>
</div>
</nav>

