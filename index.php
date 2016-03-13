<html>
<head>
<meta charset="UTF-8">
<script>
function showUser(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","getuser.php?q="+str,true);
        xmlhttp.send();
    }
}
</script>
</head>
<body>

<form>
<select name="users" onchange="showUser(this.value)">
  <option value="">Select a person:</option>
<?php

include 'config.php';

$con = mysqli_connect('localhost','root', $mysqlpwd, $mysqldbname);
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}
mysqli_query($con, "SET NAMES 'utf8'");

mysqli_set_charset('utf8', $con);
$sql="SELECT id, name_alph FROM PFSPLAYER ORDER BY name_alph";
$result = mysqli_query($con,$sql);
while($row = mysqli_fetch_array($result)) {
    echo "<option value='". $row['id'] . "'>" . $row['name_alph'] . "</option>";
}
?>
  </select>
</form>
<br>
<div id="txtHint"><b>Person info will be listed here...</b></div>

</body>
</html>
