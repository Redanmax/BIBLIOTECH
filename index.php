<?php
// Incluir todas las clases
require_once 'classes/Database.php';
require_once 'classes/Autor.php';
require_once 'classes/Categoria.php';
require_once 'classes/Libro.php';
require_once 'classes/Prestamo.php';
require_once 'classes/Biblioteca.php';

// Crear instancia de la biblioteca
$biblioteca = new Biblioteca();

// Procesar formularios
if ($_POST) {
    if (isset($_POST['agregar_libro'])) {
        $datos = [
            'titulo' => $_POST['titulo'],
            'isbn' => $_POST['isbn'],
            'autor_id' => $_POST['autor_id'],
            'categoria_id' => $_POST['categoria_id'],
            'anio_publicacion' => $_POST['anio_publicacion'],
            'editorial' => $_POST['editorial']
        ];
        if ($biblioteca->agregarLibro($datos)) {
            $mensaje = "Libro agregado correctamente";
        }
        header("Location: index.php");
        exit;
    }
    
    if (isset($_POST['prestar_libro'])) {
        if ($biblioteca->prestarLibro($_POST['libro_id'], $_POST['usuario'])) {
            $mensaje = "Préstamo realizado correctamente";
        } else {
            $error = "No se pudo realizar el préstamo. El libro no está disponible.";
        }
        header("Location: index.php");
        exit;
    }
    
    if (isset($_POST['devolver_libro'])) {
        if ($biblioteca->devolverLibro($_POST['prestamo_id'])) {
            $mensaje = "Libro devuelto correctamente";
        }
        header("Location: index.php");
        exit;
    }
    
    if (isset($_POST['buscar'])) {
        $resultados_busqueda = $biblioteca->buscarLibros($_POST['criterio'], $_POST['tipo_busqueda']);
    }
}

// Obtener datos para los selects
$autores = $biblioteca->listarAutores();
$categorias = $biblioteca->listarCategorias();
$libros = $biblioteca->listarLibros();
$prestamos_activos = $biblioteca->listarPrestamosActivos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin: 10px 0; }
        label { display: inline-block; width: 150px; font-weight: bold; }
        input, select { padding: 8px; width: 250px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background-color: #45a049; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Sistema de Gestión de Biblioteca</h1>
        
        <?php if (isset($mensaje)): ?>
            <div class="success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Búsqueda -->
        <div class="section">
            <h2>🔍 Buscar Libros</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Buscar por:</label>
                    <select name="tipo_busqueda">
                        <option value="titulo">Título</option>
                        <option value="autor">Autor</option>
                        <option value="categoria">Categoría</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Término de búsqueda:</label>
                    <input type="text" name="criterio" required>
                </div>
                <button type="submit" name="buscar">Buscar</button>
            </form>
            
            <?php if (isset($resultados_busqueda)): ?>
                <h3>Resultados de la búsqueda:</h3>
                <?php if (count($resultados_busqueda) > 0): ?>
                    <table>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Categoría</th>
                            <th>Disponible</th>
                        </tr>
                        <?php foreach ($resultados_busqueda as $libro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($libro['autor_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($libro['categoria_nombre']); ?></td>
                            <td><?php echo $libro['disponible'] ? '✅ Sí' : '❌ No'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>No se encontraron resultados.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Agregar Libro -->
        <div class="section">
            <h2>➕ Agregar Nuevo Libro</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Título:</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="form-group">
                    <label>ISBN:</label>
                    <input type="text" name="isbn">
                </div>
                <div class="form-group">
                    <label>Autor:</label>
                    <select name="autor_id" required>
                        <option value="">Seleccionar autor</option>
                        <?php foreach ($autores as $autor): ?>
                            <option value="<?php echo $autor['id']; ?>"><?php echo htmlspecialchars($autor['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="categoria_id" required>
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Año Publicación:</label>
                    <input type="number" name="anio_publicacion">
                </div>
                <div class="form-group">
                    <label>Editorial:</label>
                    <input type="text" name="editorial">
                </div>
                <button type="submit" name="agregar_libro">Agregar Libro</button>
            </form>
        </div>
        
        <!-- Lista de Libros -->
        <div class="section">
            <h2>📖 Libros en la Biblioteca</h2>
            <?php if (count($libros) > 0): ?>
                <table>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Año</th>
                        <th>Editorial</th>
                        <th>Disponible</th>
                    </tr>
                    <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($libro['autor_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($libro['categoria_nombre']); ?></td>
                        <td><?php echo $libro['anio_publicacion']; ?></td>
                        <td><?php echo htmlspecialchars($libro['editorial']); ?></td>
                        <td><?php echo $libro['disponible'] ? '✅ Sí' : '❌ No'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No hay libros en la biblioteca.</p>
            <?php endif; ?>
        </div>
        
        <!-- Préstamos -->
        <div class="section">
            <h2>📋 Préstamos Activos</h2>
            
            <!-- Formulario para prestar libro -->
            <h3>Prestar Libro</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Libro:</label>
                    <select name="libro_id" required>
                        <option value="">Seleccionar libro</option>
                        <?php foreach ($libros as $libro): ?>
                            <?php if ($libro['disponible']): ?>
                                <option value="<?php echo $libro['id']; ?>"><?php echo htmlspecialchars($libro['titulo']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Usuario:</label>
                    <input type="text" name="usuario" required placeholder="Nombre del usuario">
                </div>
                <button type="submit" name="prestar_libro">Realizar Préstamo</button>
            </form>
            
            <!-- Lista de préstamos activos -->
            <h3>Préstamos en Curso</h3>
            <?php if (count($prestamos_activos) > 0): ?>
                <table>
                    <tr>
                        <th>Libro</th>
                        <th>Usuario</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Devolución</th>
                        <th>Acción</th>
                    </tr>
                    <?php foreach ($prestamos_activos as $prestamo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></td>
                        <td><?php echo htmlspecialchars($prestamo['usuario']); ?></td>
                        <td><?php echo $prestamo['fecha_prestamo']; ?></td>
                        <td><?php echo $prestamo['fecha_devolucion']; ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="prestamo_id" value="<?php echo $prestamo['id']; ?>">
                                <button type="submit" name="devolver_libro" style="background-color: #007bff;">Devolver</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No hay préstamos activos.</p>
            <?php endif; ?>
        </div>
        
        <!-- Información del Sistema -->
        <div class="section">
            <h2>ℹ️ Información del Sistema</h2>
            <p><strong>Total libros:</strong> <?php echo count($libros); ?></p>
            <p><strong>Libros disponibles:</strong> <?php echo count(array_filter($libros, function($libro) { return $libro['disponible']; })); ?></p>
            <p><strong>Préstamos activos:</strong> <?php echo count($prestamos_activos); ?></p>
            <p><strong>Autores registrados:</strong> <?php echo count($autores); ?></p>
            <p><strong>Categorías:</strong> <?php echo count($categorias); ?></p>
        </div>
    </div>
</body>
</html>
