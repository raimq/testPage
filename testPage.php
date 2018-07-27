<?php
	session_start();
	
	$mysql_host = "localhost";
	$mysql_user = "root";
	$mysql_pass = "";
	$mysql_db = "testdatabase";
	//mainigie savienojuma izveidei ar datubāzi
	
	$sessionID = $_SESSION['sessionID'];
	$firstName = $_SESSION['firstName'];
	$testType = $_SESSION['testType'];
	$questionNum = $_SESSION['questionNum'];
	//globalie mainigie
	
	$conn = @mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db); //izveido savienoju ar DB

	getAmmountOfQuestions($testType,$conn); //iegust jautajumu skaitu testā
	
?>


<html>
<body>
<head>
<link rel="stylesheet" href="questions.css">
</head>

<div class="uzraksts">
	<?php
		getQuestion($questionNum,$testType,$conn); //izvada jautajumu
	?>
</div>

<div id="myProgress">
	<?php
	$percent = (($_SESSION['questionNum']/$_SESSION['ammountOfQuestions'])*100);	//iegust % vertibu lai varetu aizpildīt progresa logu
	echo '<div id="myBar" style="width:'.$percent.'%"></div>'; //izvada vertibu uz HTML
	?>
</div>


<div id=buttons>

<form method="post">

<?php
displayAnswers($questionNum,$testType,$conn);	//izvada atbildes uz HTML
?>

</form>

</div>


<?php

    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['submitValue'])) //parbauda vai nospiesta atbildes poga
		
    {
		$submitValue = $_POST['submitValue'];	//saglabā nospiesto vērtību mainīgajā
		saveAnswers($questionNum,$testType,$submitValue,$conn,$sessionID);	//saglabā atbildi MSQL
        nextQuestion(); //Pārlādē lapu nākamā jautājuma izvadei
		
		if($_SESSION['questionNum']>$_SESSION['ammountOfQuestions']) //ja vairs nav jautājumu ko rādīt, pārmet uz rezultātu logu
		{
			header('Location: result.php');
		}
		
    }

?>




</body>
</html>


<?php
function getQuestion($questionNum,$testType,$conn){ //funkcija jautājuma iegūšanai no SQL
	
	//sql vaicājums iegūst jautājumu, atbilstoši jautājuma numuram, un testa veidam
	
	$sqlGetQuestions = "SELECT `Question` FROM `questions` WHERE test_name='$testType' AND question_num='$questionNum'"; 
	
	$result = mysqli_query($conn, $sqlGetQuestions); //nosūta vaicājumu uz SQL un saglabā atbildi
	$row=mysqli_fetch_assoc($result); //ieliek atbildi masīvā
	echo $row['Question']; //izvada jautājumu
	
}



function displayAnswers ($questionNum,$testType,$conn){ //funckija atbilžu rādīšanai

	//sql vaicājums iegūst atbildes atgarībā, no testa veida un jautājuma numura
	$sqlGetQuestions = "SELECT `answer` FROM `answers` WHERE test_name ='$testType' AND question_num = '$questionNum'"; 
	
	$result = mysqli_query($conn, $sqlGetQuestions);  //nosuta vaicājumu saglabā atbildi
	
	$temp=1; //mainīgais lai palīdzētu sadalīt atbilžu pogas pa divi
	
	while ($row=mysqli_fetch_assoc($result)){ //cikls ģenerē pogas, kamēr vien ir atbildes masīvā

		echo '<input type="submit" name="submitValue" value="'.$row["answer"].'"/>'; //izvada pogas kodu uz HTML
			
			if($temp %2 == 0) //parbauda vai nepieciešams line brake
			{
			echo '<br>';
			}
			$temp+=1; //palielina mainigo
	}
	
	


	
	
	
}

function getAmmountOfQuestions($testType,$conn){ //iegust jautājumu skaitu
	
	//SQL vaicājums lai iegūtu skaitu ar to cik jautājumu ir testā
	$sqlGetQuestions = "SELECT COUNT(question_id) AS num FROM questions WHERE test_name = '$testType'";
	
	$result = mysqli_query($conn, $sqlGetQuestions); //nosūta vaicājumu, saglabā atbildi
	$row=mysqli_fetch_assoc($result); //atbildi ieliek masīvā
	$count = $row['num']; //nolasa skaitu
	$_SESSION['ammountOfQuestions']=$count; //jautājumu skaitu testā saglabā globālā mainīgajā
	
}


function nextQuestion(){	//funkcija, lai iegūtu atbildi, palielinātu jautājuma NR un pārlādētu lapu.
	$answerValue=$_POST['submitValue'];		
	$_SESSION['questionNum']+=1;
	
	header("Refresh:0");
}


function saveAnswers($questionNum,$testType,$submitValue,$conn,$sessionID){//funckija lai saglabātu atbildi MQSQL

	//sql vaicājums, iegūst to vai atbilde ir pareizi, konkrētjam testam/jautājumam/atbildei
	$sqlGetCorrect = "SELECT `correct` FROM `answers` WHERE question_num=$questionNum AND test_name= '$testType' AND answer = '$submitValue'";
	
	$result = mysqli_query($conn, $sqlGetCorrect); //nosūta vaicājumu, saglabā atbildi
	$row=mysqli_fetch_assoc($result);  //atbildi ieliek masīvā

	$correct = $row['correct']; //vērtibu saglabā mainīgajā
	
	//sql vaicājums - veic ierakstu par atbildi sesijas atbilžu masīvā - saglabā sesijasID, jautājumaNR, Atbildes vertibu, un to vai atbilde ir pareiza.
	$sqlSaveAnswer= "INSERT INTO `sessions_answers` (`sessions_answer_id`, `sessions_id`, `question_id`, `answer`, `correct`) VALUES (NULL, '$sessionID', '$questionNum', '$submitValue', '$correct')";
	
	mysqli_query($conn, $sqlSaveAnswer); //nosuta vaicājumu uz SQL
	
	
}


?>








