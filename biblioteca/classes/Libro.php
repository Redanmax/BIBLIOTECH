<?php
class Libro {
    private $id;
    private $titulo;
    private $isbn;
    private $autor_id;
    private $categoria_id;
    private $anio_publicacion;
    private $editorial;
    private $disponible;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->disponible = true;
    }
    
    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function getIsbn() { return $this->isbn; }
    public function setIsbn($isbn) { $this->isbn = $isbn; }
    public function getAutorId() { return $this->autor_id; }
    public function setAutorId($autor_id) { $this->autor_id = $autor_id; }
    public function getCategoriaId() { return $this->categoria_id; }
    public function setCategoriaId($categoria_id) { $this->categoria_id = $categoria_id; }
    public function getAnioPublicacion() { return $this->anio_publicacion; }
    public function setAnioPublicacion($anio) { $this->anio_publicacion = $anio; }
    public function getEditorial() { return $this->editorial; }
    public function setEditorial($editorial) { $this->editorial = $editorial; }
    public function getDisponible() { return $this->disponible; }
    public function setDisponible($disponible) { $this->disponible = $disponible; }
    
    public function guardar() {
        if ($this->id) {
            $sql = "UPDATE libros SET titulo = ?, isbn = ?, autor_id = ?, categoria_id = ?, 
                    anio_publicacion = ?, editorial = ?, disponible = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->titulo, $this->isbn, $this->autor_id, $this->categoria_id,
                $this->anio_publicacion, $this->editorial, $this->disponible, $this->id
            ]);
        } else {
            $sql = "INSERT INTO libros (titulo, isbn, autor_id, categoria_id, anio_publicacion, editorial, disponible) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $this->titulo, $this->isbn, $this->autor_id, $this->categoria_id,
                $this->anio_publicacion, $this->editorial, $this->disponible
            ]);
            if ($result) {
                $this->id = $this->db->lastInsertId();
            }
            return $result;
        }
    }
    
    public static function buscarPorId($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT l.*, a.nombre as autor_nombre, c.nombre as categoria_nombre 
                FROM libros l 
                LEFT JOIN autores a ON l.autor_id = a.id 
                LEFT JOIN categorias c ON l.categoria_id = c.id 
                WHERE l.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        
        $libro = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($libro) {
            $obj = new self();
            $obj->id = $libro['id'];
            $obj->titulo = $libro['titulo'];
            $obj->isbn = $libro['isbn'];
            $obj->autor_id = $libro['autor_id'];
            $obj->categoria_id = $libro['categoria_id'];
            $obj->anio_publicacion = $libro['anio_publicacion'];
            $obj->editorial = $libro['editorial'];
            $obj->disponible = $libro['disponible'];
            return $obj;
        }
        return null;
    }
    
    public static function buscar($criterio, $tipo = 'titulo') {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT l.*, a.nombre as autor_nombre, c.nombre as categoria_nombre 
                FROM libros l 
                LEFT JOIN autores a ON l.autor_id = a.id 
                LEFT JOIN categorias c ON l.categoria_id = c.id 
                WHERE ";
        
        switch ($tipo) {
            case 'titulo':
                $sql .= "l.titulo LIKE ?";
                break;
            case 'autor':
                $sql .= "a.nombre LIKE ?";
                break;
            case 'categoria':
                $sql .= "c.nombre LIKE ?";
                break;
            default:
                $sql .= "l.titulo LIKE ?";
        }
        
        $sql .= " ORDER BY l.titulo";
        $stmt = $db->prepare($sql);
        $stmt->execute(["%$criterio%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function listarTodos() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT l.*, a.nombre as autor_nombre, c.nombre as categoria_nombre 
                FROM libros l 
                LEFT JOIN autores a ON l.autor_id = a.id 
                LEFT JOIN categorias c ON l.categoria_id = c.id 
                ORDER BY l.titulo";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function eliminar() {
        $sql = "DELETE FROM libros WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$this->id]);
    }
}
?>
