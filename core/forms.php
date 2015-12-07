<?php

class Forms {

    public function UploadApk() {
        echo "<form method='POST' action='' id='submitApk' enctype='multipart/form-data' class='uploadForms'>";
        echo "<h2>Upload Application APK</h2>";
        echo "<input type='text' name='app_name' placeholder='Write your app name' required><br/>";
        echo "<textarea name='app_description' placeholder='Write your app description' required></textarea><br/>";
        echo "<input type='text' name='app_dev' placeholder='Write your dev name' required><br/>";
        echo "<select name='app_category'>";
        $this->MakeCategorys();
        echo "</select><br/>";
        echo "App APK: <input type='file' name='app_apk' required><br/>";        
        echo "<input type='submit' name='submit' value='Upload App' /><br/>";
        echo "</form>";
        

        if(isset($_POST["submit"]) == "Upload App"){
            if(isset($_POST["app_name"])){
                $app_name = $_POST["app_name"];
            }
            if(isset($_POST["app_dev"])){
                $app_dev = $_POST["app_dev"];
            }
            if(isset($_POST["app_category"])){
                $app_category = $_POST["app_category"];
            }
            if(isset($_POST["app_description"])){
                $app_description = $_POST["app_description"];
            }
            if(isset($_FILES["app_apk"])){
                $app_apk = $_FILES["app_apk"];
            }

            if (strpos($app_apk["name"],".apk") !== false){
                if($app_apk["size"] > 0){
                    $Upload = new UploadFiles();

                    if($_SESSION["app_id"] == NULL){
                        $apk = $Upload->Apk($app_apk);
                    } else {
                        $apk = NULL;
                        echo "You are process another upload please finish it, go in next step<br/>";
                    }

                    if($apk != NULL){
                        $Dates = new Dates();
                        $today = $Dates->DatabaseToday();

                        $Databases = new Database();
                        $db = $Databases->Open();

                        $checkIfExist = mysqli_num_rows(mysqli_query($db, "SELECT app_id FROM apps WHERE app_id = ".$_SESSION["app_id"]));

                        if($checkIfExist == 0){                    
                            mysqli_query($db, "INSERT INTO apps (name,description,apk,dev,category,date) 
                                               VALUES ('$app_name','$app_description','$apk','$app_dev','$app_category','$today')");
                            $_SESSION["app_name"] = $app_name;
                            $_SESSION["app_apk"] = $apk;
                            $_SESSION["app_id"] = mysqli_insert_id($db);
                        } else {
                            echo "app_id Exists, go in next step<br/>";
                        }
                        $Databases->Close($db);
                    }
                }
            }        
        }        

    }
    
    public function UploadImages() {
        echo "<form method='POST' action='' id='submitScreenshot' enctype='multipart/form-data' class='uploadForms'>";
        if(isset($_SESSION["app_name"])){
            $app_name = $_SESSION["app_name"];
        } else {
            $app_name = NULL;
        }
        echo "<h2>".$app_name."</h2>";
        echo "<h2>Upload Media Files</h2>";
        echo "App Icon: <input type='file' name='icon' required><br/>";
        echo "ScreenShot 1: <input type='file' name='image1' required><br/>";
        echo "ScreenShot 2: <input type='file' name='image2'><br/>";
        echo "ScreenShot 3: <input type='file' name='image3'><br/>";
        echo "ScreenShot 4: <input type='file' name='image4'><br/>";
        echo "Video 1: <input type='text' name='video1' placeholder='App video url from Youtube - Vimeo'><br/>";
        echo "Video 2: <input type='text' name='video2' placeholder='App video url from Youtube - Vimeo'><br/>";        
        echo "<input type='submit' name='submit' value='Upload Media' /><br/>";
        echo "</form>";
        
        if(isset($_POST["submit"]) == "Upload Media"){
            $app_id = $_SESSION["app_id"];
            
            $Upload = new UploadFiles();
            $Dates = new Dates();
            $today = $Dates->DatabaseToday();

            $Databases = new Database();
            $db = $Databases->Open();
            
            if(isset($_POST["video1"])){
                $video1 = $_POST["video1"];
                if($video1 != NULL){
                    mysqli_query($db, "INSERT INTO media (app_id,type,url,date) VALUES ($app_id,'video','$video1','$today')");
                }
            }
            if(isset($_POST["video2"])){
                $video2 = $_POST["video2"];
                if($video2 != NULL){
                    mysqli_query($db, "INSERT INTO media (app_id,type,url,date) VALUES ($app_id,'video','$video2','$today')");
                }                
            }            
            foreach ($_FILES as $key => $value){
                if (strpos($key,"image") !== false){
                    if($value["size"] > 0){
                        $image = $Upload->Image($value);
                        if($image != NULL){
                            mysqli_query($db, "INSERT INTO media (app_id,type,url,date) VALUES ($app_id,'screenshot','$image','$today')");
                        }                        
                    }
                }
                if (strpos($key,"icon") !== false){
                    if($value["size"] > 0){
                        $image = $Upload->Image($value);
                        if($image != NULL){
                            mysqli_query($db, "INSERT INTO media (app_id,type,url,date) VALUES ($app_id,'icon','$image','$today')");
                        }                        
                    }
                }
                $image = NULL;
            }
        }
    }
    
    public function MakeCategorys(){
        $Databases = new Database();
        $db = $Databases->Open();
        
        $query = mysqli_query($db,"SELECT * FROM `categorys` ORDER BY `order`");
        
        while($categorys = mysqli_fetch_array($query)){
            echo sprintf("<option value='%s'>%s</option>",$categorys[cat_id],$categorys[name]);
        }
        $Databases->Close($db);
    }
}

