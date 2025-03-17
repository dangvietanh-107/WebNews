<?php
if ($_FILES['upload']['name']) {
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES['upload']['name']);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_file)) {
        $response = [
            "uploaded" => 1,
            "fileName" => $file_name,
            "url" => $target_file
        ];
    } else {
        $response = ["uploaded" => 0, "error" => ["message" => "Lỗi khi upload ảnh"]];
    }

    echo json_encode($response);
}
?>
