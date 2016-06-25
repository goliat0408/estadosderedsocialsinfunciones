<?php
//Make a database connection
session_start();
mysql_connect("localhost", "root", "");
mysql_select_db("networksocial");

//Login section start
if(!isset($_SESSION["logged"])) 
{
  if(isset($_POST["username"]) && ($_POST['password'])) 
  
  {
    $query = mysql_query("SELECT id FROM site_members WHERE username = '" . $_POST["username"] . "' AND password = '" . $_POST["password"] . "'");
    if(mysql_num_rows($query) > 0) 
	{
      $row = mysql_fetch_array($query);
      $_SESSION["logged"] = $row["id"];
      header("Location: " . $_SERVER["PHP_SELF"]);
    }
  } 
  else 
  {
    echo("<form method=\"POST\">
    <input type=\"text\" name=\"username\" placeholder=\"Nombre\">
	<input type=\"password\" name=\"password\" placeholder=\"Contraseña\">
    <input type=\"submit\" name=\"submit\">  
    </form>");
  }
} 

else {
//end of login section

//Section for adding friend
  if(isset($_GET["add"])) {
    $query = mysql_query("SELECT id FROM site_members WHERE id = '" . $_GET["add"] . "'");
    if(mysql_num_rows($query) > 0) {
      $_query = mysql_query("SELECT * FROM friendship_requests WHERE sender = '" . $_SESSION["logged"] . "' AND recipient = '" . $_GET["add"] . "'");
      if(mysql_num_rows($_query) == 0) {
        mysql_query("INSERT INTO friendship_requests SET sender = '" . $_SESSION["logged"] . "', recipient = '" . $_GET["add"] . "'");
      }
    } 
  }
//END

//Section for exceting friendship requests
  if(isset($_GET["accept"])) {
    $query = mysql_query("SELECT * FROM friendship_requests WHERE sender = '" . $_GET["accept"] . "' AND recipient = '" . $_SESSION["logged"] . "'");
    if(mysql_num_rows($query) > 0) {
      
      $_query = mysql_query("SELECT * FROM site_members WHERE id = '" . $_GET["accept"] . "'");
      $_row = mysql_fetch_array($_query);
      
      $friends = unserialize($_row["friends"]);
      $friends[] = $_SESSION["logged"];      
                
      mysql_query("UPDATE site_members SET friends = '" . serialize($friends) . "' WHERE id = '" . $_GET["accept"] . "'");
      
      $_query = mysql_query("SELECT * FROM site_members WHERE id = '" . $_SESSION["logged"] . "'");
      $_row = mysql_fetch_array($_query);
      
      $friends = unserialize($_row["friends"]);
      $friends[] = $_GET["accept"];      
                
      mysql_query("UPDATE site_members SET friends = '" . serialize($friends) . "' WHERE id = '" . $_SESSION["logged"] . "'");
    }
    mysql_query("DELETE FROM friendship_requests WHERE sender = '" . $_GET["accept"] . "' AND recipient = '" . $_SESSION["logged"] . "'");
  }
//END

//Section for showing friendship requests
  $query = mysql_query("SELECT * FROM friendship_requests WHERE recipient = '" . $_SESSION["logged"] . "'");
  if(mysql_num_rows($query) > 0) {
    while($row = mysql_fetch_array($query)) { 
      $_query = mysql_query("SELECT * FROM site_members WHERE id = '" . $row["sender"] . "'");
      while($_row = mysql_fetch_array($_query)) {
        echo $_row["username"] . " wants to be your friend. <a href=\"" . $_SERVER["PHP_SELF"] . "?accept=" . $_row["id"] . "\">Accept?</a><br />";
      }
    }
  }
//END

//Section for showing members list
  echo "<h2>Member List:</h2>";
  $query = mysql_query("SELECT * FROM site_members WHERE id != '" . $_SESSION["logged"] . "'");
  while($row = mysql_fetch_array($query)) {
    $alreadyFriend = false;
    $friends = unserialize($row["friends"]);
    if(isset($friends[0])) {
      foreach($friends as $friend) {
        if($friend == $_SESSION["logged"]) $alreadyFriend = true;
      }
    }
    echo $row["username"];
    $_query = mysql_query("SELECT * FROM friendship_requests WHERE sender = '" . $_SESSION["logged"] . "' AND recipient = '" . $row["id"] . "'");
    if(mysql_num_rows($_query) > 0) {
       echo " - solicitud de amistad.";
    } elseif($alreadyFriend == false) {
       echo " - <a href=\"" . $_SERVER["PHP_SELF"] . "?add=" . $row["id"] . "\">Agregar como amigo</a>";
    } else {
      echo " - Ya amigos.";
    }
    echo "<br />";
  }
//END

//Friends list start
  echo "<h2>Lista amigos:</h2>";
  $query = mysql_query("SELECT friends FROM site_members WHERE id = '" . $_SESSION["logged"] . "'");
  while($row = mysql_fetch_array($query)) {
    $friends = unserialize($row["friends"]);
    
    if(isset($friends[0])) {
      foreach($friends as $friend) {
        $_query = mysql_query("SELECT username FROM site_members WHERE id = '" . $friend . "'");
        $_row = mysql_fetch_array($_query);
        echo $_row["username"] . "<br />";
      }
    }
  }
//END
}
?>