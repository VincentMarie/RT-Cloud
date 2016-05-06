<?php
class Disques extends \_DefaultController {

	public function __construct(){
		parent::__construct();
		$this->title="Disques";
		$this->model="Disque";
	}

	public function form($id=NULL){
		$disque=$this->getInstance($id);
		$disabled="";
		$this->loadView("views/Disques/form.html",array("disque"=>$disque,"disabled"=>$disabled));
		}

	/* (non-PHPdoc)
	 * @see _DefaultController::setValuesToObject()
	 */

	protected function setValuesToObject(&$object) {
		parent::setValuesToObject($object);
		$object->setUtilisateur(Auth::getUser());
	}
}
