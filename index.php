<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="bootstrap.css">
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
                $('.upload').on('change', function(event) {

                    var index = $(event.target).attr('data-index');
                    var file_data = $(event.target).prop('files')[0];
                    //console.log(file_data);
                    var form_data = new FormData();                  
                    form_data.append('file', file_data);
                    form_data.append('turniej', $(event.target).attr('data-turniej'));
                    form_data.append('runda', $(event.target).attr('data-runda'));
                    form_data.append('player1', $(event.target).attr('data-player1'));
                    form_data.append('player2', $(event.target).attr('data-player2'));
                    form_data.append('p1pts', $(event.target).attr('data-p1pts'));
                    form_data.append('p2pts', $(event.target).attr('data-p2pts'));
                    $.ajax({
                       
                     url: 'upload.php', // point to server-side PHP script 
                     dataType: 'text',  // what to expect back from the PHP script, if anything
                     cache: false,
                     contentType: false,
                     processData: false,
                     data: form_data,
                     type: 'post',
                     success: function(php_script_response){
                         var response = $.parseJSON(php_script_response);
                         if ( response.status == 'error') {
                             alert( response.errormsg );
                         }
                         else {
                             console.log(response);
                             var temp_fname = $(event.target).attr('data-turniej') + '_' + $(event.target).attr('data-runda') + '_' + $(event.target).attr('data-player1') + '_' + $(event.target).attr('data-player2') + '.gcg';
                             var board_link = '<a href=board.php?turniej=' + $(event.target).attr('data-turniej') + '&runda=' + $(event.target).attr('data-runda') + '&p1=' + $(event.target).attr('data-player1') + '&p2=' + $(event.target).attr('data-player2') + '>';

                             $(event.target).closest('td').prepend(board_link + '[zapis]</a> ');
                             $(event.target).closest('.fileUpload').hide();
                         }
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

require_once "../system.php";
db_open();


$sql="SELECT id, name_alph FROM ".TBL_PLAYER." WHERE utype!='L' ORDER BY name_alph";
$result = db_query($sql);

while($row = db_fetch_assoc($result)) {
    echo "<option value='". $row['id'] . "'>" . $row['name_alph'] . "</option>";
}
db_close();
?>

</select>
</form>
<br>
<div id="txtHint"><b>Lista gier wyświetli się tutaj...</b></div>

</body>
</html>
