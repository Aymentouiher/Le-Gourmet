<header data-bs-theme="dark">
  <nav class="navbar navbar-expand-md navbar-dark fixed-top" style="background-color: #060834;">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">Le Gourmet</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" 
      aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav me-auto mb-2 mb-md-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="Reservation.php">Reservation</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="Menu.php">Menu</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="galerie.php">galerie</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="contact.php">Contact</a>
          </li>
        </ul>
        <?php
        if(isset($_SESSION['nom'])){?>
        <a href="" type="button" class="btn btn-outline-primary m-2">Bonjour <?php echo $_SESSION['nom'];  ?></a>
        <a href="logout.php" type="button" class="btn btn-outline-primary">Se déconnecter</a>
        <?php }else{ ?>
        <a href="http://"></a>        
        <div class="d-flex">
          <a href="signup.php" type="button" class="btn btn-outline-primary mx-2" style="color: white;">s'inscrire</a>
          <a href="signin.php" type="button" class="btn btn-outline-primary" style="color: white;">se connecter</a>
        </div>
        <?php } ?>
      </div>
    </div>
  </nav>
</header>