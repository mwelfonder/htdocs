<?php

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/users/init.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/getdatg/users/init.php';
}
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
$username = $user->data()->username;
include "../view/includes/functions.php";

// Datenbankverbindung und Datenabfrage
$conn = dbconnect();
$query = "
    SELECT s.city, s.client, COUNT(h.city) as count, h.carrier 
    FROM scan4_citylist s
    LEFT JOIN scan4_homes h ON s.city = h.city 
    WHERE h.scan4_status = 'PENDING' 
    AND h.anruf5 IS NOT NULL 
    AND h.emailsend IS NULL
    GROUP BY s.city, h.carrier;
";
$result = $conn->query($query);
$cities = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>

    <!-- Add Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @import url(http://fonts.googleapis.com/css?family=Calibri:400,300,700);

        .scrollable-table {
            max-height: 500px;
            /* Angenommen, jeder Zeileneintrag ist 33px hoch: 15 Einträge * 33px = 495px (wählen Sie eine etwas höhere Zahl für Puffer) */
            overflow-y: auto;
        }

        .scrollable {
            max-height: 400px;
            /* Berechnung: 12 Zeilen * (angenommene Höhe von 33px pro Zeile) */
            overflow-y: auto;
        }


        .container {
            margin-top: 100px;
        }

        .card {
            position: relative;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 0px solid transparent;
            border-radius: 0px;
        }


        .card-body {
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            padding: 1.25rem;
        }

        .card .card-title {
            position: relative;
            font-weight: 600;
            margin-bottom: 10px;
        }


        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
        }

        * {
            outline: none;
        }

        .table th,
        .table thead th {
            font-weight: 500;
        }


        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }


        .table th {
            padding: 1rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }


        .table th,
        .table thead th {
            font-weight: 500;
        }


        th {
            text-align: inherit;
        }


        .m-b-20 {
            margin-bottom: 20px;
        }


        .customcheckbox {
            display: block;
            position: relative;
            padding-left: 24px;
            font-weight: 100;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 22px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }


        .customcheckbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: -3px;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #CDCDCD;
            border-radius: 6px;
        }


        .customcheckbox input:checked~.checkmark {
            background-color: #2196BB;
        }


        .customcheckbox .checkmark:after {
            left: 8px;
            top: 4px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                    </div>
                    <?php
                    $isScrollable = count($cities) > 12 ? 'scrollable' : '';
                    ?>
                    <div class="table-responsive <?php echo $isScrollable; ?>">
                        <table class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>
                                        <label class="customcheckbox m-b-20">
                                            <input type="checkbox" id="mainCheckbox">
                                        </label>
                                    </th>
                                    <th scope="col">City</th>
                                    <th scope="col">Carrier</th>
                                    <th scope="col">Pendings</th>
                                    <!-- Add more columns if required -->
                                </tr>
                            </thead>
                            <tbody class="customtable">
                                <?php
                                foreach ($cities as $city) {
                                    echo '<tr>';
                                    echo '<th><label class="customcheckbox"><input type="checkbox" class="listCheckbox"><span class="checkmark"></span></label></th>';
                                    echo '<td>' . $city['city'] . '</td>';
                                    echo '<td>' . $city['carrier'] . '</td>';
                                    echo '<td>' . $city['count'] . '</td>';
                                    // Add more columns if required
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="detailTables"></div>
    <!-- Bootstrap JS and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
<script>
    $(document).ready(function() {
        let debounce;
        $(".listCheckbox").on("change", function() {
            clearTimeout(debounce); // Debouncing
            debounce = setTimeout(function() {
                $("#detailTables").empty();
                $(".listCheckbox:checked").each(function() {
                    var cityName = $(this).closest("tr").find("td:first").text();
                    getCityDetails(cityName);
                });
            }, 250); // 250ms Debounce-Zeit
        });
    });

    function getCityDetails(cityName) {
    $.ajax({
        url: 'view/load/mailsender_load.php',
        type: 'GET',
        data: {
            city: cityName
        },
        dataType: 'json',
        success: function(dataArray) {
            console.log("Erhaltene Daten:", dataArray);

            const keys = ["city", "carrier", "firstname", "lastname", "street", "streetnumber", "streetnumberadd", "email", "scan4_status"];

            if (!dataArray.error) {
                let tableContainer = '<div class="container"><div class="row"><div class="col-12"><div class="card"><div class="card-body text-center"><h5 class="card-title m-b-0">Pendings</h5></div><div class="table-responsive"><table class="table"><thead class="thead-light"><tr>';

                keys.forEach(function(key) {
                    tableContainer += '<th scope="col">' + key + '</th>';
                });

                tableContainer += '</tr></thead><tbody class="customtable">';

                dataArray.forEach(function(data) {
                    let tableRow = '<tr>';
                    keys.forEach(function(key) {
                        tableRow += '<td>' + data[key] + '</td>';
                    });
                    tableRow += '</tr>';
                    tableContainer += tableRow;
                });

                tableContainer += '</tbody></table></div></div></div></div></div>';

                $("#detailTables").append(tableContainer);

                // Überprüfen Sie die Anzahl der Zeilen und fügen Sie ggf. die scrollable-Klasse hinzu
                if (dataArray.length > 10) {
                    $("#detailTables .table-responsive").addClass("scrollable");
                }

            } else {
                console.error(dataArray.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Fehler beim Abrufen der Stadtdaten:", textStatus, errorThrown);
            alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
        }
    });
}


</script>