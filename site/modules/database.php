<?php
class Database {

    private $pdo;

    
    public function __construct(string $path){
        try {
    // Путь к файлу базы данных SQLite (создастся автоматически, если не существует)
    $this -> pdo = new PDO("sqlite:" . $path);

    // Настройки: выбрасывать исключения при ошибках
    $this -> pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Успешное подключение к базе данных SQLite";
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
};
    }

    public function Execute($sql) : bool{
      try{  
        return $this->pdo->exec($sql) !==false; 
    } catch(PDOException $e)  {
        die("Execute error" . $e->getMessage());
    }
}

    public function Fetch($sql) : array{
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Fetch error" . $e->getMessage());
        }
    }

    public function Create(string $table, array $data): int {
        try {
        
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
            $stmt = $this->pdo->prepare($sql);
    
            $stmt->execute($data);

            return $this->pdo->lastInsertId();
    
        } catch (PDOException $e) {
            die("Create error: " . $e->getMessage());
        }
    }

    public function Read($table, $id): mixed{
        try {
            $sql = "SELECT * FROM $table WHERE id = :id LIMIT 1";
            $statement = $this->connection->prepare($sql);
            $statement->bindValue(":id", $id);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
            
        } catch (PDOException $e) {
            die("Create error: " . $e->getMessage());
        }

    }

    public function Update($table, $id, $data): bool {
        try {
            // Создаём строку "ключ1 = :ключ1, ключ2 = :ключ2"
            $columns = [];
            foreach ($data as $key => $value) {
                $columns[] = "$key = :$key";
            }
            $setString = implode(", ", $columns);
    
            $sql = "UPDATE $table SET $setString WHERE id = :id";
    
            $statement = $this->connection->prepare($sql);
    
            // Привязываем значения
            foreach ($data as $key => $value) {
                $statement->bindValue(":$key", $value);
            }
            $statement->bindValue(":id", $id);
    
            return $statement->execute();
        } catch (PDOException $e) {
            die("Update error: " . $e->getMessage());
        }
    }
    
    public function Delete(string $table, int $id): bool {
        try {
            $sql = "DELETE FROM $table WHERE id = :id";
            $statement = $this->connection->prepare($sql);
            $statement->bindValue(":id", $id);

            return $statement->execute();
        } catch (PDOException $e) {
            die("Delete record error: " . $e->getMessage());
        }
    }

    public function Count(string $table): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM $table";
            $statement = $this->connection->prepare($sql);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (PDOException $e) {
            die("Count records error: " . $e->getMessage());
        }
    }

}
?>