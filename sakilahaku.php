<!DOCTYPE html>
<html lang="fi">

<?php
$tietokanta = "sakila";
$result = false;
include('tietokantarutiinit.php');
include('header.php');

function genre(){
$yhteys = db_connect();   
$query = "SELECT category_id,name FROM category ORDER BY name";
$result = $yhteys->query($query);
echo "<option value=''></option>\n";
while (list($category_id,$name) = $result->fetch_row()){
  echo "<option value='$category_id'>$name</option>\n";
  } 
}

if (isset($_GET['painike'])){
    $title = $_GET['title'] ?? '';
    $category_id = $_GET['category_id'] ?? '';
    if ($title or $category_id){
    $title = $yhteys->real_escape_string(strip_tags(trim($title)));
    $category_id = $yhteys->real_escape_string(strip_tags(trim($category_id)));
    //nimi, kuvaus, ikäraja ja julkaisuvuosi.
    $category_query = ($category_id) ? "category_id = $category_id" : 1;
    $query = "SELECT title,description,release_year,rating FROM film f LEFT JOIN film_category fc USING(film_id) WHERE $category_query AND title LIKE '$title%' ORDER BY title";
    $result = $yhteys->query($query);
    /*if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
           echo "Nimi: " . $row["title"]. ",kuvaus: " . $row["release_year"]. ", rating: " . $row["rating"]. "<br>";
         }
     } else {
        echo "Ei hakutuloksia";
     }*/
   } 
  }
?>
<style>
th {text-align:left;}  
td {vertical-align:top;}    
</style>    
<div class="container">
<h1>HAKU</h1> 
<form novalidate>
<label>Genre</label>
<select id="category_id" name="category_id"><?php genre();?></select> 
<label>Nimi</label>
<input id="title" name="title" placeholder="Nimi" autofocus></input>
<input type="submit" name="painike" value="Hae">    
</form>
</div>
<?php

if (isset($_GET['painike']) and $result){
echo "<p>";
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Nimi</th><th>Kuvaus</th><th>Julkaisuvuosi</th><th>Ikäraja</th></tr>";
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       echo "<tr><td>" . $row["title"]. "</td><td>" . $row["description"]. "</td><td>". $row["release_year"] . "</td><td>" . $row["rating"]. "</td></tr>";
       }  
    echo "</table>";
    } 
 else {
    echo "Ei hakutuloksia";
 }
echo "</p>";
}
include('footer.html')
?>
</html>