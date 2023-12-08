<?php
session_start();
$servername = "sql203.infinityfree.com";
$username = "if0_35435711";
$password = "hrtPcoQHzpRSu";
$dbname = "if0_35435711_users";

try {
    $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
}

if (isset($_POST['share'])) {
    $username = $_SESSION['username'];
    $description = $_POST['description'];
    $targetDirectory = "data/posts/";
    $fileName = uniqid() . "_" . basename($_FILES["fileToUpload"]["name"]);
    $targetPath = $targetDirectory . $fileName;
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetPath)) {
        try {
            $query = "INSERT INTO post (username,photo,description) VALUES (:username,:photo,:description)";
            $statement = $dbh->prepare($query);
            $statement->bindParam(':username', $username);
            $statement->bindParam(':photo', $fileName);
            $statement->bindParam(':description', $description);

            if ($statement->execute()) {
                header("Location:https://egoistsky.free.nf/user");
                exit(); // Kodun devam etmemesi için çıkış yap
            } else {
                echo "Veritabanına kaydedilirken bir hata oluştu.";
            }

        } catch (PDOException $e) {
            echo "Veritabanı hatası: " . $e->getMessage();
        }
    } else {
        echo "Dosya yüklenirken bir hata oluştu.";
    }
} else {
    header("Location:https://egoistsky.free.nf/user");
}
?>