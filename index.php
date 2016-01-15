
<!DOCTYPE HTML>
<html>
<head>
<title>Flipazon Online Product Comparison</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
<script src="js/jquery.min.js"></script> 
<!-- start top_js_button -->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
   <script type="text/javascript">
		jQuery(document).ready(function($) {
			$(".scroll").click(function(event){		
				event.preventDefault();
				$('html,body').animate({scrollTop:$(this.hash).offset().top},1200);
			});
		});
	</script>
</head>
<body>
<!-- start header -->
<div class="header_bg">
<div class="wrap">
	<div class="header">
		<div class="logo">
			<a href="index.html"><img src="images/logo.png" alt=""/> </a>
		</div>
		<div class="h_icon">
		
		</div>
		<div class="h_search">
    		<form action="index.php" method="post">
    			<input type="text" name="name">
    			<input type="submit" value="">
    		</form>
		</div>
		<div class="clear"></div>
	</div>
</div>
</div>

<!-- start main -->
<div class="main_bg">
<div class="wrap">	
	<div class="main">
		<h2 class="style top">SEARCH FOR A PRODUCT</h2>
		<!-- start grids_of_3 -->
		
		


<?php
error_reporting(0);



if ("cli" !== PHP_SAPI)
{
    echo "<pre>";
}

if (is_file('sampleSettings.php'))
{
  include 'sampleSettings.php';
}

defined('AWS_API_KEY') or define('AWS_API_KEY', 'AKIAJ3SXK26ABGUYBAUA');
defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY', 'nSjL1iMFZFVVu2jnwW0zT0wxw3t79hsWUEbYbVsr');
defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'productcompar-21');

require '/lib/AmazonECS.class.php';
//Include the class.
include "clusterdev.flipkart-api.php";

//Replace <affiliate-id> and <access-token> with the correct values
$flipkart = new \clusterdev\Flipkart("ananthakr", "8dc956763a5a45ffb62258125a2845c5", "json");
$query=$_POST["name"];



$query2= str_replace(' ', '+', $query);


$dotd_url = 'https://affiliate-api.flipkart.net/affiliate/search/json?query='.$query2.'&resultCount=9';




	//Call the API using the URL.
	$details = $flipkart->call_url($dotd_url);




	if(!$details){
		echo 'Error: Could not retrieve products list.';
		exit();
	}

	//The response is expected to be JSON. Decode it into associative arrays.
	$details = json_decode($details, TRUE);

	
	$products = $details['productInfoList'];


	echo "<table border=2 cellpadding=10 cellspacing=1 style='text-align:center'>";
	$count = 0;
	$end = 1;
	$i=1;
	try
			{
		
	$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY,'in',AWS_ASSOCIATE_TAG);
	$amazonEcs->associateTag(AWS_ASSOCIATE_TAG);
	$response = $amazonEcs->country('in')->category('All')->search($query);
	
	
	//Make sure there are products in the list.
	if(count($products) > 0){
		foreach ($products as $product) {

			//Hide out-of-stock items unless requested.
			if($count%2==0)
			{
					$inStock = $product['productBaseInfo']['productAttributes']['inStock'];
					if(!$inStock )
						continue;
					
					//Keep count.
					

					//The API returns these values nested inside the array.
					//Only image, price, url and title are used in this demo
					$productId = $product['productBaseInfo']['productIdentifier']['productId'];
					$title = $product['productBaseInfo']['productAttributes']['title'];
					$productDescription = $product['productBaseInfo']['productAttributes']['productDescription'];

					//We take the 200x200 image, there are other sizes too.
					$productImage = array_key_exists('200x200', $product['productBaseInfo']['productAttributes']['imageUrls'])?$product['productBaseInfo']['productAttributes']['imageUrls']['200x200']:'';
					$sellingPrice = $product['productBaseInfo']['productAttributes']['sellingPrice']['amount'];
					$productUrl = $product['productBaseInfo']['productAttributes']['productUrl'];
					$productBrand = $product['productBaseInfo']['productAttributes']['productBrand'];
					$color = $product['productBaseInfo']['productAttributes']['color'];
					$productUrl = $product['productBaseInfo']['productAttributes']['productUrl'];
					$from="Flipkart";

			
			}
			else
			{
					if($response->Items->Item!=NULL){
					$asin=($response->Items->Item[$i]->ASIN);
					
					$response2 = $amazonEcs->responseGroup('Large')->lookup($asin);
					
					$productImage=($response2->Items->Item->MediumImage->URL);
					$sellingPrice=($response2->Items->Item->ItemAttributes->ListPrice->FormattedPrice);
					if ($sellingPrice==NULL)
						$sellingPrice=($response2->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice);
					$title=($response2->Items->Item->ItemAttributes->Title);
					$productUrl=($response2->Items->Item->DetailPageURL);
					$from="Amazon";
					$i+=1;}
					//print_r($productImage.'</br>'.$sellingPrice.'</br>'.$title.'</br>'.$productUrl);
			}
			
			$count++;		
			
			
			//Setting up the table rows/columns for a 3x3 view.
			$end = 0;
			if($count%3==1)
				echo '<div class="grids_of_3">
			<div class="grid1_of_3">';
			else if($count%3==2)
				echo '</div>
			<div class="grid1_of_3">';
			else{
				echo '</div>
			<div class="grid1_of_3">';
				$end =1;
			}
			echo '
				<a href="'.$productUrl.'">
				<img src="'.$productImage.'" />
					<h3>'.$title.'</h3>
					<div class="price">
						<h4>'.$sellingPrice.'<span>'.$from.'</span></h4>
					</div>
					<span class=\"b_btm\"></span></a>';
			//echo '<a target="_blank" href="'.$productUrl.'"><img src="'.$productImage.'"/><br>'.$title."</a><br>Rs. ".$sellingPrice;

			if($end)
				echo '</div>
			<div class="clear"></div>
		</div>';

		}
	}

	//A message if no products are printed.	
	if($count==0){
		echo 'SEARCH for an item';
	}

	//A hack to make sure the tags are closed.	
	if($end!=1)
		echo '';

	echo '</table>';

	//Next URL link at the bottom.
	

	//That's all we need for the category view.
	//exit();
	

}
catch(Exception $e)
{
  echo $e->getMessage();
}
?>
<div class="grids_of_3">
			
			<div class="clear"></div>
		</div>	
		<!-- end grids_of_3 -->
	</div>
</div>
</div>	
<!-- start footer -->

<div class="wrap">
	<div class="footer">
		<!-- scroll_top_btn -->
	    <script type="text/javascript">
			$(document).ready(function() {
			
				var defaults = {
		  			containerID: 'toTop', // fading element id
					containerHoverID: 'toTopHover', // fading element hover id
					scrollSpeed: 1200,
					easingType: 'linear' 
		 		};
				
				
				$().UItoTop({ easingType: 'easeOutQuart' });
				
			});
		</script>
		 <a href="#" id="toTop" style="display: block;"><span id="toTopHover" style="opacity: 1;"></span></a>
		<!--end scroll_top_btn -->
		<div class="copy">
			<p class="link">&copy; 2014 Aditii. All rights reserved | Template by&nbsp;&nbsp;<a href="http://w3layouts.com/"> W3Layouts</a></p>
		</div>
		<div class="clear"></div>
	</div>
</div>
</div>
</body>
</html>

