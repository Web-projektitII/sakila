<!DOCTYPE html>
<html lang="fi">

<?php
$tietokanta = "sakila";
$result = false;
include('tietokantarutiinit.php');
include('header.php');

function nayta($kentta){
echo isset($_POST[$kentta]) ? $_POST[$kentta] : ""; 
return;
}

function is_invalid($kentta){
  $virheet = $GLOBALS['virheet'];
  echo isset($virheet[$kentta]) ? "is-invalid" : ""; 
  return;
  }
   
function genre(){
$yhteys = db_connect();   
$query = "SELECT category_id,name FROM category ORDER BY name";
$result = $yhteys->query($query);
echo "<option value=''></option>\n";
while (list($category_id,$name) = $result->fetch_row()){
  echo "<option value='$category_id'>$name</option>\n";
  } 
}

function kielet(){
$yhteys = db_connect();
echo "<option value=''></option>\n";
$query = "SELECT language_id,name FROM language ORDER BY name";
$result = $yhteys->query($query);
if ($result !== false){
   while (list($id,$name) = $result->fetch_row()){
     if (isset($_POST['language_id']) and $_POST['language_id'] == $id)
        $selected = " selected";
     else $selected = "";
     echo "<option value=\"$id\"$selected>$name</option>\n";
     }
   }  
 }


function rating(){
   /* 'G','PG','PG-13','R','NC-17' */
   /* Huom. validointivirhe ei tulostu ilman lopun 
      ylimääräistä piilotettua radio button-elementtiä. */
   $yhteys = $GLOBALS['yhteys'];
   $str = "";
   $strArr = [];
   $query = "SHOW COLUMNS FROM film LIKE 'rating'";
   $result = $yhteys->query($query);
   if ($result !== false){
     $row = $result->fetch_assoc();
     $str = $row['Type'];
     preg_match('/enum\((.*)\)$/',$str,$matches);
     $strArr = explode(",",$matches[1]);
     }
   //echo "<ul class=\"list-group list-group-horizontal\">";
   foreach ($strArr AS $rating){
     $r = trim($rating,"'");
     $rating_set = (isset($_POST['rating']) and $r == $_POST['rating']);  
     $checked = ($rating_set) ? "checked" : "";
     echo "<div class=\"form-check form-check-inline\">
     <input class=\"form-check-input\" type=\"radio\" name=\"rating\" value=$rating $checked required>
     <label class=\"form-check-label\">$r</label>
     </div>";
     }	
   //echo "</ul>"; 
   }
   

function specialfeatures(){
   /* 'Trailers','Commentaries','Deleted Scenes','Behind the Scenes' */
   $yhteys = $GLOBALS['yhteys'];
   $strArr = [];
   $query = "SHOW COLUMNS FROM film LIKE 'special_features'";
   $result = $yhteys->query($query);
   if ($result !== false){
      $row = $result->fetch_assoc();
      $str = trim(substr($row['Type'],3),'()');
      $strArr = explode(",",$str);
      }
   foreach ($strArr AS $feature){
      $f = trim($feature,"'");
      $box_set = isset($_POST['special_features']);
      $feature_set = ($box_set and in_array($f,$_POST['special_features']));  
      $checked = ($feature_set) ? "checked" : "";
      echo "<div class=\"form-check\">
         <label class=\"form-check-label\">
         <input class=\"form-check-input\" type=\"checkbox\" name=\"special_features[]\" 
         value=$feature $checked>$f</label></div>";
        }	
      }
      

if (isset($_POST['painike'])){
/* nimi, kuvaus, julkaisuvuosi, kieli, vuokra-aika, vuokrahinta, pituus, korvaushinta, ikäraja, special features */
/* title, description, release_year, language_id, rental_duration, rental_rate, length, rating, special_features */ 
   $kentat = ['title','description','release_year','language_id','rental_duration','rental_rate','length','rating','special_features'];
   $pakolliset = ['title','description','language_id','rental_duration','rental_rate','rating'];
   $virheet = [];
   foreach ($kentat as $kentta) {
      $$kentta = $_POST[$kentta] ?? '';
      if (!is_array($$kentta)){
         //echo "$kentta:".$$kentta."<br>";
         $yhteys->real_escape_string(strip_tags(trim($$kentta)));
         }
      else {
         //echo "$kentta: ".implode(",",$$kentta);
         foreach ($$kentta as $value) {
            $yhteys->real_escape_string(strip_tags(trim($value)));
           }
         $$kentta = implode(",",$$kentta);  
        }
      if (!$$kentta && in_array($kentta,$pakolliset)) $virheet[$kentta] = true;
      }
  
   $str_kentat = implode(", ",$kentat);
   //echo "str_kentat:$str_kentat<br>";
   
   if (!$virheet) {
      $query = "INSERT INTO film ($str_kentat) VALUES ('$title','$description','$release_year',$language_id,$rental_duration,$rental_rate,'$length','$rating','$special_features')";
      //echo "$query<br>";
      //exit;
      $result = $yhteys->query($query);
      $lisattiin = $yhteys->affected_rows;
   }
    
  }
