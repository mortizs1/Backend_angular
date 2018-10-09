<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get("/pruebas", function() use($app){
    echo "Hola Mundo desde Slim PHP";
});
$db = new mysqli('localhost', 'root', '', 'curso_angular4');

$app->get("/GermanGAY", function() use($app, $db){
    echo "GERMAN ES REEEEEEEEEE GAY!";
    var_dump("db");
});


//LISTAR TODOS LOS PRODUCTOS
$app->get('/productos', function() use($db, $app){
  $sql = 'SELECT * FROM productos ORDER BY id DESC;';
  $query = $db->query($sql);
  $productos = array();

  while($producto = $query->fetch_assoc()) {
    $productos[] = $producto;
  }

  $result = array(
    'status'=> 'Success',
    'code' => 200,
    'data' => $productos
  );

  echo json_encode($result);
});

//DEVOLVER UN SOLO PRODUCTOS
$app->get('/producto/:id', function($id) use($db, $app){
    $sql = 'SELECT * FROM productos WHERE id = '. $id;
    $query = $db->query($sql);

    $result = array(
      'status'=> 'error',
      'code' => 404,
      'message' => 'producto no disponible'
    );

    if($query->num_rows == 1){
      $producto = $query->fetch_assoc();

      $result = array(
        'status' => 'Success',
        'code' => 200,
        'data' => $producto
      );
      }

    echo json_encode($result);
});

//ELIMINAR UN PRODUCTO

$app->get('/delete-producto/:id', function($id) use($db, $app){
    $sql ='DELETE FROM productos WHERE id = ' .$id;
    $query = $db->query($sql);

    if($query){
      $result = array(
        'status' => 'Success',
        'code' => 200,
        'message' => 'El producto se elimino correctamente'
      );
    }else{
      $result = array(
        'status' => 'Error',
        'code' => 404,
        'message' => 'No se eliminó el producto'
      );
    }

    echo json_encode($result);

});

//ACTUALIZAR UN PRODUCTO

$app->post('/update-producto/:id', function($id) use($db, $app){
  $json = $app->request->post('json');
  $data = json_decode($json, true);
  $sql ="UPDATE productos SET ".
        "nombre ='{$data["nombre"]}',".
        "descripcion ='{$data["descripcion"]}',".
        "precio ='{$data["precio"]}' WHERE id = {$id}";
  $query = $db->query($sql);

  if($query){
    $result = array(
      'status' => 'Success',
      'code' => 200,
      'message' => 'El producto se actualizo correctamente'
    );
  }else{
    $result = array(
      'status' => 'Error',
      'code' => 404,
      'message' => 'No se pudo actualizar el producto'
    );
  }

  echo json_encode($result);

});

//SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function() use($db, $app){
	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'El archivo no ha podido subirse'
	);

	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();
		$upload = $piramideUploader->upload('image', 'uploads', 'Uploads', array('image/jpeg', 'image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile();
		$file_name = $file['complete_name'];

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> 'El archivo no ha podido subirse',
				'state'		=> $upload,
				'file_name'	=> $file
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});
//GUARDAR PRODUCTOS
$app->post('/productos', function() use($app, $db){
  $json = $app->request->post('json');
  $data = json_decode($json, true);

  if(!isset($data['descripcion'])){
    $data['descripcion']=null;
  }

  if(!isset($data['imagen'])){
    $data['imagen']=null;
  }

  $query =  "INSERT INTO productos VALUES(NULL,".
            "'{$data['nombre']}',".
            "'{$data['descripcion']}',".
            "'{$data['precio']}',".
            "'{$data['imagen']}'".
            ");";

            $insert = $db->query($query);

            $result = array(
              'status'=> 'Error',
              'code' => 404,
              'message' => 'El producto no se ha creado correctamente'
            );

          if($insert){
            $result = array(
              'status'=> 'Success',
              'code' => 200,
              'message' => 'Producto creado correctamente'
            );
          }

          echo json_encode($result);
});

$app->run();
