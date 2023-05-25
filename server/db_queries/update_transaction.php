<?php
    if($_SERVER["REQUEST_METHOD"] === "GET"){
        echo "This file should not be accessed through browser.";
        exit;
    }
    
    require "../db_conn/connection.php";
    require "../utils/headers.php";
    require "../utils/db_components.php";
    require "../jwt/jwt.php";
    require "../mail/send_mail.php";

    $request = file_get_contents("php://input");
    $data = json_decode($request, true);

    $requestHeaders = apache_request_headers();
    list(, $token) = explode(" ", $requestHeaders["Authorization"]);
    performValidationProcess($token, $data["id"], $data["role"]);

    $connection = getDBConnection();
    $statement = $connection->prepare("UPDATE $transaction_table_name SET transaction_status = ? WHERE id = ?");
    $statement->bind_param("ss", $data["updatedStatus"], $data["transactionId"]);
    $statement->execute();
    $statement->close();
    $connection->close();

    if($data["updatedStatus"] === "confirmed"){
        sendMail($data["email"], "confirmed", $data["productName"]);
    }
?>