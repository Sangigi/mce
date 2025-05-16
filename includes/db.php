<?php
require_once 'config.php';

class Database {
    private $conn;
    
    // Constructor - conecta a la base de datos
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    // Ejecutar consulta
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            die("Error en la consulta: " . $e->getMessage());
        }
    }
    
    // Obtener un solo registro
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener múltiples registros
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Insertar registro
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);
        
        $sql = "INSERT INTO $table (" . implode(", ", $fields) . ") 
                VALUES (" . implode(", ", $placeholders) . ")";
        
        $stmt = $this->query($sql, $data);
        return $this->conn->lastInsertId();
    }
    
    // Actualizar registro
    public function update($table, $data, $where, $whereParams = []) {
        $setFields = array_map(function($field) {
            return "$field = :$field";
        }, array_keys($data));
        
        $sql = "UPDATE $table SET " . implode(", ", $setFields) . " WHERE $where";
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Eliminar registro
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}

// Instancia global de la base de datos
$db = new Database();
