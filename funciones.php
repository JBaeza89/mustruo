<?php
function sanitizar($str) {
    return htmlspecialchars(stripslashes(trim($str)));
}
function cambiarNombreAvatar($img, $login) {
    $nombre =  $login . "_avatar";
    $extension = substr($img, strrpos($img, "."));
    return $nombre . $extension;
}
function guardarAvatar($img) {
    $target_dir = "media/avatares/";
    $target_file = $target_dir . basename($img["name"]);
    move_uploaded_file($img["tmp_name"], $target_file);
}
function actualizarAvatar($imgAntigua, $imgNueva) {
    $target_file = "media/avatares/$imgAntigua";
    unlink($target_file);
    guardarAvatar($imgNueva);
}