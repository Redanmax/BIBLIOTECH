<?php
class Autor {
    private $id;
    private $nombre;
    private $nacionalidad;
    private $fecha_nacimiento;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getNacionalidad() { return $this->nacionalidad; }
    public function setNacionalidad($nacionalidad) { $this->nacionalidad = $nacionalidad; }
    public function getFechaNacimiento() { return $this->fecha_nacimiento; }
    public function setFechaNacimiento($fecha) { $this->fecha_nacimiento = $fecha; }
    
    // Métodos CRUD
    public function guardar() {
        if ($this->id) {
            // Actualizar
            $sql = "UPDATE autores SET nombre = ?, nacionalidad = ?, fecha_nacimiento = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->nombre, 
                $this->nacionalidad, 
                $this->fecha_nacimiento, 
                $this->id
            ]);
        } else {
            // Insertar
            $sql = "INSERT INTO autores (nombre, nacionalidad, fecha_nacimiento) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->nombre, 
                $this->nacionalidad, 
                $this->fecha_nacimiento
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
            return $result;
        }
    }
    
    public static function buscarPorId($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM autores WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        
        $autor = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($autor) {
            $obj = new self();
            $obj->id = $autor['id'];
            $obj->nombre = $autor['nombre'];
            $obj->nacionalidad = $autor['nacionalidad'];
            $obj->fecha_nacimiento = $autor['fecha_nacimiento'];
            return $obj;
        }
        return null;
    }
    
    public static function listarTodos() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM autores ORDER BY nombre";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
