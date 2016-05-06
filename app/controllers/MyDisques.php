<?php
use micro\controllers\Controller;
use micro\js\Jquery;
use micro\utils\RequestUtils;
class MyDisques extends Controller{
	public function initialize(){
		if(!RequestUtils::isAjax()){
			$this->loadView("main/vHeader.html",array("infoUser"=>Auth::getInfoUser()));
		}
	}
	public function index() {
		echo Jquery::compile();
		if (Auth::isAuth()==True) {
			$users = Auth::getUser();
			$disque = micro\orm\DAO::getOneToMany($users, "disques");
			$this->loadView("Disques/vmydisc.html", array("users"=>$users, "disque"=>$disque));
			}
		else {
			echo "Vous devez vous connecter.";
		}
		$this -> loadView("Disques/vmydisc.html", array("user"=>$user));
	}

	public function finalize(){
		if(!RequestUtils::isAjax()){
			$this->loadView("main/vFooter.html");
		}
	}

}
?>