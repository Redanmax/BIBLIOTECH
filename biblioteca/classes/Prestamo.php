<?php
class Prestamo {
    private $id;
    private $libro_id;
    private $usuario;
    private $fecha_prestamo;
    private $fecha_devolucion;
    private $devuelto;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->fecha_prestamo = date('Y-m-d');
        $this->devuelto = false;
    }
    
    public function getId() { return $this->id; }
    public function getLibroId() { return $this->libro_id; }
    public function setLibroId($libro_id) { $this->libro_id = $libro_id; }
    public function getUsuario() { return $this->usuario; }
    public function setUsuario($usuario) { $this->usuario = $usuario; }
    public function getFechaPrestamo() { return $this->fecha_prestamo; }
    public function setFechaPrestamo($fecha) { $this->fecha_prestamo = $fecha; }
    public function getFechaDevolucion() { return $this->fecha_devolucion; }
    public function setFechaDevolucion($fecha) { $this->fecha_devolucion = $fecha; }
    public function getDevuelto() { return $this->devuelto; }
    public function setDevuelto($devuelto) { $this->devuelto = $devuelto; }
    
    public function guardar() {
        if ($this->id) {
            $sql = "UPDATE prestamos SET libro_id = ?, usuario = ?, fecha_prestamo = ?, 
                    fecha_devolucion = ?, devuelto = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->libro_id, $this->usuario, $this->fecha_prestamo,
                $this->fecha_devolucion, $this->devuelto, $this->id
            ]);
        } else {
            $sql = "INSERT INTO prestamos (libro_id, usuario, fecha_prestamo, fecha_devolucion, devuelto) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->libro_id, $this->usuario, $this->fecha_prestamo,
                $this->fecha_devolucion, $this->devuelto
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
            return $result;
        }
    }
    
    public function realizarPrestamo() {
        $libro = Libro::buscarPorId($this->libro_id);
        if (!$libro || !$libro->getDisponible()) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            if ($this->guardar()) {
                $libro->setDisponible(false);
                if ($libro->guardar()) {
                    $this->db->commit();
                    return true;
                }
            }
            $this->db->rollBack();
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function devolverLibro() {
        $this->devuelto = true;
        $this->fecha_devolucion = date('Y-m-d');
        
        $this->db->beginTransaction();
        
        try {
            if ($this->guardar()) {
                $libro = Libro::buscarPorId($this->libro_id);
                $libro->setDisponible(true);
                if ($libro->guardar()) {
                    $this->db->commit();
                    return true;
                }
            }
            $this->db->rollBack();
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public static function buscarPorId($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*, l.titulo as libro_titulo 
                FROM prestamos p 
                LEFT JOIN libros l ON p.libro_id = l.id 
                WHERE p.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function listarPrestamosActivos() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*, l.titulo as libro_titulo 
                FROM prestamos p 
                LEFT JOIN libros l ON p.libro_id = l.id 
                WHERE p.devuelto = false 
                ORDER BY p.fecha_prestamo DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