?>
<style>
th {text-align:left;}  
td {vertical-align:top;}    
</style>    
<div class="container">
<form method="post" novalidate class="needs-validation">
<fieldset>
<legend>Video lisääminen</legend>   

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Nimi</label>
<div class="col-sm-9">
<input id="title" name="title" class="form-control <?php is_invalid('title');?>" placeholder="Nimi" value="<?php nayta('title');?>" autofocus required></input>
<div class="invalid-feedback">Nimi puuttuu.</div>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Kuvaus</label>
<div class="col-sm-9">
<!--<textarea onkeyup="poista_is_invalid(this)" id="description" name="description" class="<?php is_invalid('description');?>" placeholder="Kuvaus"><?php nayta('description');?></textarea>-->
<textarea id="description" name="description" class="form-control <?php is_invalid('description');?>" placeholder="Kuvaus"><?php nayta('description');?></textarea>
<span class="invalid-feedback">Kuvaus puuttuu.</span>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Julkaisuvuosi</label>
<div class="col-sm-9">
<input required min="1901" max="2100" type="number" id="release_year" name="release_year" class="form-control w-auto <?php is_invalid('release_year');?>" placeholder="2019" value="<?php nayta('release_year');?>">
<div class="invalid-feedback">Julkaisuvuosi puuttuu.</div>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Kieli</label>
<div class="col-sm-9">
<select required id="language_id" name="language_id" class="form-select w-auto <?php is_invalid('language_id');?>" >\n
<?php echo kielet();?>
</select>
<div class="invalid-feedback">Valitse kieli.</div>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Vuokra-aika</label>
<div class="col-sm-9">
<input required min="1" max="7" type="number" id="rental_duration" name="rental_duration" class="form-control w-auto <?php is_invalid('rental_duration');?>" placeholder="7" value="<?php nayta('rental_duration');?>">
<div class="invalid-feedback">Vuokra-aika puuttuu.</div>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Vuokrahinta</label>
<div class="col-sm-9">
<input required min="1.00" max="100.00" type="number" step="0.10" id="rental_rate" name="rental_rate" class="form-control w-auto <?php is_invalid('rental_rate');?>" placeholder="5,00" value="<?php nayta('rental_rate');?>">
<div class="invalid-feedback">Vuokrahinta puuttuu.</div>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Ikäraja</label>
<div class="col-sm-9">
<?php rating();?>
<input class="d-none" type="radio" name="rating">
<div class="invalid-feedback">Valitse ikäraja.</div>
</div></div>

<div class="row mb-sm-1">
<label class="form-label mb-0 col-sm-3">Special features</label>
<div class="col-sm-9">
<?php specialfeatures();?>
</div></div>

<input type="submit" name="painike" class="btn btn-primary" value="Lisää">  
</fieldset>
</form>

<?php

if (isset($_POST['painike'])){
  echo '<div class="mt-3">';
  echo '<div class="alert alert-info" role="alert">';
  echo "Lomake on vastaanotettu.</div>";
  if ($virheet) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">";
    echo "Virheet:<br>";
    foreach ($virheet as $kentta => $arvo) echo "$kentta<br>";
    echo "</div>";
    }
  else {
    echo "<div class=\"alert alert-success\" role=\"alert\">";
    echo "Lisättiin: $lisattiin video<br></div>";   
    echo "<div class=\"alert alert-info\" role=\"alert\">";
    echo $query;
    echo "</div>";
  }
  echo "</div></div>";
  }
include('footer.html')
?>
</html>