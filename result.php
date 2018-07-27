<?php
session_start();

	$mysql_host = "localhost";
	$mysql_user = "root";
	$mysql_pass = "";
	$mysql_db = "testdatabase";
	$conn = @mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //izveido savienoju ar DB

	$sessionID = $_SESSION['sessionID'];
	$ammountOfQuestions = $_SESSION['ammountOfQuestions'];
	$firstName=$_SESSION['firstName'];
	//saglabā globālos mainīgos

	$count = countAmountOfCorrect($conn, $sessionID); //saglabā pareizo atbilžu skaitu
	
	$totalCorrectAnswers = getTotalAmmountofCorrectAnswers($conn, $firstName); //saglabā kopējo pareizo atbilžu skaitu
	$totalAmountOfAnswers = getTotalAmountOfAnswers($conn, $firstName); //iegūst kopējo atbildētu jautājumu skaitu
	$totalTestsTaken = totalAmountOfTestTaken($conn, $firstName); //iegūst kopēju izpildīto testu skaitu


?>



<html>
<body>
<head>
<link rel="stylesheet" href="css50.css">
</head>
<br>
<?php echo "Thank you, $firstName, for completing the test! <br> <br> You answered, $count out of $ammountOfQuestions questions correctly!"; //izvada to cik pareizi atbildeja no cik

echo "<br><br>";

echo "Total amount of correct answers: $totalCorrectAnswers out of $totalAmountOfAnswers, from $totalTestsTaken tests!"; //izvada kopējo rezultātu no visiem testiem

?> 

</body>
</html>


<?php

function countAmountOfCorrect($conn, $sessionID){ //funkcija lai saskaitītu, uz cik jautjumiem pareizi atbildējis

	//sql vaicājums lai iegūtu pareizo atbilžu skaitu, konkrētajai sesijai
	$sqlGetAmoountOfCorrect = "SELECT COUNT(correct) AS num FROM sessions_answers WHERE correct = '1' AND sessions_id = '$sessionID'";
	
	$result = mysqli_query($conn, $sqlGetAmoountOfCorrect);
	$row=mysqli_fetch_assoc($result); //nosuta vaicājumu, saglabā rezultātu masīvā
	
	$count = $row['num'];
	return $count; //atgriež kopējo pareizo atbilžu skaitu
	
}

function getTotalAmmountofCorrectAnswers($conn, $firstName){
	
	//sql vaicājums lai iegūtu kopējo pareizo atbilžu skaitu, no visiem izpildītajiem testiem, sekojošajam lietotājam.
	
	$sqlGetTotalCorrect = 
	"SELECT COUNT(*) AS num FROM sessions_answers 
	INNER JOIN sessions ON sessions.session_id=sessions_answers.sessions_id
	WHERE sessions.user_name='$firstName' AND sessions_answers.correct=1";
	
	$result = mysqli_query($conn, $sqlGetTotalCorrect); //nosūta vaicājumu, saglabā atbildi
	
	$row=mysqli_fetch_assoc($result); //atbildi ieliek masīvā
	$totalCorrectAnswers = $row['num'];
	return $totalCorrectAnswers; //atgriež kopējo pareizo atbilžu skaitu sekojošajam lietotājam
	
}

function getTotalAmountOfAnswers($conn, $firstName){ //funckija lai iegūtu kopējo atbilžu skaitu lietotājam
	
	//sql vaicājums lai iegūtu kopēju atbilžu skaitu
	$sqlGetTotalAmmount = 
	
	"SELECT COUNT(*) AS num FROM sessions_answers 
	INNER JOIN sessions ON sessions.session_id=sessions_answers.sessions_id
	WHERE sessions.user_name='$firstName'";
	
	$result = mysqli_query($conn, $sqlGetTotalAmmount); //nosūta vaicājumu, saglabā atbildi
	
	$row=mysqli_fetch_assoc($result);
	$totalAmmountOfAnswers = $row['num'];
	return $totalAmmountOfAnswers; //atgriež kopējo atbilžu saitu, sekojošajam lietotājam
	
	
}

function totalAmountOfTestTaken($conn, $firstName){ //funkcija lai iegūtu kopējo pildīto testu skaitu
	
		//SQL vaicājums, lai iegūtu kopējo pildīto testu skaitu, sekojošajam lietotājam
		$sqlGetTotalTestsTaken = "SELECT COUNT(session_id) AS num FROM sessions WHERE user_name='$firstName'";
		
		
		$result = mysqli_query($conn, $sqlGetTotalTestsTaken); //nosūta vaicājumu, atbildi saglabā
	
		$row=mysqli_fetch_assoc($result);
		$totalTestsTaken = $row['num'];
		return $totalTestsTaken; //atgriež kopējo veikto testu skaitu
	
}




?>