<?php
class Disques extends \_DefaultController {
	public function __construct(){
		parent::__construct();
		$this->title="Disques";
		$this->model="Disque";
	}
	public function frm($id=NULL){
		$disque=$this->getInstance($id);
		$disabled="";
		$this->loadView("Disques/frm.html",array("disque"=>$disque,"disabled"=>$disabled));
	}
	public function update(){
		// Si un ID et un nom sont passés en paramètres, il s'agit de mettre à jour un disque ***
		if($_POST["id"] && $_POST['nom']) {
			// On recupère le chemin ABSOLU du dossier (disque) grace à l'ancien nom du disque disque et au variable globale
			$oldfolder = DAO::getOne('Disque', $_POST['id'])->getNom();
			$basepath = (dirname(dirname(__DIR__))."/files/".$GLOBALS['config']['cloud']['prefix'].Auth::getUser()->getLogin().'/');
			$actualpath = $basepath.$oldfolder;
			$newpath = $basepath.$_POST['nom'];
			// Ensuite une exception classique pour tester si tout s'est bien passé !
			try {
				rename($actualpath, $newpath);
			} catch (Exception $e) {
				die("Erreur pour renommer le dossier");
			}
			// *** Sinon, il s'agit de créer un disque
		} else {
			if ($_POST['nom']) {
				// On recupère le chemin ABSOLU du dossier (disque) comme au dessus
				$basepath = (dirname(dirname(__DIR__))."/files/".$GLOBALS['config']['cloud']['prefix'].Auth::getUser()->getLogin().'/');
				$newpath = $basepath.$_POST['nom'];
				// Ensuite une exception classique pour tester si la création a fonctionné !
				try {
					mkdir($newpath);
				} catch (Exception $e) {
					die("Erreur de créer le dossier");
				}
			}
		}
		// On appelle ensuite la fonction update du DefaultController pour mettre à jour les paramètres en base de données.
		parent::update();
	}
	/* (non-PHPdoc)
	 * @see _DefaultController::setValuesToObject()
	 */
	protected function setValuesToObject(&$object) {
		parent::setValuesToObject($object);
		$object->setUtilisateur(Auth::getUser());
	}
}