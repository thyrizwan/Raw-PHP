<?php

class StudentAPI
{
    /**
     * Written by Rizwan Ansari(https://github.com/thyrizwan)
     * 
     * Below code for creating the table
     * 
     * CREATE TABLE student (
     * id INT AUTO_INCREMENT PRIMARY KEY,
     * roll VARCHAR(30) NOT NULL,
     * name VARCHAR(255) NOT NULL,
     * father_name VARCHAR(255) NOT NULL,
     * dob DATE NOT NULL,
     * gender ENUM('Male', 'Female', 'Transgender') NOT NULL,
     * created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     * );
     * 
     */

    private $connect;

    public function __construct()
    {
        try {
            $host = "localhost";
            $db_name = "php_api";
            $username = "root";
            $password = "";
            $this->connect = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function validateInput($data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required.';
        }

        if (empty($data['father_name'])) {
            $errors['father_name'] = 'Father name is required.';
        }

        if (empty($data['dob'])) {
            $errors['dob'] = 'Date of birth is required.';
        }

        if (empty($data['gender']) || !in_array($data['gender'], ['Male', 'Female', 'Transgender'])) {
            $errors['gender'] = 'Invalid gender value.';
        }

        return $errors;
    }

    public function insertStudent($data)
    {
        $validationErrors = $this->validateInput($data);

        if (empty($validationErrors)) {
            $roll = 'PRFX-' . date('Y') . (str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT) . time());

            $stmt = $this->connect->prepare("INSERT INTO student (roll, name, father_name, dob, gender) VALUES (:roll, :name, :father_name, :dob, :gender)");
            $stmt->bindParam(':roll', $roll, PDO::PARAM_STR);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':father_name', $data['father_name'], PDO::PARAM_STR);
            $stmt->bindParam(':dob', $data['dob'], PDO::PARAM_STR);
            $stmt->bindParam(':gender', $data['gender'], PDO::PARAM_STR);
            $stmt->execute();

            $studentInfo = [
                'roll' => $roll,
                'name' => $data['name'],
                'father_name' => $data['father_name'],
                'dob' => $data['dob'],
                'gender' => $data['gender'],
            ];

            return ['success' => true, 'message' => 'Data Inserted Successfully', 'studentInfo' => $studentInfo];
        } else {
            return ['errors' => $validationErrors];
        }
    }
}



$studentAPI = new StudentAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $response = $studentAPI->insertStudent($data);
} else {
    $response = ['message' => 'Method not allowed'];
}

header('Content-Type: application/json');
echo json_encode($response);
