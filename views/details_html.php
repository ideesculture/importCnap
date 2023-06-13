<?php
$product = $this->getVar("product");
$config = $this->getVar("config");
$idno = $this->getVar("idno");
//var_dump($config);
?>
<div class='images'  style="float:right;margin:0 0 0 20px;">
	<img src="https://collection.mobiliernational.culture.gouv.fr/media/xl/<?=$product["images"][0]["path"]?>" style="width:250px;"/>
	<br/>
	<?php
		for($i=1;$i<sizeof($product["images"]);$i++){
?>
		<img src="https://collection.mobiliernational.culture.gouv.fr/media/xl/<?=$product["images"][$i]["path"]?>" style="width:70px;"/>
<?php			
		};	
	?>
</div>

<h1><?= $product["title_or_designation"] ?></h1>
<div class="product">
<?php foreach($product as $attribute_name=>$value) {
	if(!($value)) continue;
	if($attribute_name == "id") continue;
	if($attribute_name == "inventory_id_as_keyword") continue;
	if($attribute_name == "period_start_year") continue;
	if($attribute_name == "period_end_year") continue;
	if($attribute_name == "image_quality_score") continue;
	if(!$title = $config[$attribute_name]["title"]) {
		$title = $attribute_name;
	} 
	print "<p><b>".$title."</b> : ";
	if(is_string($value)) {
		print $value;
	} else if(is_array($value)) {
		print "<ul>";
		if($attribute_name == "authors") {
			foreach($value as $key=>$subvalue) {
				print "<li>".$subvalue["first_name"]." ".$subvalue["last_name"]."</li>";
			}
		} elseif ($attribute_name == "images") {
			foreach($value as $key=>$subvalue) {
				print "<li>".$subvalue["path"]."</li>";

			}
		} else {
			//var_dump($value);
			foreach($value as $key=>$subvalue) {
				if($value["name"] && ($key != "name")) continue;
				print "<li>";
				
				if(isset($subvalue["name"])) {
					print $subvalue["name"];
				} else if($key == "name") {
					print $subvalue;
				} else {
					var_dump($subvalue);
				}
				print "</li>";
			}
		}
		print "</ul>";
	}
	print "</p>";
}

?>
</div>
<a href="https://justice.ideesculture.fr/gestion/index.php/ImportMobilierNational/Import/Import/idno/<?= $idno ?>">
<button>Importer</button>
</a>
<div style="height:120px"></div>
<style>
	b {
		font-weight: 900;
		font-family: Verdana, Geneva, Tahoma, sans-serif;

	}
</style>