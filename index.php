<?php
require 'assets/partials/_functions.php';
$conn = db_connect();
if (!$conn)
    die("Connection Failed");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>19BCI0241_Ez Bus</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <?php
    require 'assets/styles/styles.php'
    ?>
</head>

<body>
    <?php

    if (isset($_GET["booking_added"]) && !isset($_POST['pnr-search'])) {
        if ($_GET["booking_added"]) {
            echo '<div class="my-0 alert alert-success alert-dismissible fade show" role="alert">
                <strong>Successful!</strong> Booking Added, your PNR is <span style="font-weight:bold; color: #272640;">' . $_GET["pnr"] . '</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        } else {
            // Show error alert
            echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Booking already exists
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["pnr-search"])) {
        $pnr = $_POST["pnr"];

        $sql = "SELECT * FROM bookings WHERE booking_id='$pnr'";
        $result = mysqli_query($conn, $sql);

        $num = mysqli_num_rows($result);

        if ($num) {
            $row = mysqli_fetch_assoc($result);
            $route_id = $row["route_id"];
            $customer_id = $row["customer_id"];
            $customer_name = get_from_table($conn, "customers", "customer_id", $customer_id, "customer_name");
            $customer_phone = get_from_table($conn, "customers", "customer_id", $customer_id, "customer_phone");
            $customer_route = $row["customer_route"];
            $booked_amount = $row["booked_amount"];
            $booked_seat = $row["booked_seat"];
            $booked_timing = $row["booking_created"];
            $dep_date = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_date");
            $dep_time = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_time");
            $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
    ?>

            <div class="alert alert-dark alert-dismissible fade show" role="alert">

                <h4 class="alert-heading">Want Your Booking Details ?</h4>
                <p>
                    <button class="btn btn-sm btn-success"><a href="assets/partials/_download.php?pnr=<?php echo $pnr; ?>" class="link-light">Download</a></button>
                    <button class="btn btn-danger btn-sm" id="deleteBooking" data-bs-toggle="modal" data-bs-target="#deleteModal" data-pnr="<?php echo $pnr; ?>" data-seat="<?php echo $booked_seat; ?>" data-bus="<?php echo $bus_no; ?>">
                        Delete
                    </button>
                </p>
                <hr>
                <p class="mb-0">
                <ul class="pnr-details">
                    <li>
                        <strong>PNR : </strong>
                        <?php echo $pnr; ?>
                    </li>
                    <li>
                        <strong>Customer Name : </strong>
                        <?php echo $customer_name; ?>
                    </li>
                    <li>
                        <strong>Customer Phone : </strong>
                        <?php echo $customer_phone; ?>
                    </li>
                    <li>
                        <strong>Route : </strong>
                        <?php echo $customer_route; ?>
                    </li>
                    <li>
                        <strong>Bus Number : </strong>
                        <?php echo $bus_no; ?>
                    </li>
                    <li>
                        <strong>Booked Seat Number : </strong>
                        <?php echo $booked_seat; ?>
                    </li>
                    <li>
                        <strong>Departure Date : </strong>
                        <?php echo $dep_date; ?>
                    </li>
                    <li>
                        <strong>Departure Time : </strong>
                        <?php echo $dep_time; ?>
                    </li>
                    <li>
                        <strong>Booked Timing : </strong>
                        <?php echo $booked_timing; ?>
                    </li>

                    </p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } else {
            echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Record Doesnt Exist
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }

        ?>

    <?php }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteBtn"])) {
        $pnr = $_POST["id"];
        $bus_no = $_POST["bus"];
        $booked_seat = $_POST["booked_seat"];

        $deleteSql = "DELETE FROM `bookings` WHERE `bookings`.`booking_id` = '$pnr'";

        $deleteResult = mysqli_query($conn, $deleteSql);
        $rowsAffected = mysqli_affected_rows($conn);
        $messageStatus = "danger";
        $messageInfo = "";
        $messageHeading = "Error!";

        if (!$rowsAffected) {
            $messageInfo = "Record Doesn't Exist";
        } elseif ($deleteResult) {
            $messageStatus = "success";
            $messageInfo = "Booking Details deleted";
            $messageHeading = "Successfull!";
            $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");
            $booked_seat = $_POST["booked_seat"];
            $seats = explode(",", $seats);
            $idx = array_search($booked_seat, $seats);
            array_splice($seats, $idx, 1);
            $seats = implode(",", $seats);
            $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
            mysqli_query($conn, $updateSeatSql);
        } else {

            $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
        }

        // Message
        echo '<div class="my-0 alert alert-' . $messageStatus . ' alert-dismissible fade show" role="alert">
                <strong>' . $messageHeading . '</strong> ' . $messageInfo . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    ?>


    <header>
        <nav>
            <div>
                <a href="#" class="nav-item nav-logo">Ez Bus | Mukund Mohan Zutshi</a>
            </div>

            <ul>
                <li><a href="#" class="nav-item">Home</a></li>
                <li><a href="#about" class="nav-item">About</a></li>
                <li><a href="#contact" class="nav-item">Contact Us</a></li>
            </ul>
            <div>
                <a href="#" class="login nav-item" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-sign-in-alt" style="margin-right: 0.4rem;"></i>Login</a>
                <a href="#pnr-enquiry" class="pnr nav-item">PNR Help</a>
            </div>
        </nav>
    </header>
    <?php
    require 'assets/partials/_loginModal.php';
    require 'assets/partials/_getJSON.php';
    $routeData = json_decode($routeJson);
    $busData = json_decode($busJson);
    $customerData = json_decode($customerJson);
    ?>


    <section id="home">
        <div id="route-search-form">
            <h1>Ez Bus | Mukund Mohan Zutshi</h1>
            <center>
                <button class="btn btn-danger " data-bs-toggle="modal" data-bs-target="#loginModal">Admin Login</button>
            </center>
            <br>
            <center>
                <a href="#pnr-enquiry"><button class="btn btn-primary">Scroll Down <i class="fa fa-arrow-down"></i></button></a>
            </center>
        </div>
    </section>
    <div id="block">
        <section id="info-num">
            <lottie-player src="https://assets7.lottiefiles.com/packages/lf20_cvcwsr0y.json" background="transparent" speed="1" style="width: 300px; height: 300px;" hover loop autoplay></lottie-player>
        </section>
        <section id="pnr-enquiry">
            <div id="pnr-form">
                <h2>PNR ENQUIRY</h2>
                <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST">
                    <div>
                        <input type="text" name="pnr" id="pnr" placeholder="Enter PNR">
                    </div>
                    <button type="submit" name="pnr-search">Submit</button>
                </form>
            </div>
        </section>
        <section id="about">
            <div>
                <br>
                <br>
                <h1>About Us</h1>
                <p>
                    IWP Theory Da<br>
                    Submitted By: Mukund Mohan Zutshi<br>
                    19BCI0241
                </p>
            </div>
        </section>
        <section id="contact">
            <div id="contact-form">
                <h1>Contact Us</h1>
                <form action="">
                    <div>
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name">
                    </div>
                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email">
                    </div>
                    <div>
                        <label for="message">Message</label>
                        <textarea name="message" id="message" cols="30" rows="10"></textarea>
                    </div>
                    <div></div>
                </form>
            </div>
        </section>
        <footer>
            <p>
                <i class="far fa-copyright"></i> <?php echo date('Y'); ?> - Ez Bus | Mukund Mohan Zutshi
            </p>
        </footer>
    </div>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-circle"></i></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h2 class="text-center pb-4">
                        Are you sure?
                    </h2>
                    <p>
                        Do you really want to delete your booking? <strong>This process cannot be undone.</strong>
                    </p>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="delete-form" method="POST">
                        <input id="delete-id" type="hidden" name="id">
                        <input id="delete-booked-seat" type="hidden" name="booked_seat">
                        <input id="delete-booked-bus" type="hidden" name="bus">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="delete-form" class="btn btn-primary btn-danger" name="deleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="assets/scripts/main.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</body>

</html>