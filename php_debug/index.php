<!---PHP----->
<?php
  $servername = "localhost";
  $username = "root";
  $password = "p@5Sw0r+171188";
  $dbName = "myDB";
  $conn = new mysqli($servername, $username, $password, $dbName);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT id, firstname, lastname FROM Person";
  $result = $conn->query($sql);
  $rows = $result->fetch_all();
  $conn->close();
?>
<!-------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Document</title>
</head>
<body>
      <table>
          <thead>
            <tr>
              <th>Vorname</th>
              <th>Nachname</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$result->num_rows;$i++):?>
              <tr>
                <th><?=$rows[$i][1]?></th>
                <th><?=$rows[$i][2]?></th>
              </tr>
            <?php endfor ?>  
          </tbody>
        </table>
  <button>drücken</button>
  <script src="script.js"></script>
</body>

</html>

