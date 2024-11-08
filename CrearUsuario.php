<?php
// Establece el tipo de contenido de la respuesta a JSON
header('Content-Type: application/json');

// Conecta a la base de datos (ajusta los valores de conexión)
$host = "localhost";
$dbname = "siagemi"; // Nombre de tu base de datos en XAMPP
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error de conexión a la base de datos"]);
    exit();
}

// Variables de respuesta
$response = [
    "status" => "error",
    "message" => "No se pudieron registrar los datos",
    "errors" => []
];

// Recibe los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$ap_paterno = $_POST['ap_paterno'] ?? '';
$ap_materno = $_POST['ap_materno'] ?? '';
$especialidad = $_POST['especialidad'] ?? '';
$matricula = $_POST['matricula'] ?? '';
$rol = $_POST['rol'] ?? '';
$area = $_POST['area'] ?? '';
$cargoArea = $_POST['cargoArea'] ?? '';
$grado = $_POST['grado'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$años_trabajo = $_POST['años_trabajo'] ?? '';
$contraseña = $_POST['contraseña'] ?? '';
$nom_usuario = $_POST['nom_usuario'] ?? '';
$image = $_FILES['image'] ?? null;

// Validaciones
if (empty($nombre) || !preg_match("/^[a-zA-Z]+$/", $nombre)) {
    $response['errors']['nombre'] = "El nombre es obligatorio y solo debe contener letras.";
}
if (empty($ap_paterno) || !preg_match("/^[a-zA-Z]+$/", $ap_paterno)) {
    $response['errors']['ap_paterno'] = "El apellido paterno es obligatorio y solo debe contener letras.";
}
// Añadir aquí el resto de las validaciones necesarias para cada campo

// Verifica si hay errores
if (!empty($response['errors'])) {
    echo json_encode($response);
    exit();
}

// Verifica y crea la carpeta uploads si no existe
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Procesa la imagen si se subió
$foto_perfil = null;
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $imageName = uniqid() . '-' . basename($image['name']);
    $imagePath = $uploadDir . $imageName;

    if (move_uploaded_file($image['tmp_name'], $imagePath)) {
        $foto_perfil = $imageName; // Guarda solo el nombre para almacenar en la base de datos
    } else {
        $response['errors']['image'] = "No se pudo subir la imagen.";
        echo json_encode($response);
        exit();
    }
}

// Inserta los datos en la base de datos si no hay errores
try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, ap_paterno, ap_materno, especialidad, matricula, id_rol, id_area, cargo_area, grado, telefono, años_trabajo, contraseña, nom_usuario, foto_perfil)
                           VALUES (:nombre, :ap_paterno, :ap_materno, :especialidad, :matricula, :id_rol, :id_area, :cargo_area, :grado, :telefono, :años_trabajo, :contraseña, :nom_usuario, :foto_perfil)");

    // Asocia los parámetros con los valores recibidos
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':ap_paterno', $ap_paterno);
    $stmt->bindParam(':ap_materno', $ap_materno);
    $stmt->bindParam(':especialidad', $especialidad);
    $stmt->bindParam(':matricula', $matricula);
    $stmt->bindParam(':id_rol', $rol); // Asegúrate de que 'rol' coincide con id_rol en la base de datos
    $stmt->bindParam(':id_area', $area); // Asegúrate de que 'area' coincide con id_area en la base de datos
    $stmt->bindParam(':cargo_area', $cargoArea);
    $stmt->bindParam(':grado', $grado);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':años_trabajo', $años_trabajo);
    $stmt->bindParam(':contraseña', password_hash($contraseña, PASSWORD_DEFAULT)); // Hashea la contraseña
    $stmt->bindParam(':nom_usuario', $nom_usuario);
    $stmt->bindParam(':foto_perfil', $foto_perfil);

    $stmt->execute();

    $response['status'] = "success";
    $response['message'] = "Usuario registrado exitosamente.";
} catch (PDOException $e) {
    $response['message'] = "Error al registrar el usuario: " . $e->getMessage();
}

// Envía la respuesta en formato JSON
echo json_encode($response);
?>
