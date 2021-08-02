<?php

namespace BlogPhp\Model;

class Admin extends Blog
{

  /* ========== SELECT ========== */


  public function inTable($sTable)
  {
    $oStmt = $this->oDb->query("SELECT COUNT(id) FROM $sTable");
    return $oStmt->fetch();
  }


  public function getCommentsUnseen()
  {
    $oStmt = $this->oDb->query("
      SELECT  Comments.id,
              Comments.user_id,
              Comments.comment,
              Comments.post_id,
              Comments.date,
              Comments.signals,
              Posts.title,
              Users.pseudo
      FROM Comments
      JOIN Posts
      ON Comments.post_id = Posts.id
      JOIN Users
      ON Comments.user_id = Users.id
      WHERE Comments.seen = '0'
      AND Comments.signals = '0'
      ORDER BY Comments.date ASC
    ");

    $results = [];

    while($rows = $oStmt->fetchObject())
    {
      $results[] = $rows;
    }
    return $results;
  }


  public function getSignaledComments()
  {
    $oStmt = $this->oDb->query("
      SELECT  Comments.id,
              Comments.user_id,
              Comments.comment,
              Comments.post_id,
              Comments.date,
              Comments.signals,
              Posts.title,
              Users.pseudo
      FROM Comments
      JOIN Posts
      ON Comments.post_id = Posts.id
      JOIN Users
      ON Comments.user_id = Users.id
      WHERE Comments.seen = '0'
      AND Comments.signals > '0'
      ORDER BY Comments.signals
    ");

    $results = [];

    while($rows = $oStmt->fetchObject())
    {
      $results[] = $rows;
    }
    return $results;
  }


  public function getNbrSignals()
  {
    $oStmt = $this->oDb->query("SELECT COUNT(id) FROM Votes");
    return $oStmt->fetch();
  }

  /**
   * Perso 1 : Ajouter des catégories 
   * Fonction permettant de retourner toutes les catégories déja présentes 
   * retourne l'id de la catégorie si elle existe 
  */

  public function existCategorie($cat){
    $oStmt = $this->oDb->prepare("SELECT id FROM Categorie WHERE nomCategorie = :categorie");
    $oStmt->bindParam(':categorie', $cat);
    $oStmt->execute();
    $data = $oStmt->fetch();
    return $data[0];
  }
    


  /* ========== UPDATE ========== */


    public function update(array $aData)
    {
      $oStmt = $this->oDb->prepare('UPDATE Posts SET title = :title, body = :body WHERE id = :postId LIMIT 1');
      $oStmt->bindValue(':postId', $aData['post_id'], \PDO::PARAM_INT);
      $oStmt->bindValue(':title', $aData['title'], \PDO::PARAM_STR);
      $oStmt->bindValue(':body', $aData['body'], \PDO::PARAM_LOB);
      return $oStmt->execute();
    }


    public function updateImg($name, $id, $tmp_name)
    {
      $i = [
        'id'     => $id,
        'image'  => $name
      ];

      $oStmt = $this->oDb->prepare('UPDATE Posts SET image = :image WHERE id = :id');
      move_uploaded_file($tmp_name,"static/img/posts/".$i['image']);
      return $oStmt->execute($i);
    }


    public function postImg($tmp_name, $extension)
    {
      $i = [
        'id'     => $this->oDb->lastInsertId(),
        'image'  => $this->oDb->lastInsertId().$extension
      ];

      $oStmt = $this->oDb->prepare('UPDATE Posts SET image = :image WHERE id = :id');
      move_uploaded_file($tmp_name,"static/img/posts/".$i['image']);
      return $oStmt->execute($i);
    }


    public function see_comment()
    {
      $oStmt = $this->oDb->exec("UPDATE Comments SET seen = '1' WHERE id='{$_POST['id']}'");
      $oStmt = $this->oDb->exec("DELETE FROM Votes WHERE comment_id = {$_POST['id']}");
      $oStmt = $this->oDb->exec("UPDATE Comments SET signals = '0' WHERE id='{$_POST['id']}'");
    }


    /* ========== DELETE ========== */


    public function delete($iId)
    {
      $oStmt = $this->oDb->prepare('DELETE FROM Posts WHERE id = :postId LIMIT 1');
      $oStmt->bindParam(':postId', $iId, \PDO::PARAM_INT);
      return $oStmt->execute();
    }


    public function deleteComments($iId){
      $oStmt = $this->oDb->prepare('DELETE FROM Comments WHERE post_id = :postId');
      $oStmt->bindParam(':postId', $iId, \PDO::PARAM_INT);
      return $oStmt->execute();
    }


    public function deleteVotes($iId){
      $oStmt = $this->oDb->prepare('DELETE FROM Votes WHERE post_id = :postId');
      $oStmt->bindParam(':postId', $iId, \PDO::PARAM_INT);
      return $oStmt->execute();
    }


    public function deleteComment($iId)
    {
      $oStmt = $this->oDb->prepare('DELETE FROM Comments WHERE id = :id');
      $oStmt->bindParam(':id', $iId, \PDO::PARAM_INT);
      return $oStmt->execute();
    }


    public function deleteVote($iId)
    {
      $oStmt = $this->oDb->prepare('DELETE FROM Votes WHERE comment_id = :comment_id');
      $oStmt->bindParam(':comment_id', $iId, \PDO::PARAM_INT);
      return $oStmt->execute();
    }


    public function delete_comment()
    {
      $oStmt = $this->oDb->exec("DELETE FROM Comments WHERE id = {$_POST['id']}");
      $oStmt = $this->oDb->exec("DELETE FROM Votes WHERE comment_id = {$_POST['id']}");
    }


    /* ========== INSERT ========== */


    // public function add(array $aData)
    // {
    //   $oStmt = $this->oDb->prepare('INSERT INTO Posts (title, body, createdDate) VALUES(:title, :body, :createdDate)'); // l'erreur était ici !! createdDate et non created_date
    //   $oStmt->bindValue(':title', $aData['title'], \PDO::PARAM_STR);
    //   $oStmt->bindValue(':body', $aData['body'], \PDO::PARAM_LOB);
    //   $oStmt->bindValue(':createdDate', $aData['created_date'], \PDO::PARAM_STR);
    //   return $oStmt->execute();
    // }


    /**
     * Perso 1 - A : Ajout de la catégorie, qui est une string 
     */

    // public function add(array $aData){
    //   $oStmt = $this->oDb->prepare('INSERT INTO Posts (title, categorie, body, createdDate) VALUES(:title, :categorie, :body, :createdDate)'); 
    //   $oStmt->bindValue(':title', $aData['title'], \PDO::PARAM_STR);
    //   // $oStmt->bindValue(':categorie', $aData['categorie'], \PDO::PARAM_STR);
    //   $oStmt->bindValue(':body', $aData['body'], \PDO::PARAM_LOB);
    //   $oStmt->bindValue(':createdDate', $aData['created_date'], \PDO::PARAM_STR);
    //   return $oStmt->execute();
    // }

    /**
     * Perso 1 - B : Ajout de la catégorie, qui est une string 
     *  EDIT : maintenant je ne renseigne plus un champ de categorie dans table Posts
     *  mais je crée si n'existe pas déja, une ligne dans la table catégorie 
     */

    public function add(array $aData){
      $oStmt = $this->oDb->prepare('INSERT INTO Posts (title, idCategorie, body, createdDate) VALUES(:title, :idCategorie, :body, :createdDate)');

      $cat = $aData['categorie'];
      // si est différent de NULL, idCat est l'id (prim_key) de la categori
      $idCat = $this->existCategorie($cat);
      
      // Si la categorie n'existe pas, je la crée, puis idCat prend la valeur de l'id qui vient d'etre crée
      if($idCat == NULL ){
        $this->addCategorie($cat);
        $idCat = $this->existCategorie($cat);
        $oStmt->bindValue(':idCategorie', $idCat, \PDO::PARAM_INT);
      }
      // sinon j'envoie l'id dans idCategorie de table Post
      else {
        $oStmt->bindValue(':idCategorie', $idCat, \PDO::PARAM_INT);
      }
      
      $oStmt->bindValue(':title', $aData['title'], \PDO::PARAM_STR);
      $oStmt->bindValue(':body', $aData['body'], \PDO::PARAM_LOB);
      $oStmt->bindValue(':createdDate', $aData['created_date'], \PDO::PARAM_STR);
      return $oStmt->execute();
    }

    /**
     * Création d'une fonction qui ajoute une ligne dans la table 'Categorie', rentrant le nom de la catégorie.
     * Ok fonctionnnelle, vérifiée
     */
    public function addCategorie($categorie){
      $oStmt = $this->oDb->prepare('INSERT INTO Categorie (nomCategorie) VALUES(:categorie)');
      $oStmt->bindParam(':categorie', $categorie);
      return $oStmt->execute();
    }

}
