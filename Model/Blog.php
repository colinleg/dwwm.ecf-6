<?php

namespace BlogPhp\Model;

class Blog
{
  protected $oDb;

  public function __construct()
  {
    $this->oDb = new \BlogPhp\Engine\Db;
  }


  /* ========== SELECT ========== */

  // le nombre d'article qui seront affichés sur la page d'accueil
  public function get($iOffset, $iLimit)
  {
    //edité
    $oStmt = $this->oDb->prepare('SELECT * FROM Posts ORDER BY createdDate DESC LIMIT :offset, :limit');
    $oStmt->bindParam(':offset', $iOffset, \PDO::PARAM_INT);
    $oStmt->bindParam(':limit', $iLimit, \PDO::PARAM_INT);
    $oStmt->execute();
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
  }


  public function getById($iId)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Posts WHERE id = :postId LIMIT 1');
    $oStmt->bindParam(':postId', $iId, \PDO::PARAM_INT);
    $oStmt->execute();
    return $oStmt->fetch(\PDO::FETCH_OBJ);
  }


	public function getComments()
	{
		$oStmt = $this->oDb->query("
    SELECT Users.id,
           Comments.user_id,
           Comments.comment,
           Comments.post_id,
           Comments.date,
           Comments.signals,
           Users.pseudo,
           Comments.id
    FROM Comments
    JOIN Users
    ON Comments.user_id = Users.id
    WHERE post_id = '{$_GET['id']}'
    ORDER BY date DESC
       ");
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
	}


  public function getAll()
  {
    $oStmt = $this->oDb->query('SELECT * FROM Posts ORDER BY createdDate DESC');
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
  }

  // Perso1 : Ajout de categories 

  
  public function getCategories(){
    $oStmt = $this->oDb->query('SELECT * FROM Categorie ORDER BY nomCategorie');
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
  }

  public function getPostsByIdCat(){
    $oStmt = $this->oDb->query('SELECT * FROM Categorie INNER JOIN Posts ON Categorie.id = Posts.idCategorie ORDER BY idCategorie');
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
  }

  /**
   * On recupere un tableau d'ids : aIds
   * on modifie la requete jusqu'à ce qu'elle contiennent toutes les ids à afficher 
   * on effectue et renvoie la requete qui affichera les posts et nom de catégorie de chaque 
   * post qui a une catégorie
   */
  public function getCatById(array $aIds){

    $req = 'SELECT Posts.idCategorie, Posts.title, Categorie.id, Categorie.nomCategorie FROM Posts INNER JOIN Categorie ON Posts.idCategorie = Categorie.id';


    $oStmt = $this->oDb->prepare($req);

    $oStmt->execute();
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
  }
  // Fin Perso


  public function isAdmin($sEmail)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Users WHERE email = :email LIMIT 1');
    $oStmt->bindValue(':email', $sEmail, \PDO::PARAM_STR);
    $oStmt->execute();
    return $oStmt->fetch(\PDO::FETCH_OBJ);
  }


  public function login($sEmail, $sPassword)
  {
    $a = [
      'email' 	  => $sEmail,
      'password' 	=> sha1($sPassword)
    ];
    $sSql = "SELECT * FROM Users WHERE email = :email AND password = :password";
    $oStmt = $this->oDb->prepare($sSql);
    $oStmt->execute($a);
    $exist = $oStmt->rowCount($sSql);

    return $exist;
  }


  public function signalExist($aData)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Votes WHERE comment_id = :comment_id AND user_id = :user_id');
    $oStmt->bindValue(':comment_id', $aData['comment_id'], \PDO::PARAM_INT);
    $oStmt->bindValue(':user_id', $aData['user_id'], \PDO::PARAM_STR);
    $oStmt->execute();
    return $oStmt->rowCount();
  }


  public function userVotes($user)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Votes WHERE user_id = :user_id');
    $oStmt->bindValue(':user_id', $user, \PDO::PARAM_STR);
    $oStmt->execute();
    return $oStmt->fetchAll(\PDO::FETCH_OBJ);
  }


  public function pseudoTaken($pseudo)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Users WHERE pseudo = :pseudo');
    $oStmt->bindParam(':pseudo', $pseudo, \PDO::PARAM_STR);
    $oStmt->execute();
    return $oStmt->rowCount();
  }


  public function emailTaken($sEmail)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Users WHERE email = :email');
    $oStmt->bindParam(':email', $sEmail, \PDO::PARAM_STR);
    $oStmt->execute();
    return $oStmt->rowCount();
  }


  public function getUserId($userId)
  {
    $oStmt = $this->oDb->prepare('SELECT id FROM Users WHERE pseudo = :pseudo');
    $oStmt->bindParam(':pseudo', $userId, \PDO::PARAM_STR);
    $oStmt->execute();
    return $oStmt->fetch(\PDO::FETCH_OBJ);
  }


  /* ========== INSERT ========== */


	public function addComment(array $aData)
	{
		$oStmt = $this->oDb->prepare('INSERT INTO Comments (user_id, comment, post_id, date) VALUES(:user_id, :comment, :post_id, NOW())');
    return $oStmt->execute($aData);
	}


  public function addUser($aData)
  {
    $oStmt = $this->oDb->prepare('INSERT INTO Users (email, pseudo, password) VALUES(:email, :pseudo, :password)');
    return $oStmt->execute($aData);
  }


  public function signalComment($aData)
  {
    $oStmt = $this->oDb->prepare('SELECT * FROM Comments WHERE id = :comment_id');
    $oStmt->bindValue(':comment_id', $aData['comment_id'], \PDO::PARAM_INT);
    $oStmt->execute();

    if ($oStmt->rowCount() > 0)
    {
      $oStmt = $this->oDb->prepare('INSERT INTO Votes (comment_id, user_id, post_id, vote) VALUES(:comment_id, :user_id, :post_id, 1) ');
      $oStmt->execute($aData);
      return true;
    }
    else
    {
      throw new \Exception("Impossible de voter pour un commentaire qui n'existe pas");

    }
  }


  /* ========== UPDATE ========== */


  public function substrSignal($id)
  {
    $oStmt = $this->oDb->exec("UPDATE Comments SET signals = signals - '1' WHERE id='$id'");
  }


  public function addSignal($id)
  {
    $oStmt = $this->oDb->exec("UPDATE Comments SET signals = signals + '1' WHERE id='$id'");
  }


  public function setUnseen($id)
  {
    $oStmt = $this->oDb->exec("UPDATE Comments SET seen = '0' WHERE id='$id'");
  }


  /* ========== DELETE ========== */


  public function deleteUserVote($aData)
  {
    $oStmt = $this->oDb->prepare('DELETE FROM Votes WHERE comment_id = :comment_id AND user_id = :user_id');
    $oStmt->bindParam(':comment_id', $aData['comment_id'], \PDO::PARAM_INT);
    $oStmt->bindParam(':user_id', $aData['user_id'], \PDO::PARAM_STR);
    return $oStmt->execute();
  }


}
