<?php

session_start();

$mysql_host = "localhost";
$mysql_user = "root";
$mysql_pass = "";
$mysql_db = "testdatabase"; //mainigie, lai varetu savienoties ar datubāzi

$_SESSION['questionNum'] = 1;
$_SESSION['sessionID'] = 0;

readFormToDB($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //savienojas ar DB, ja Vārds ievadīts un ja lietotājs neeksitē tad izveido jaunu lietotāja ierakstu.
	
?>


<html>
<body>
<head>
<link rel="stylesheet" href="hello.css">
</head>

<div class="uzraksts">Please enter your name and choose a test to complete!</div> <!-- Izvada uzrakstu un piešķir tam klasi -->
<br>

<div id="parent">
<form id="form_login" method="POST" action="" accept-charset="utf-8" >    <!-- Izveido formu kura var ievadit testa veidu un vārdu -->
	
	<input placeholder="Name" id="firstname" type="text" name="firstname" />  <!-- Vārda ievade -->
	<br><br>
		
		<?php
			addElementsToSelection($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //ievieto testus izveles sadaljaaa
		?>
	
	<br><br>
    <input value ="Start Test!" type="submit" name="submitForm" /> <!-- formas iesniegšanas poga -->
	<br><br><br>
	
<div class="uzraksts">Good Luck!</div>
	
</form>
</div>

</body>
</html>


<?php //sadalja funkciju novietosahanai


function readFormToDB($mysql_host, $mysql_user, $mysql_pass, $mysql_db){   //savienojas ar DB/ja Vārds ievadīts un ja lietotājs neeksitē tad izveido jaunu lietotāja ierakstu.
	

	$conn = @mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //izveido savienoju ar DB
	
	if (isset($_POST['submitForm']))  //parbauda vai forma ir ievadita
	{
	$testType = $_POST['testType'];  //saglabā ievādītās vērtības no HTML formas
	$firstName = $_POST['firstname']; 
	$userFound=false; //mainigais kurš nosaka vai eksistē lietotājs, lai neveidotu duplikātus
	
	if($firstName != "") //izpilda darbibās ja ir ievādīts vārds, ja tas nav ievadīts  tad, parāda Kļūdas paziņojumu
	{
		$_SESSION['firstName'] = $firstName;
		$_SESSION['testType'] = $testType;
		$sql = "SELECT `user_name` FROM `user`"; //sql vaicājums - izvēlas kollonu 'user_name' no tabulas 'user'
		$result = mysqli_query($conn, $sql); //ievieto rezultātu '$result' masīvā, kas satur visus elementus

	
	if(mysqli_num_rows($result) > 0) //pārbauda vai ir atrasts vismaz viens ieraksts
	{
		while ($row=mysqli_fetch_assoc($result)) //kamēr masīvā ir elementi - izpildas cikls
		{
			if($row["user_name"] == $firstName) //pārbauda vai ievadītais Vārds sakrīt ar kādu no datubāzē eksistējošajiem
			{
				$userFound = true; // ja ekstē šis lietotājs tad mainīgajam tiek piešķirta TRUE vērtība	
			} //if beigas
			
		}//while beigas
	
		if($userFound==false) //pārbauda vai lieotājs netika atrasts
		{
			$sql = "INSERT INTO user (user_id, user_name) VALUES(NULL, '$firstName')"; //SQL vaicājums - Ievieto 'user' tabulā jaunu ierakstu
			mysqli_query($conn, $sql); //nosūta SQL vaicājumu uz datubāzi
		}//if beigas
	
		createNewSession($firstName,$testType,$mysql_host, $mysql_user, $mysql_pass, $mysql_db); //ievieto ierakstu, par jaunas sesijas sakumu

		echo '<script type="text/javascript"> window.location = "testPage.php" </script>'; //parnes uz testa pildišanas lapu
	
	}//if beigas	
	
	}else 
	{ //ja vārds netika ievadīts tad izavda kļūdas paziņojumu
		echo '<script>', 'alert("Ludzu ievadiet vārdu!");', '</script>';
	}//else beigas
	
	}//if formas parbaudes beigas
	
}//funkcijas beigas



function addElementsToSelection($mysql_host, $mysql_user, $mysql_pass, $mysql_db){ //funkcija, kas pievieno Testu veidus izvelnei no datubāzes
	
	$conn = @mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //izveido savienoju ar DB
			$sql = "SELECT `test_name` FROM `tests`"; //SQL vaicājums, izvēlas test_name ierakstus no DB
			$result = mysqli_query($conn, $sql); //ievieto rezultātu '$result' masīvā, kas satur visus elementus
			
		echo '<select name="testType">';
		
			while ($row=mysqli_fetch_assoc($result)) //cikls kas ievieto elementus no DB
			{
				echo '<option value="'.$row["test_name"].'">'.$row["test_name"].'</option>';	
			}//cikla beigas
			
		echo '</select>';
		
}//funckijas beigas


function createNewSession($firstName,$testType,$mysql_host, $mysql_user, $mysql_pass, $mysql_db){ //funkcija jaunas sesijas ierakstīšanai datubāzē
	
	$conn = @mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //izveido savienoju ar DB
	$sql = "INSERT INTO `sessions`(`session_id`, `user_name`, `test_name`) VALUES (null,'$firstName','$testType')"; //SQL vaicājums lai ievietotu sesijas tabulā ierakstu par sesiju
	
	mysqli_query($conn, $sql); //nosūta SQL vaicājumu uz datubāzi
	
	$sql = "SELECT session_id FROM sessions WHERE user_name = '$firstName' ORDER BY session_id DESC LIMIT 1"; //sql vaicājums lai iegutu, sesijas_id 
																											  //kurš tikko tikai izveidots datubāzē
																											  
	$result = mysqli_query($conn, $sql);//nosuta vaicajumu, saglaba atbildi
	$row=mysqli_fetch_assoc($result); //atbildi ieliek masīvā
	
	$_SESSION['sessionID'] = $row['session_id']; //sesijas ID saglabā, globalā mainīgajā
	
	
}



?>






