<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript1.3" src="../js/script.js"></script>
<link rel="stylesheet" href="../css/styluzup.css">
</head>
<?php
	$target_path = "../uploads/";
	$target_path = $target_path . basename( $_FILES['uploadedfile']['name']);
	if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) 
	{
		echo "The file ". basename( $_FILES['uploadedfile']['name']). " has been uploaded";
		echo "<br><a href='uzupelnianie.html'>Powrot</a>";
	}
       	else
	{
		echo "There was an error uploading the file, please try again!";
		echo "<br><a href='uzupelnianie.html'>Powrot</a>";
	}
?>
</html>
