<?php 


function _importObject($data_to_map, $mapping, $type_id){
		ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ERROR);

    $vt_object = new ca_objects();
    $idno = str_replace(["-000", "-00", "-0"], "/", $data_to_map["inventory_id"]);
    $idno = str_replace("-", " ", $idno);

    $idno = trim($idno, "/");
    $vt_object->load(["idno" => $idno, "deleted" => 0]);

   
    $vt_object->setMode(ACCESS_WRITE);
    if (!$vt_object->getPrimaryKey()){
        $vt_object->set(array('idno' => $idno,'type_id' => $type_id,'locale_id'=>2));//Define some intrinsic data.
        $vt_object->insert();//Insert the object
        if ($vt_object->numErrors()){
            var_dump($vt_object->getErrors());die();
        }
    }
    $containers = [];
    foreach($data_to_map as $mk=>$data){
        $map = $mapping[$mk];
        if (!$map["target"]) continue;
        if ($map["target"] == "ca_objects.preferred_labels"){ 
            $vt_object->removeAllLabels();
            $vt_object->addLabel(array("name" => $data), 6, null, true);
            continue;
        }
        if ($map["container"]){
            $element_code = end(explode(".", $map["target"]));
            $element_mere_code = explode(".", $map["target"]);
            $element_mere_code = $element_mere_code[1];
            $containers[$element_mere_code][$element_code] = $data; 
            // The import of the containers should be at the end, when all the fetches will be grouped
            continue;
        }
        if ($map["list"] && !$map["needLeaf"]){
            $vt_list = new ca_lists();
            $vt_list->load(["list_code" => $map["list"], "deleted" => 0]);
            if (!$vt_list->getPrimaryKey()) continue;
            $vt_list_item = new ca_list_items();
            $vt_list_item->load(["name_singular" => strtoupper($data["name"]), "list_id" => $vt_list->getPrimaryKey()]);
            if (!$vt_list_item->getPrimaryKey()){
                $vt_list_item->set(["type_id" => "concept", "idno" => strtoupper($data["name"]), "value" => strtoupper($data["name"])]);
                $vt_list_item->insert();
                $vt_list_item->addLabel(["name_singular" => strtoupper($data["name"]), "name_plural" => strtoupper($data["name"])], 6);
                $vt_list_item->update();
            }

        }
        if ($map["relation"]){
            switch ($map["related"]):
                case "ca_entities":
                    $entity_id = getEntityIDByIdno($data);
                    if ($entity_id){
                      //  $vt_object->removeRelationships("ca_entities", $map["relation_type"]);
                       // $vt_object->addRelationship("ca_entities", $entity_id, $map["relation_type"]);

                    }
                    break;
                default:
                    break;
            endswitch;
            continue;
        }
        $metadata = explode(".",$map["target"])[1];
        $vt_object->removeAttributes($metadata);
        if (is_array($data)){
            foreach ($data as $beep => $uniqueData){
                if ($map["needLeaf"]){
                    
                    if (!$uniqueData["is_leaf"]) continue;
                    if ($map["list"]){
                        $vt_list = new ca_lists();
                        $vt_list->load(["list_code" => $map["list"], "deleted" => 0]);
                        if (!$vt_list->getPrimaryKey()) continue;
                        $vt_list_item = new ca_list_items();
                        $vt_list_item->setMode(ACCESS_WRITE);

                        $vt_list_item->load(["name_singular" => strtoupper($uniqueData["name"]), "list_id" => $vt_list->getPrimaryKey()]);
                        if (!$vt_list_item->getPrimaryKey()){
                            $vt_list_item->set(["type_id" => "concept", "idno" => strtoupper($uniqueData["name"]), "value" => strtoupper($uniqueData["name"])]);
                            $vt_list_item->insert();
                            $vt_list_item->addLabel(["name_singular" => strtoupper($uniqueData["name"]), "name_plural" => strtoupper($uniqueData["name"])], 6);
                            $vt_list_item->update();
                        }
                    }
                  
                    $vt_object->addAttribute(array($metadata => $uniqueData["name"]), $metadata);
                    $vt_object->update();
                }else{
                    if ($beep != "name") continue;

                    $name = $uniqueData["name"];
                    if (!is_array($uniqueData)){
                        $name = $uniqueData;
                    }
                    if ($map["list"]){
                        $vt_list = new ca_lists();
                        $vt_list->load(["list_code" => $map["list"], "deleted" => 0]);
                        if (!$vt_list->getPrimaryKey()) continue;
                        $vt_list_item = new ca_list_items();
                        $vt_list_item->setMode(ACCESS_WRITE);
                        $vt_list_item->load(["name_singular" => strtoupper($name), "list_id" => $vt_list->getPrimaryKey()]);
                        if (!$vt_list_item->getPrimaryKey()){
                            $vt_list_item->set(["type_id" => "concept", "idno" => strtoupper($name), "value" => strtoupper($name)]);
                            $vt_list_item->insert();
                            $vt_list_item->addLabel(["name_singular" => strtoupper($name), "name_plural" => strtoupper($name)], 6);
                            $vt_list_item->update();
                        }
                    }
                    $vt_object->addAttribute(array($metadata => $name), $metadata);
                    $vt_object->update(); 
                }

            }
            continue;
        }

       
        $vt_object->addAttribute(array($metadata => $data), $metadata);
    }

    $vt_object->update();
    if ($vt_object->numErrors()){
        var_dump($vt_object->getErrors());die();
    }

    //On traite les containers ici

    //var_dump($containers);die();
    foreach ($containers as $metadata => $container){
        if (!$metadata) continue;
        $vt_object->removeAttributes($metadata);
        $vt_object->update();
    }
    foreach ($containers as $metadata => $container){
        if (!$metadata) continue;
        $vt_object->addAttribute($container, $metadata);
        if($vt_object->numErrors()) {
            var_dump($vt_object->getErrors());
            die();
        }
        
        $vt_object->update();
    }
    return $vt_object->getPrimaryKey();
}