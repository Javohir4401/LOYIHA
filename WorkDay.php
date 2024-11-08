<?php

require 'DB.php';

class WorkDay{
    const REQUIRED_HOUR_DURATION = 8;
    public $conn;
    public function __construct () {
        $db = new DB();
        $this->conn = $db->pdo;
    }
    public function store (string $name, string $arrived_at, string $left_at) {

        $arrived_at = new DateTime($arrived_at);
        $left_at = new DateTime($left_at);
        $diff = $arrived_at->diff($left_at);
        $hour = $diff->h;
        $minute = $diff->i;
        $total = ((self::REQUIRED_HOUR_DURATION * 3600) - (($hour * 3600) + ($minute * 60)));
        $query = "INSERT INTO daily (name,arrived_at,left_at, required_of) 
                        VALUES (:name, :arrived_at, :left_at, :required_of)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindValue(':arrived_at', $arrived_at->format('Y-m-d H:i'));
        $stmt->bindValue(':left_at', $left_at->format('Y-m-d H:i'));
        $stmt->bindParam(':required_of', $total);
        $stmt->execute();
        header('Location: index.php');
        return;
    }

    public function getWorDayList () {
        $query = "SELECT * FROM daily";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function calculateDebtTimeForEachUser () {
        $query = "SELECT name, SUM(required_of) as debt FROM daily GROUP BY name";
    }
    public function markAsDone (int $id) {
        $stmt->execute();
        header('Location: index.php');
    }
    public function getWorkDayListWithPagination (int $offset) {
        $offset = $offset ? ($offset * 10)-10 : 0;
        $query = "SELECT * FROM daily ORDER BY arrived_at DESC LIMIT 10 OFFSET " . $offset;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getTotalRecords () {
        $query = "SELECT COUNT(id) as pageCount FROM daily";
        $stmt = $this->conn->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function calculatePageCount () {
        $total = $this->getTotalRecords()['pageCount'];
        return ceil($total/10);
    }
}
