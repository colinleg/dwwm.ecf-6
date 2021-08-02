<?php require 'inc/header.php' ?>
<?php require 'inc/topbar.php' ?>
<main>
  <div class="container">
    <h1 class="page-title">Categories</h1>
    <hr>
    

    <!-- ok verifie -->
    <?php foreach ($this->oCategories as $oCategorie): ?>
      <div class="row">
        
  			<div class="col s12 m12 l12">
  				<h4><?= $oCategorie->nomCategorie ?></h4>
          <?php $id = $oCategorie->id ?>
          <!-- en cours  -->
          <?php foreach ($this->oPosts as $oPost):?>
            <?php if($oPost->idCategorie == $id){
              
              echo "<a>$oPost->title</a><br>";
            } ?>
          
          <?php endforeach?>

  			</div>
  		</div>
    <?php endforeach ?>
  </div>
</main>
<?php require 'inc/footer.php' ?>