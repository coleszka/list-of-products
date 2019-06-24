<?php
require_once( 'PSWebServiceLibrary.php' );

try {
    // creating web service access
    $webService = new PrestaShopWebservice('localhost/presta/', 'BGBZSDXCY66WZD3T8EVLCRZPG3DHJL34', false);

    // call to retrieve all customers
    $xml = $webService->get(array('resource' => 'products', 'display'  => 'full', 'limit'    => '20'));
    $stock = $webService->get(array('resource' => 'stock_availables', 'display'  => 'full'));
    $resources = $xml->products->product;
    $sources = $stock->stock_availables->stock_available;
}
catch (PrestaShopWebserviceException $ex) {
    // Shows a message related to the error
    echo 'Other error: <br />' . $ex->getMessage();
}

$prod = array();
//$x = $stock->stock_availables->stock_available[0]->quantity."<br>";

//getting id's, names and put into array
foreach ($resources as $resource) {
    $prod[] = array('id' => (int) $resource->id, 'name' => (string) $resource->name->language,
        'idStock' => (int) $resource->associations->stock_availables->stock_available->id);
}

//getting quantity and put into array data about products
foreach ($sources as $source) {
    if (array_search($source->id, array_column($prod, 'idStock'))) {
        //echo array_search($source->id, array_column($prod, 'idStock'))." : ".$source->quantity."<br>";
        $prod[array_search($source->id, array_column($prod, 'idStock'))] += ['stock' => ((int) $source->quantity)]; // - ($source->out_of_stock)
    }
}
echo "<pre>";
var_dump($prod);
echo "</pre>";

//display products and forms
foreach ($prod as $prods)
{
    echo '<form method="POST" action="list_of_products.php'.'">';
    if ($prods['name'] != ''){
        echo 'Nazwa: '.$prods['name']."<br>";
    }
    if (isset($prods['stock'])){
        echo 'Ilość sztuk na stanie: <input type="text" name="'.$prods['idStock'].'" value="'.$prods['stock'].'"><br>';
        echo " <input type=\"submit\" value=\"Submit\"></form>";
        $_POST['temp'] = TRUE;
    }
}

//var_dump($_POST);

//update the resource
if (isset($_POST['temp']))
{
    try {
        foreach ($_POST as $key => $value) {

            $opt = array('resource' => 'stock_availables');

            // Define the resource id to modify
            $opt['id'] = $key;

            // Call the web service, recuperate the XML file
            $xml = $webService->get($opt);

            // Retrieve resource elements in a variable (table)
            $resources = $xml->children()->children();
            echo "<pre>";
            var_dump($resources);

            echo "</pre>";
            echo $value;
            $resources->quantity = $value;

            // Resource definition
            $opt = array('resource' => 'stock_availables');

            //XML file definition
            $opt['putXml'] = $xml->asXML();

            // Definition of ID to modify
            $opt['id'] = $key;

            // Calling asXML() returns a string corresponding to the file
            $xml = $webService->edit($opt);
            echo $key . ":" . $value . "<br>";
            header("Location: ");
        }
        unset($_POST['temp']);
    }
    catch (PrestaShopWebserviceException $ex)
    {
        // Here we are dealing with errors
        $trace = $ex->getTrace();
        if ($trace[0]['args'][0] == 404) echo 'Bad ID';
        else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
        else echo 'Other error<br />'.$ex->getMessage();
    }
}
echo "<pre>";
//var_dump($update);
//print_r($xml);
//var_dump($prod);
echo "</pre>";



?>