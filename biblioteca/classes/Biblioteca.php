<?php
class Biblioteca {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function agregarLibro($datos) {
        $libro = new Libro();
        $libro->setTitulo($datos['titulo']);
        $libro->setIsbn($datos['isbn']);
        $libro->setAutorId($datos['autor_id']);
        $libro->setCategoriaId($datos['categoria_id']);
        $libro->setAnioPublicacion($datos['anio_publicacion']);
        $libro->setEditorial($datos['editorial']);
        
        return $libro->guardar();
    }
    
    public function editarLibro($id, $datos) {
        $libro = Libro::buscarPorId($id);
        if ($libro) {
            $libro->setTitulo($datos['titulo']);
            $libro->setIsbn($datos['isbn']);
            $libro->setAutorId($datos['autor_id']);
            $libro->setCategoriaId($datos['categoria_id']);
            $libro->setAnioPublicacion($datos['anio_publicacion']);
            $libro->setEditorial($datos['editorial']);
            
            return $libro->guardar();
        }
        return false;
    }
    
    public function eliminarLibro($id) {
        $libro = Libro::buscarPorId($id);
        if ($libro) {
            return $libro->eliminar();
        }
        return false;
    }
    
    public function buscarLibros($criterio, $tipo = 'titulo') {
        return Libro::buscar($criterio, $tipo);
    }
    
    public function listarLibros() {
        return Libro::listarTodos();
    }
    
    public function prestarLibro($libro_id, $usuario, $fecha_devolucion = null) {
        $prestamo = new Prestamo();
        $prestamo->setLibroId($libro_id);
        $prestamo->setUsuario($usuario);
        
        if ($fecha_devolucion) {
            $prestamo->setFechaDevolucion($fecha_devolucion);
        } else {
            $fecha_devolucion = date('Y-m-d', strtotime('+15 days'));
            $prestamo->setFechaDevolucion($fecha_devolucion);
        }
        
        return $prestamo->realizarPrestamo();
    }
    
    public function devolverLibro($prestamo_id) {
        $prestamo_data = Prestamo::buscarPorId($prestamo_id);
        if ($prestamo_data) {
            $prestamo = new Prestamo();
            $prestamo->setLibroId($prestamo_data['libro_id']);
            $prestamo->setUsuario($prestamo_data['usuario']);
            $prestamo->setFechaPrestamo($prestamo_data['fecha_prestamo']);
            $prestamo->setFechaDevolucion($prestamo_data['fecha_devolucion']);
            $prestamo->setDevuelto($prestamo_data['devuelto']);
            $prestamo->id = $prestamo_data['id'];
            
            return $prestamo->devolverLibro();
        }
        return false;
    }
    
    public function listarPrestamosActivos() {
        return Prestamo::listarPrestamosActivos();
    }
    
    public function agregarAutor($nombre, $nacionalidad = null, $fecha_nacimiento = null) {
        $autor = new Autor();
        $autor->setNombre($nombre);
        $autor->setNacionalidad($nacionalidad);
        $autor->setFechaNacimiento($fecha_nacimiento);
        
        return $autor->guardar();
    }
    
    public function listarAutores() {
        return Autor::listarTodos();
    }
    
    public function agregarCategoria($nombre, $descripcion = null) {
        $categoria = new Categoria();
        $categoria->setNombre($nombre);
        $categoria->setDescripcion($descripcion);
        
        return $categoria->guardar();
    }
    
    public function listarCategorias() {
        return Categoria::listarTodas();
    }
}
?>