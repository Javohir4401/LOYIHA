<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>

<body>
    <form method="POST">
        <div class="container text-center text-primary">
            <h1>Hello World</h1>
        </div>
        <div class="container">
            <div class="mb-3">
                <label for="name" class="form-label">ISM</label>
                <input type="text" class="form-control" id="name" aria-describedby="emailHelp" name="name" required>
            </div>
            <div class="mb-3">
                <label for="arrived_at" class="form-label">KELGAN VAQTI</label>
                <input type="datetime-local" class="form-control" id="arrived_at" name="arrived_at" required>
            </div>
            <div class="mb-3">
                <label for="left_at" class="form-label">KETGAN VAQTI</label>
                <input type="datetime-local" class="form-control" id="left_at" name="left_at" required>
            </div>
            <button class="btn btn-primary" type="submit" value="Submit">YUBORISH</button>
        </div>
    </form>

    <?php

$dsn = "mysql:host=localhost;dbname=homework";
$pdo = new PDO($dsn, username: "root", password: "1112");

const required_work_hour_daily = 8;

// Foydalanuvchining so'nggi 10 kun ichidagi jami ish qarzdorligini hisoblaydi
function calculateDebtLast10Days($pdo, $name) {
    $required_seconds = required_work_hour_daily * 3600;
    
    $select_query = "
        SELECT SUM(GREATEST(0, :required_of - (TIMESTAMPDIFF(SECOND, arrived_at, left_at)))) AS total_debt 
        FROM daily 
        WHERE name = :name AND arrived_at >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)";
    
    $stmt = $pdo->prepare($select_query);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":required_of", $required_seconds, PDO::PARAM_INT);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total_debt'] ?? 0;
}

if (isset($_POST["name"]) && isset($_POST["arrived_at"]) && isset($_POST["left_at"])) {

    if (!empty($_POST['name']) && !empty($_POST['arrived_at']) && !empty($_POST['left_at'])) {
        $name = $_POST["name"];
        $arrived_at = new DateTime($_POST["arrived_at"]);
        $left_at = new DateTime($_POST["left_at"]);

        $diff = $arrived_at->diff($left_at);
        $hour = $diff->h;
        $minute = $diff->i;
        $second = $diff->s;
        $worked_seconds = ($hour * 3600) - ($minute * 60) + $second;

        $required_seconds = required_work_hour_daily * 3600;
        $total = $required_seconds - $worked_seconds;

        $insertQuery = "INSERT INTO time(name, arrived_at, left_at, worked_seconds, required_of)  
                        VALUES (:name, :arrived_at, :left_at, :worked_seconds, :required_of)";

        $stmt = $pdo->prepare($insertQuery);

        $stmt->bindParam(":name", $name);
        $stmt->bindValue(":arrived_at", $arrived_at->format("Y-m-d H:i"));
        $stmt->bindValue(":left_at", $left_at->format("Y-m-d H:i"));
        $stmt->bindParam(":worked_seconds", $worked_seconds);
        $stmt->bindParam(":required_of", $required_seconds);
        $stmt->execute();
        header('Location: work.php');
    }
}

$select_query = "SELECT * FROM time";
$next_stmt = $pdo->query($select_query);
$records = $next_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <table class="table table-primary">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">ISMI</th>
                <th scope="col">KELGAN VAQTI</th>
                <th scope="col">KETGAN VAQTI</th>
                <th scope="col">QARZDORLIK VAQTI</th>
                <th scope="col">10 KUNLIK JAMI QARZDORLIK</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($records)) {
                foreach ($records as $record) {
                    $debtLast10Days = calculateDebtLast10Days($pdo, $record['name']);
                    echo "<tr>
                        <td>{$record['id']}</td>
                        <td>{$record['name']}</td>
                        <td>{$record['arrived_at']}</td>
                        <td>{$record['left_at']}</td>
                        <td>" . gmdate('H:i', $record['required_of']) . "</td>
                        <td>" . gmdate('H:i', $debtLast10Days) . "</td>
                    </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
