<html>
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
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
                //http://stackoverflow.com/questions/23980733/jquery-ajax-file-upload-php
                //http://stackoverflow.com/a/21061777/2261298
                $('.upload').on('click', function(event) {
                    var index = $(event.target).attr('data-index');
                    var file_data = $('.inp*[data-index=' + index + ']').prop('files')[0];
                    console.log(file_data);
                    var form_data = new FormData();                  
                    form_data.append('file', file_data);
                    form_data.append('turniej', $(event.target).attr('data-turniej'));
                    form_data.append('runda', $(event.target).attr('data-runda'));
                    form_data.append('player1', $(event.target).attr('data-player1'));
                    form_data.append('player2', $(event.target).attr('data-player2'));
                    alert(form_data);                             
                    $.ajax({
                            url: 'upload.php', // point to server-side PHP script 
                     dataType: 'text',  // what to expect back from the PHP script, if anything
                     cache: false,
                     contentType: false,
                     processData: false,
                     data: form_data,                         
                     type: 'post',
                     success: function(php_script_response){
                         alert(php_script_response); // display response from the PHP script, if any
                         var temp_fname = $(event.target).attr('data-turniej') + '_' + $(event.target).attr('data-runda') + '_' + $(event.target).attr('data-player1') + '_' + $(event.target).attr('data-player2') + '.gcg'
                         $(event.target).closest('td').prepend('<a href=upload/gcg/' + temp_fname + '>[zapis]</a> ');
                     }
                     });
                });
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
  <option value="">Zawodnik:</option>
<?php

include 'config.php';

$con = mysqli_connect($mysqlhost,$mysqluser, $mysqlpwd, $mysqldbname);
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}
//this is a temporary fix, people have said I shouldn't be using SET NAMES
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
<div id="txtHint"><b>Lista gier wyświetli się tutaj...</b></div>

</body>
</html>
