<?php
use micro\js\Jquery;
/**
 * Contrôleur permettant d'afficher/gérer 1 disque
 * @author jcheron
 * @version 1.1
 * @package cloud.controllers
 */
class Scan extends BaseController
{
    public function index()
    {
    }
    /**
     * Affiche un disque
     * @param int $idDisque
     */
    public function show($idDisque, $option = false){
        if (Auth::isAuth()) { //Vérification de la connexion d'un utilisateur
            $user = Auth::getUser();
            $disque = micro\orm\DAO::getOne("disque", "id =". $idDisque ."&& idUtilisateur = ". $user->getId());
            $services = micro\orm\DAO::getManyToMany($disque, "services");
            $tarif = ModelUtils::getDisqueTarif($disque);
            if($option) {
                $tarifs = micro\orm\DAO::getAll("tarif");
                $selected = $disque->getTarif()->getId();
                $this->loadView("scan/tarifs.html", array("disque" => $disque, "user" => $user,
                    "tarifs" => $tarifs, "selected" => $selected));
            }
            if(empty($disque)) {
                $msg = new DisplayedMessage();
                $msg->setContent("Le disque n'existe pas ou ne vous appartient pas !")
                    ->setType("warning")
                    ->setDismissable(true)
                    ->show($this);
                return false;
            }
            $users = $disque->getUtilisateur()->getLogin();
            $diskName = $disque->getNom();
            $size = DirectoryUtils::formatBytes($disque->getSize());
            $quota = DirectoryUtils::formatBytes($disque->getQuota());
            $occupation = $disque->getOccupation();
            $this->loadView("scan/vFolder.html", array("idDisque" => $idDisque, "user" => $users, "nom_disque" => $diskName, "taille" => $size,
                "occupation" => $occupation, "quota" => $quota, "tarif" => $tarif, "services" => $services));
            Jquery::executeOn("#ckSelectAll", "click", "$('.toDelete').prop('checked', $(this).prop('checked'));$('#btDelete').toggle($('.toDelete:checked').length>0)");
            Jquery::executeOn("#btUpload", "click", "$('#tabsMenu a:last').tab('show');");
            Jquery::doJqueryOn("#btDelete", "click", "#panelConfirmDelete", "show");
            Jquery::postOn("click", "#btConfirmDelete", "scan/delete", "#ajaxResponse", array("params" => "$('.toDelete:checked').serialize()"));
            Jquery::doJqueryOn("#btFrmCreateFolder", "click", "#panelCreateFolder", "toggle");
            Jquery::postFormOn("click", "#btCreateFolder", "Scan/createFolder", "frmCreateFolder", "#ajaxResponse");
            Jquery::execute("window.location.hash='';scan('" . $diskName . "')", true);
            echo Jquery::compile();
        }
        else {
            $msg = new DisplayedMessage();
            $msg->setContent("Vous devez vous connecter pour avoir accès à cette ressource")
                ->setType("danger")
                ->setDismissable(false)
                ->show($this);
            echo Auth::getInfoUser();
        }
    }
    public function changeTarif() {
        $valid_input = ['disqueId', 'userId', 'tarif'];
        $disque = micro\orm\DAO::getOne('disque', 'id = '. $_GET['disqueId']);
        $disqueTarif = micro\orm\DAO::getOne('disquetarif', 'idDisque = '. $_GET['disqueId']);
        $size=var_dump($disque->getSize());
        ModelUtils::sizeConverter($size);
        $tarif = micro\orm\DAO::getOne('tarif', 'id = '. $_GET['tarif']);
        $i=$disqueTarif->setTarif($tarif);
        $u=micro\orm\DAO::update($i);
        if ($u) {
            header('Location: /RT-Cloud/Scan/show/' . $_GET['disqueId']);
            return false;
        } else {
            echo '<div class="alert alert-danger">Une erreur est survenue, veuillez rééssayer ultérieurement</div>';
        }
    }
    public function files($dir="Datas"){
        $cloud=$GLOBALS["config"]["cloud"];
        $root=$cloud["root"].$cloud["prefix"].Auth::getUser()->getLogin()."/";
        $response = DirectoryUtils::scan($root.$dir,$root);
        header("Content-type: application/json");
        echo json_encode(array(
                "name" => $dir,
                "type" => "folder",
                "path" => $dir,
                "items" => $response,
                "root" => $root
        ));
    }
    public function upload(){
        $allowed = array("png", "jpg", "gif","zip");
        if(isset($_FILES["upl"]) && $_FILES["upl"]["error"] == 0){
            $extension = pathinfo($_FILES["upl"]["name"], PATHINFO_EXTENSION);
            if(!in_array(strtolower($extension), $allowed)){
                echo "{'status':'error'}";
                exit;
            }
            if(move_uploaded_file($_FILES["upl"]["tmp_name"], $_POST["activeFolder"]."/".$_FILES["upl"]["name"])){
                echo "{'status':'success'}";
                exit;
            }
        }
        echo '{"status":"error"}';
        exit;
    }
    /**
     * Supprime le fichier dont le nom est fourni dans la clé toDelete du $_POST
     */
    public function delete(){
        if(array_key_exists("toDelete", $_POST)){
            foreach ($_POST["toDelete"] as $f){
                unlink(realpath($f));
            }
            echo Jquery::execute("scan()");
            echo Jquery::doJquery("#panelConfirmDelete", "hide");
        }
    }
    /**
     * Crée le dossier dont le nom est fourni dans la clé folderName du $_POST
     */
    public function createFolder(){
        if(array_key_exists("folderName", $_POST)){
            $pathname=$_POST["activeFolder"].DIRECTORY_SEPARATOR.$_POST["folderName"];
            if(DirectoryUtils::mkdir($pathname)===false){
                $this->showMessage("Impossible de créer le dossier `".$pathname."`", "warning");
            }else{
                Jquery::execute("scan();",true);
            }
            Jquery::doJquery("#panelCreateFolder", "hide");
            echo Jquery::compile();
        }
    }
    /**
     * Affiche un message dans une alert Bootstrap
     * @param String $message
     * @param String $type Class css du message (info, warning...)
     * @param number $timerInterval Temps d'affichage en ms
     * @param string $dismissable Alert refermable
     * @param string $visible
     */
    public function showMessage($message,$type,$timerInterval=5000,$dismissable=true){
        $this->loadView("main/vInfo",array("message"=>$message,"type"=>$type,"dismissable"=>$dismissable,"timerInterval"=>$timerInterval,"visible"=>true));
    }
}