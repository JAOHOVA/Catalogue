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

function sauvegarde_augmentation_tarifaire_catalogue ( &$action ) {
    
    // Récupérer les paramètres de l'url
    $iIFicheCatalogueClient = GetHttpVars("id");
    $fPourcentage = GetHttpVars("augmentation", "");
    $sOption = GetHttpVars("arrondit");
    $oFicheCatalogueClient = new_Doc("", $iIFicheCatalogueClient);
    // Donnée brut de DB
    $aFicheCatalogueClient = $oFicheCatalogueClient->getArrayRawValues("cat_ar_soustarif");
    $fTarifBase = $oFicheCatalogueClient->getRawValue("cat_tarif");

    if(!empty($fPourcentage)) {
        if($sOption == "exces") {
            // Appel de la fonction pour calculer l'augmentation pour la colonne 'cat_ar_ttarif' par excés
            calculerAugmentationColonne($aFicheCatalogueClient, 'cat_ar_ttarif', $fPourcentage, 0);
            // Appel de la fonction setAttributeValueAndStoreArray pour enregistrer dans la base de données
            setAttributeValueAndStoreArray($oFicheCatalogueClient, 'cat_ar_soustarif', $aFicheCatalogueClient);

            // Appel de la fonction calculerAugmentation tarif de base par excès
            $fTarifBase = calculerAugmentationTarifBase($fTarifBase, $fPourcentage, 0);
            // Appel de la fonction setAttributeValueAndStoreFloat pour enregistrer dans la base de données
            setAttributeValueAndStoreFloat($oFicheCatalogueClient, 'cat_tarif', $fTarifBase);
        } else {
            // Appel de la fonction pour calculer l'augmentation pour la colonne 'cat_ar_ttarif' par defaut
            calculerAugmentationColonne($aFicheCatalogueClient, 'cat_ar_ttarif', $fPourcentage, 2);
            // Appel de la fonction setAttributeValueAndStoreArray pour enregistrer dans la base de données
            setAttributeValueAndStoreArray($oFicheCatalogueClient, 'cat_ar_soustarif', $aFicheCatalogueClient);

            // Appel de la fonction calculerAugmentation tarif de base par défaut
            $fTarifBase = calculerAugmentationTarifBase($fTarifBase, $fPourcentage, 2);
            // Appel de la fonction setAttributeValueAndStoreFloat pour enregistrer dans la base de données
            setAttributeValueAndStoreFloat($oFicheCatalogueClient, 'cat_tarif', $fTarifBase);
        }
    }

    // Redirection vers la page d'accueil
    $url = '?app=FORMATION&action=AUGMENTATION_TARIFAIRE_CATALOGUE&id='.$iIFicheCatalogueClient;
    header("Location:".$url);
            
}

// Fonction setAttributeValueAndStoreArray
function setAttributeValueAndStoreArray($oFicheCatalogueClient, $sAttribute, $aValeur) {
    $oFicheCatalogueClient->setAttributeValue($sAttribute, $aValeur);
    $oFicheCatalogueClient->store();
}
// Fonction setAttributeValueAndStoreFloat
function setAttributeValueAndStoreFloat($oFicheCatalogueClient, $sAttribute, $fValeur) {
    $oFicheCatalogueClient->setAttributeValue($sAttribute, $fValeur);
    $oFicheCatalogueClient->store();
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
