<?php 
    
    require_once 'vendor/autoload.php';
    require_once "./random_string.php";

    use MicrosoftAzure\Storage\Blob\BlobRestProxy;
    use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
    use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
    use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
    use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

    # Mengatur instance dari Azure::Storage::Client
    $connectionString = "DefaultEndpointsProtocol=https;AccountName=naufalstorage;AccountKey=mf3ynOXu01mTBjb/eIf5t+utfkNa388I8cyLsFq3OB7nP4WniISzDNrC8TYVcN9AI18P54LlS7xraznlS7uetA==;EndpointSuffix=core.windows.net";
 
    // Membuat blob client.
    $blobClient = BlobRestProxy::createBlobService($connectionString);
 
    # Membuat BlobService yang merepresentasikan Blob service untuk storage account
    $createContainerOptions = new CreateContainerOptions();
 
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
 
    // Menetapkan metadata dari container.
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");
 
    //$containerName = "blockblobs".generateRandomString();
    $containerName = "naufal";

    try    {
        // Membuat container.
        $blobClient->createContainer($containerName, $createContainerOptions);
    } catch(ServiceException $e){
         $code = $e->getCode();
         $error_message = $e->getMessage();
         echo $code.": ".$error_message."<br />";
    }

    if (isset($_POST['submit'])) {
        $fileToUpload = strtolower($_FILES["fileToUpload"]["name"]);
        $content = fopen($_FILES["fileToUpload"]["tmp_name"], "r");
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        header("Location: index.php");
    }

    $listBlobsOptions = new ListBlobsOptions();
    $listBlobsOptions->setPrefix("");
    $result = $blobClient->listBlobs($containerName, $listBlobsOptions);

?>

<!DOCTYPE html>
<html>
 <head>
 <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <title>Submission Azure</title>
  </head>
<body>
	<main role="main" class="container">
    		<div class="limiter"> <br>
        		<h1>Image Analyzer</h1>
				<p class="lead">Pilih foto dari komputer yang ingin Anda analisis. lalu klik tombol <b>Upload</b> <br>Untuk memulai proses analisis foto, pilih tombol <b>Analyze!</b> pada pilihan gambar di masing-masing daftar.</p>
				<span class="border-top my-3"></span>
			</div>
		<div>
            <form class="d-flex justify-content-lefr" action="index.php" method="post" enctype="multipart/form-data">
                <div class="btn-group"> 
                    <div class="buttonRow">
                        <input class="btn btn-warning btn-small" type="file" name="fileToUpload" accept=".jpeg,.jpg,.png" required="">
                        <br></br>
                        <input class="btn btn-success btn-small" type="submit" name="submit" value="Upload">
                    </div>
                </div>	
			</form>
        </div>
        
		<br>
        <br>

        <div> 
            <div class="table-wrapper">
                <div class="table-title"> 
                    <h4>Total Files : <?php echo sizeof($result->getBlobs())?></h4>
                </div>
            <div class="well">
                <table class='table table-hover'>
                    <thead>
                        <tr >
                            <th >File Name</th>
                            <th >File URL</th>
                            <th >Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        do {
                            foreach ($result->getBlobs() as $blob)
                            {
                                ?>
                                <tr>
                                    <td><?php echo $blob->getName() ?></td>
                                    <td><?php echo $blob->getUrl() ?></td>
                                    <td>
                                        <form action="computerVision.php" method="post">
                                            <input type="hidden" name="url" value="<?php echo $blob->getUrl()?>">
                                            <input type="submit" name="submit" value="Analyze!" class="btn btn-primary">
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
                        } while($result->getContinuationToken());
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="https://getbootstrap.com/docs/4.0/assets/js/vendor/popper.min.js"></script>
    <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js"></script>

  </body>
</html>
