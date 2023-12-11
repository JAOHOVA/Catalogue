<?php
/**
 * Methods for contact document
 *
 * @author Nevea
 * @version $Id$
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package NEVEA_ADDONS
 */
include_once("./FDL/freedom_util.php");

function reponse_augmentation_tarifaire_catalogue ( &$action ) {

    $iIFicheCatalogueClient = GetHttpVars("id");
    $fPourcentage = $_POST["augmentation"];
    $sOption = $_POST["arrondit"];


    $url = '?app=FORMATION&action=AUGMENTATION_TARIFAIRE_CATALOGUE&id='.$iIFicheCatalogueClient.'&augmentation='.$fPourcentage.'&arrondit='.$sOption;
    header("Location:".$url);
}

?>
