<?php
class Categoria {
    private $id;
    private $nombre;
    private $descripcion;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Getters y Setters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    
    public function guardar() {
        if ($this->id) {
            $sql = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->nombre, 
                $this->descripcion, 
                $this->id
            ]);
        } else {
            $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->nombre, 
                $this->descripcion
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
            return $result;
        }
    }
    
    public static function buscarPorId($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM categorias WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($categoria) {
            $obj = new self();
            $obj->id = $categoria['id'];
            $obj->nombre = $categoria['nombre'];
            $obj->descripcion = $categoria['descripcion'];
            return $obj;
        }
        return null;
    }
    
    public static function listarTodas() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM categorias ORDER BY nombre";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
