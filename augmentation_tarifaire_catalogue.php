<?php

/**
 *
 * @version $Id$
 * @author Nevea
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FORMATION
 */
/**
 */
//include_once ("FDL/Class.ActiviteApprenant.php");
//include_once ("FDL/Class.CalculDureeActiviteApprenant.php");
include_once("FDL/freedom_util.php");
/**
 * 
 */
function augmentation_tarifaire_catalogue ( &$action ) {
    // Récupérer les paramètres de l'url
    $iIFicheCatalogueClient = GetHttpVars("id");
    $fPourcentage = GetHttpVars("augmentation", "");
    $sOption = GetHttpVars("arrondit");

    $oFicheCatalogueClient = new_Doc("", $iIFicheCatalogueClient);
    // Donnée brut de DB
    $aSousTarifs = $oFicheCatalogueClient->getArrayRawValues("cat_ar_soustarif");
    // Champ non tableau
    $fTarifBase = $oFicheCatalogueClient->getRawValue("cat_tarif");

    if(isset($aSousTarifs) && isset($fTarifBase)) {
        if(!empty($fPourcentage)) {
            if($sOption == "exces") {
                // Appel de la fonction pour calculer l'augmentation pour la colonne 'cat_ar_ttarif' par excés
                calculerAugmentationColonne($aSousTarifs, 'cat_ar_ttarif', $fPourcentage, 0);
                // Appel de la fonction pour calculer l'augmentation pour tarif de base par excès
                $fTarifBase = calculerAugmentationTarifBase($fTarifBase, $fPourcentage, 0);
            } else {
                // Appel de la fonction pour calculer l'augmentation pour la colonne 'cat_ar_ttarif' par defaut
                calculerAugmentationColonne($aSousTarifs, 'cat_ar_ttarif', $fPourcentage, 2);
                // Appel de la fonction pour calculer l'augmentation pour tarif de base par defaut
                $fTarifBase = calculerAugmentationTarifBase($fTarifBase, $fPourcentage, 2);
            }

            // Appel de fonction confuration action lay pour bouton valider false
            configurerActionLayValider($action, $iIFicheCatalogueClient, $fPourcentage, $sOption, $fTarifBase, $aSousTarifs);
        } else {
    
            // Appel de fonction confuration action lay pour bouton sauvegarder false
            configurerActionLaySauvegarder($action, $iIFicheCatalogueClient, $fTarifBase, $aSousTarifs);
        }
    } else if(!empty($fTarifBase)) {
        if(!empty($fPourcentage)) {
            if($sOption == "exces") {
                // Appel de la fonction calculer l'augmentation pour le tarif de base par excès
                $fTarifBase = calculerAugmentationTarifBase($fTarifBase, $fPourcentage, 0);
            } else {
                // Appel de la fonction pour calculer l'augmentation tari lef de base par défaut
                $fTarifBase = calculerAugmentationTarifBase($fTarifBase, $fPourcentage, 2);
            }
            // Appel de fonction confuration action lay pour bouton valider false
            configurerActionLayValider($action, $iIFicheCatalogueClient, $fPourcentage, $sOption, $fTarifBase, "");
        } else {
            // Appel de fonction confuration action lay pour bouton sauvegarder false
            configurerActionLaySauvegarder($action, $iIFicheCatalogueClient, $fTarifBase, $aSousTarifs);
        }
    } else {
        if(!empty($fPourcentage)) {
            if($sOption == "exces") {
                // Appel de la fonction pour calculer l'augmentation pour la colonne 'cat_ar_ttarif' par excés
                calculerAugmentationColonne($aSousTarifs, 'cat_ar_ttarif', $fPourcentage, 0);
            } else {
                // Appel de la fonction pour calculer l'augmentation pour la colonne 'cat_ar_ttarif' par defaut
                calculerAugmentationColonne($aSousTarifs, 'cat_ar_ttarif', $fPourcentage, 2);
            }
            // Appel de fonction confuration action lay pour bouton valider false
            configurerActionLayValider($action, $iIFicheCatalogueClient, $fPourcentage, $sOption, 0, $aSousTarifs);
        } else {
            // Appel de fonction confuration action lay pour bouton sauvegarder false
            configurerActionLaySauvegarder($action, $iIFicheCatalogueClient, 0, $aSousTarifs);
        }
    }

}

// Fonction confuration action lay pour bouton valider false
function configurerActionLayValider($action, $iIFicheCatalogueClient, $fPourcentage, $sOption, $fTarifBase, $aSousTarifs) {
    $action->lay->Set("VALEUR_ID", $iIFicheCatalogueClient);
    $sParametres = "&id=".$iIFicheCatalogueClient."&augmentation=".$fPourcentage."&arrondit=".$sOption;
    $action->lay->Set("PARAMETRES", $sParametres);
    $action->lay->Set("boutonValider", false);
    $action->lay->Set("boutonSauvegarder", true);
    $action->lay->Set("TARIFBASE", $fTarifBase);
    $action->lay->Set("BASE", json_encode($aSousTarifs));
}
// Fonction confuration action lay pour bouton sauvegarder false
function configurerActionLaySauvegarder($action, $iIFicheCatalogueClient, $fTarifBase, $aSousTarifs) {
    $action->lay->Set("PARAMETRE", "&id=".$iIFicheCatalogueClient);
    $action->lay->Set("boutonValider", true);
    $action->lay->Set("boutonSauvegarder", false);
    $action->lay->Set("TARIFBASE", $fTarifBase);
    $action->lay->Set("BASE", json_encode($aSousTarifs));
}
// Fonction calculerAugmentation tarif de base
function calculerAugmentationTarifBase($fValeurInitiale, $fPourcentage, $iNombre) {
    // Calcul de l'augmentation
    $fAugmentation = round(($fValeurInitiale + ($fValeurInitiale * $fPourcentage)/ 100), $iNombre);
    // Retourne le nouveau montant après l'augmentation
    return $fAugmentation;
}
// Fonction calculerAugmentationColonne
function calculerAugmentationColonne(&$aTableau, $sNomColonne, $fPourcentage, $iNombre) {
    foreach ($aTableau as &$aLigne) {
        if (array_key_exists($sNomColonne, $aLigne)) {
            $fValeurInitiale = $aLigne[$sNomColonne];
            // Calcul de l'augmentation par excès
            $fAugmentation = round(($fValeurInitiale + ($fValeurInitiale * $fPourcentage)/ 100), $iNombre);
            // Mettre à jour les valeurs dans le tableau
            $aLigne[$sNomColonne ] = $fAugmentation;
        }
    }
}

?>