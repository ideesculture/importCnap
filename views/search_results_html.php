<?php

$results = $this->getVar("results");

?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css">
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js"></script>
  <script type="text/javascript" charset="utf8" src="https:/cdn.datatables.net/plug-ins/1.13.1/i18n/fr-FR.json"></script>

<h1>Résultats</h1>

<table id="resultats">
    <thead>
        <tr>
            <td></td>

            <td>Dénomination</td>
            <td>Numéro d'inventaire</td>
            <td>Auteur</td>
            <td>Période</td>
            <td></td>

            <td></td>

        </tr>
    </thead>
    <tbody>
        <?php
            foreach ($results as $result){
                ?>
                <tr>
                    <td><img src="https://collection.mobiliernational.culture.gouv.fr/media/xl/<?=$result["images"][0]["path"]?>" style="width:50px"/></td>
                    <td><?=$result["denomination"]?></td>
                    <td><?=$result["inventory_id"]?></td>
                    <td><?php
                        $authors = [];
                        foreach ($result["authors"] as $author){
                            $author_name = trim($author["first_name"]." ".$author["last_name"]);
                            $authors[] = $author_name;
                        }
                        print implode(", ", $authors);
                    
                    ?></td>
                    <td><?=$result["period_name"]?></td>
                    <td><a href="https://collection.mobiliernational.culture.gouv.fr/objet/<?=$result["inventory_id"]?>" target="_blank">site collections MN</a></td>

                    <td class="hideText"><?php 
                    
                    $idno = str_replace(["-000", "-00", "-0"], "/", $result["inventory_id"]);
                    $idno = str_replace("-", " ", $idno);

                    $idno = trim($idno, "/");
                    $vt_obj = new ca_objects();
                    $vt_obj->load(["idno" => $idno, "deleted" => 0]);
                    if ($vt_obj->getPrimaryKey()){
                        print 1 . caNavLink($this->request, caNavIcon(__CA_NAV_ICON_EDIT__), "commit_button", "ImportMobilierNational", "Import", "Details", ["idno"=>$result["inventory_id"]]);
                    }else{
                        print 1 . caNavLink($this->request, caNavIcon(__CA_NAV_ICON_COMMIT__), "commit_button", "ImportMobilierNational", "Import", "Details", ["idno"=>$result["inventory_id"]]);
                    }

                    
                    
                   ?></td>

                </tr>
<?php

            }
        ?>
    </tbody>
</table>
<div style="height:120px"></div>

<style>

    .hideText{
        color: white;
    }


#mainContent{
    margin-left: inherit;
    width: 970px;
    border-left: 2px solid #ddd;
}
</style>
<script>
    $('#resultats').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.1/i18n/fr-FR.json"
        }
    });
    $(document).ready(function(){
        $("#leftNavSidebar").parent().remove();
    })
</script>