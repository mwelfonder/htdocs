<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<title>SB Admin - Start Bootstrap Template</title>
<!-- Bootstrap core CSS-->
<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom fonts for this template-->
<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<!-- Page level plugin CSS-->
<link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
<!-- Custom styles for this template-->
<link href="css/sb-admin.css" rel="stylesheet">
</head>
<body class="fixed-nav sticky-footer bg-dark" id="page-top">
<!-- Navigation-->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
<a class="navbar-brand" href="index.php">SCan4 GmbH</a>
<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarResponsive">
<ul class="navbar-nav navbar-sidenav" id="exampleAccordion">
<li class="nav-item" data-toggle="tooltip" data-placement="right" title="Dashboard">
<a class="nav-link" href="index.php">
<i class="fa fa-fw fa-dashboard"></i>
<span class="nav-link-text">Dashboard</span>
</a>
</li>
<li class="nav-item" data-toggle="tooltip" data-placement="right" title="Charts">
<a class="nav-link" href="product.php">
<i class="fa fa-check-square"></i>
<span class="nav-link-text">Telefonist Check</span>
</a>
</li>
<li class="nav-item" data-toggle="tooltip" data-placement="right" title="Charts">
<a class="nav-link" href="register.php">
<i class="fa fas fa-user"></i>
<span class="nav-link-text">User erstellen</span>
</a>
</li>
</ul>
<ul class="navbar-nav sidenav-toggler">
<li class="nav-item">
<a class="nav-link text-center" id="sidenavToggler">
<i class="fa fa-fw fa-angle-left"></i>
</a>
</li>
</ul>
<ul class="navbar-nav ml-auto">
<li class="nav-item dropdown">


<!-- Breadcrumbs-->
<ol class="breadcrumb">
<li class="breadcrumb-item">
<a href="#">Dashboard</a>
</li>
<li class="breadcrumb-item active">Dashboard</li>
</ol>
<!-- Icon Cards-->
<?php
$servername = "db.scan4-gmbh.com";
$username = "SC4_CRM";
$password = "";
$dbname = "SC4_CRM_2";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$sqll = "SELECT  * from scan4_hbgcheck WHERE month='Mar' ";
if (mysqli_query($conn, $sqll))
{
echo "";
}
else
{
echo "Error: " . $sqll . "<br>" . mysqli_error($conn);
}
$result = mysqli_query($conn, $sqll);
if (mysqli_num_rows($result) > 0)
{
// output data of each row
while($row = mysqli_fetch_assoc($result))
{
?>


<?php
}
}
else
{
echo '0 results';
}
?>

<?php
$servername = "db.scan4-gmbh.com";
$username = "root";
$password = "";
$dbname = "registration";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$sqlll = "SELECT noCrm from BenNothingDO";
if (mysqli_query($conn, $sqlll))
{
echo "";
}
else
{
echo "Error: " . $sqlll . "<br>" . mysqli_error($conn);
}
$result = mysqli_query($conn, $sqlll);
$number=array();
if (mysqli_num_rows($result) > 0)
{
// output data of each row
while($row = mysqli_fetch_assoc($result))
{
$number[]=$row['sales'];
}
}
else
{
echo "0 results";
}
$number_formated= "[".implode(",",$number)."]";
?>
<script type="text/javascript">
window.dataf= <?php echo $number_formated; ?>
</script>
<?php
$servername = "db.scan4-gmbh.com";
$username = "root";
$password = "";
$dbname = "registration";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$sql = 'SELECT * from scam';
if (mysqli_query($conn, $sql)) {
echo "";
} else {
echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
$count=1;
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
// output data of each row
while($row = mysqli_fetch_assoc($result)) { ?>

<?php
$count++;
}
} else {
echo '0 results';
}
?>

</div>
<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Page level plugin JavaScript-->
<script src="vendor/chart.js/Chart.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.js"></script>
<!-- Custom scripts for all pages-->
<script src="js/sb-admin.min.js"></script>
<!-- Custom scripts for this page-->
<script src="js/sb-admin-datatables.min.js"></script>
<script src="js/sb-admin-charts.min.js"></script>
</div>
</body>
</html>