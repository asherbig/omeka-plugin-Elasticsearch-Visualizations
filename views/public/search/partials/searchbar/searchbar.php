<!DOCTYPE html>
<html>
  
  <head>
    
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-colors-flat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script type='text/javascript' src='../../../javascripts/searchBarFunctions.js'></script>
    
  </head>

  <body>
  
    <div class="w3-container w3-flat-sun-flower">
      <h1>Big Header</h1>
    </div>
    
    <div class="w3-cell-row w3-flat-midnight-blue">
      
      <?php 
      $query = array(
          'facets' => []
      );
      include '../seachBar.php';
      ?>
      
    </div>
    
    <button id="coolButton"
            class="w3-button"
            onclick="myFunction()">
            Click Here
    </button>
    
  </body>
  
</html>
