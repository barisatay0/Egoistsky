<?php
session_start();
include 'connect.php';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = "SELECT * FROM user WHERE username = :username";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newUsername = $_POST['username'];
        $biography = $_POST['biography'];
        $email = $_POST['email'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        if (!empty($_FILES['profilephoto']['name'])) {
            $targetDirectory = "data/photos/";
            $randomFileName = uniqid() . '_' . $_FILES["profilephoto"]["name"];
            $targetFile = $targetDirectory . basename($randomFileName);

            if (move_uploaded_file($_FILES["profilephoto"]["tmp_name"], $targetFile)) {
                try {
                    $query = "UPDATE user SET username = :newUsername, biography = :biography, email = :email, firstname = :firstname, lastname = :lastname, profilephoto = :profilephoto WHERE username = :username";
                    $stmt = $dbh->prepare($query);
                    $stmt->bindParam(':newUsername', $newUsername);
                    $stmt->bindParam(':biography', $biography);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':firstname', $firstname);
                    $stmt->bindParam(':lastname', $lastname);
                    $stmt->bindParam(':profilephoto', $targetFile);
                    $stmt->bindParam(':username', $username);
                    $stmt->execute();

                    $_SESSION['username'] = $newUsername;

                    // Kullanıcı adını güncelledikten sonra post tablosundaki ilgili kullanıcıya ait gönderileri güncelle
                    $queryUpdatePosts = "UPDATE post SET username = :newUsername WHERE username = :oldUsername";
                    $stmtUpdatePosts = $dbh->prepare($queryUpdatePosts);
                    $stmtUpdatePosts->bindParam(':newUsername', $newUsername);
                    $stmtUpdatePosts->bindParam(':oldUsername', $username);
                    $stmtUpdatePosts->execute();

                    header("Location: profile.php");
                    exit();
                } catch (PDOException $e) {
                    echo "Connection Error: " . $e->getMessage();
                }
            } else {
                echo "Dosya yüklenirken bir hata oluştu.";
            }
        } else {
            try {
                $query = "UPDATE user SET username = :newUsername, biography = :biography, email = :email, firstname = :firstname, lastname = :lastname WHERE username = :username";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':newUsername', $newUsername);
                $stmt->bindParam(':biography', $biography);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':firstname', $firstname);
                $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                $_SESSION['username'] = $newUsername;

                $queryUpdatePosts = "UPDATE post SET username = :newUsername WHERE username = :oldUsername";
                $stmtUpdatePosts = $dbh->prepare($queryUpdatePosts);
                $stmtUpdatePosts->bindParam(':newUsername', $newUsername);
                $stmtUpdatePosts->bindParam(':oldUsername', $username);
                $stmtUpdatePosts->execute();

                header("Location: profile.php");
                exit();
            } catch (PDOException $e) {
                echo "Connection Error: " . $e->getMessage();
            }
        }
    }
} else {
    header("Location: login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egoistsky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="astronom.ico">
    <link rel="stylesheet" href="style.css">
    <style>
        .seacher {
            width: 33%;
        }

        .logo {
            width: 6%;
        }

        .formander {
            width: 25%;
        }

        .profilephoto {
            margin-left: 20px;
            width: 6.5rem;
            height: 6.5rem;
        }

        .buttona {
            width: 50%;

        }

        @media only screen and (max-width: 600px) {
            .seacher {
                width: 50%;
            }

            .logo {
                width: 10%;
                margin-left: -8%;
            }

            .formander {
                width: 50%;
                margin-top: 10%;
            }

            .profilephoto {
                margin-left: 18px;
                width: 4rem;
                height: 4rem;
            }

            .buttona {
                width: 70%;
                font-size: 10px;

            }

            @media only screen and (max-width: 400px) {
                .seacher {
                    width: 50%;
                }

                .logo {
                    width: 15%;
                    margin-left: -8%;
                }

                .formander {
                    width: 75%;
                }

                .profilephoto {
                    margin-left: 18px;
                    width: 4rem;
                    height: 4rem;
                }

                .buttona {
                    width: 100%;
                    font-size: 10px;

                }
            }
    </style>
</head>

<body class="bg-black">
    <a href="" class="mx-3 mt-2"></a>
    <div><a href="https://egoistsky.free.nf/user"
            class=" link-light link-underline-opacity-0 text-uppercase fst-italic fw-bolder"
            style="margin-left:12%;"><img class="border border-black border-3 rounded-circle logo" style=""
                src="astronomy.png" alt="logo"></a></div>
    <div class="position-absolute top-0 start-50 translate-middle mt-4 searcher" style="">
        <form name="searcher" method="post" action="search.php">
            <input type="search" id="searchInput" name="search" placeholder="Search..." class="form-control">
        </form>
        <div id="searchResults"></div>
    </div>
    <form class="formander text-white position-absolute top-50 start-50 translate-middle mt-4"
        enctype="multipart/form-data" method="post">
        <div class="mb-4">
            <label for="exampleInputEmail1" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"
                value="<?php echo isset($username) ? $username : ''; ?>">
        </div>
        <div class="mb-4">
            <label for="exampleInputEmail1" class="form-label">Biography</label>
            <input type="text" class="form-control border border-black" id="formFile" aria-describedby="emailHelp"
                name="biography" value="<?php echo isset($user['biography']) ? $user['biography'] : ''; ?>">
        </div>
        <div class="mb-4">
            <label for="exampleInputEmail1" class="form-label">Email</label>
            <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="email"
                value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>">
        </div>
        <div class="mb-4">
            <label for="exampleInputEmail1" class="form-label">First Name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"
                name="firstname" value="<?php echo isset($user['firstname']) ? $user['firstname'] : ''; ?>">
        </div>
        <div class="mb-4">
            <label for="exampleInputEmail1" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" name="lastname"
                value="<?php echo isset($user['lastname']) ? $user['lastname'] : ''; ?>">
        </div>
        <div class="mb-4">
            <label for="exampleInputEmail1" class="form-label">Profile Photo</label>
            <input type="text" name="photofolder"
                value="<?php echo isset($user['profilephoto']) ? $user['profilephoto'] : ''; ?>"
                style="visibility: hidden;">
            <input type="file" class="form-control border border-black" id="formFile" aria-describedby="emailHelp"
                name="profilephoto">
        </div>
        <button type="submit" class="btn btn-success w-100">Save</button>
    </form>
    <div class="mb-4 position-absolute translate-middle-y end-0 w-25 text-center" style="">
        <a href="profile.php"><img class="rounded-circle border border-black border-2 mt-1 profilephoto"
                src="<?php echo isset($user['profilephoto']) ? $user['profilephoto'] : ''; ?>" style=""></a>
        <br>
        <a href="password.php"><button class="btn btn-outline-light mt-4 buttona">Change Password</button></a>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
    integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
    crossorigin="anonymous"></script>

<script>
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    searchInput.addEventListener('input', function () {
        const searchValue = this.value;
        if (searchValue === '') {
            searchResults.innerHTML = '';
            return;
        }
        fetch(`search.php?search_query=${searchValue}`)
            .then(response => response.text())
            .then(data => {
                searchResults.innerHTML = data;
            })
            .catch(error => {
                console.error('Search Error:', error);
            });
    });
</script>

</html>