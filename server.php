<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class Values {
    const SERVERNAME = "";
    const USERNAME = "";
    const PASS = "";
    const DBNAME = "";

    private $conn = null;

    function __construct() {
        ;
    }

    function initConnection() {
        // Create a connection to the database using PDO
        try {
            $this->conn = new PDO("mysql:host=".self::SERVERNAME.";dbname=".self::DBNAME, self::USERNAME, self::PASS);
            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: ".$e->getMessage());
        }
    }

    function queryValues() {
        if($this->conn) {
            $reply = array();

            // Select the value from the table
            $sql = "SELECT state FROM `values`"; // Use the same table name
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            // Check the result
            if($stmt->rowCount() > 0) {
                // Fetch the value as a numeric value
                $value = $stmt->fetchColumn();

                // Convert the value to a string value (true or false)
                $value = $value == 1 ? 'true' : 'false';

                // Send the value as a response
                $reply['success'] = true;
                $reply['value'] = $value;
            } else {
                // Send an error response
                $reply['success'] = false;
                $reply['error'] = "No value found";
            }
            return $reply;
        } else {
            $reply = array();
            $reply['success'] = false;
            $reply['error'] = "DB connection not open";
            return $reply;
        }
    }
    function insertValue($value) {
        if($this->conn) {
            // Validate the value as a boolean
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if($value !== null) {
                // Convert the value to a numeric value (1 for true, 0 for false)
                $value = $value ? 1 : 0;

                // Create the table if not exists
                $sql = "CREATE TABLE IF NOT EXISTS `values` (
                  state TINYINT(1) NOT NULL
                )";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                // Delete all rows from the table
                $sql = "DELETE FROM `values`";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                // Insert a new row with the new value
                $sql = "INSERT INTO `values` (state) VALUES (:value)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':value', $value);
                $stmt->execute();

                // Check the result
                if($stmt->rowCount() > 0) {
                    // Send a success response
                    return 'OK';
                } else {
                    // Send an error response
                    return "No value inserted";
                }
            } else {
                return 'Invalid value';
            }

        } else {
            return 'DB connection error';
        }
    }

    function closeConnection() {
        if($this->conn) {
            $this->conn = null; // Close the connection by setting it to null
        }
    }
}

$hs = new Values();
$hs->initConnection();

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = $hs->queryValues();

    echo json_encode($response);
}
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');
    $data_decoded = urldecode($data);
    $item = json_decode($data_decoded, true);
    if(isset($item['value'])) {
        $success = $hs->insertValue($item['value']);
        if($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No value updated']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid value']);
    }
}

$hs->closeConnection();
?>