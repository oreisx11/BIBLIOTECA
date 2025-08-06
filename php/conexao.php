<?php
  $hostname = "localhost";
  $bancodedados = "db_biblioteca";
  $usuario = "root";
  $senha = "";

  $mysqli = new mysqli($hostname, $usuario, $senha, $bancodedados);
  if ($mysqli->connect_errno) {
    echo "falha ao conectar: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
    exit();
  }else{
    echo "conectado ao banco de dados";
  }
?>
