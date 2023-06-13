<?php
require_once(__CA_LIB_DIR__.'/ProgressBar.php');
require_once(__CA_LIB_DIR__.'/Parsers/ZipFile.php');
require_once(__CA_MODELS_DIR__.'/ca_sets.php');
require_once(__CA_MODELS_DIR__.'/ca_bundle_displays.php');
require (__CA_APP_DIR__."/plugins/ImportMobilierNational/lib/_importObject.php");

class ImportController extends ActionController {
	# -------------------------------------------------------
	/**
	 *
	 */
	protected $config;		// plugin configuration file

	public function Index() {
		$this->render("index_html.php");
	}

	public function Search() {
		$search = $this->request->getParameter("search", pString);
		$url = "https://collection.mobiliernational.culture.gouv.fr/api/search?q=".urlencode($search)."/";
		$response = file_get_contents($url);
		$response = json_decode($response, true);
		$results = [];
		foreach ($response["hits"] as $result){
			array_push($results, $result);

		}
		//var_dump($results);die();
		while ($response["nextPageUrl"]){
			$url = $response["nextPageUrl"];
			$url = str_replace("http://collection.mobiliernational.fr", "https://collection.mobiliernational.culture.gouv.fr", $url);

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$response = curl_exec($ch);

			$response = json_decode($response, true);

			foreach ($response["hits"] as $result){
				array_push($results, $result);
	
			}
		}
		curl_close($ch);		

		$this->view->setVar("results", $results);

		$this->render("search_results_html.php");
	}

	public function Details() {
		$this->config = Configuration::load(__CA_APP_DIR__.'/plugins/ImportMobilierNational/conf/mapping.conf');

		$idno = $this->request->getParameter("idno", pString);

		$url = "https://collection.mobiliernational.culture.gouv.fr/objet/".$idno;
		$response = file_get_contents($url);
		preg_match_all("|PRODUCT = {(.*)}|", $response, $matches);
		$product = json_decode("{".$matches[1][0]."}", true);
		$this->view->setVar("product", $product);
		$this->view->setVar("idno", $idno);
		$this->view->setVar("config", $this->config->get("mapping"));
		$this->render("details_html.php");
	}

	public function Import() {
		$this->config = Configuration::load(__CA_APP_DIR__.'/plugins/ImportMobilierNational/conf/mapping.conf');
		$idno = $this->request->getParameter("idno", pString);

		$url = "https://collection.mobiliernational.culture.gouv.fr/objet/".$idno;
		$response = file_get_contents($url);
		preg_match_all("|PRODUCT = {(.*)}|", $response, $matches);
		$product = json_decode("{".$matches[1][0]."}", true);

		$vt_id = _importObject($product,$this->config->get("mapping"), 27);

		$this->redirect("https://justice.ideesculture.fr/gestion/index.php/editor/objects/ObjectEditor/Edit/Screen43/object_id/".$vt_id);
	}

}
