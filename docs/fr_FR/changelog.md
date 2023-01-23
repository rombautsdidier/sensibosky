# Changelog Sensibo Sky

> Pour rappel s’il n’y a pas d’information sur la mise à jour, c’est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

## 23/01/2023

Correction d'un bug sur les commandes fanLevel.
Il faudra supprimer les commandes commançant par fanLevels. De nouvelles commandes seront automatiquement ajoutées pour gérer correctement la force de ventilation. 

## 17/12/2022

Ajout des commandes pour récupérer des informations du Sensibo Air
Modification de la présentation de la liste des commandes depuis la 4.2


## 16/10/2020

Correction d'une erreur dans une fonction qui force un mode si la température à atteindre et le mode choisi ne correspond pas à la température ambiante. Par exemple, si la température est à 21° et que la consigne est 24° et que le mode cool est choisi, le plugin forcera en heat. 

Ajout d'une option dans l'équipement pour activer ou non la fonction de forçage du mode et à partir de quel delta de température le forçage se fera.

Ajout du paramètre step pour le slider pour avoir des pas de 1° et non de 0.5° par défaut. Pour ce paramètre, il faut attendre 1 minute après la mise à jour du plugin pour que le pas de 1° soit effectif.


## 21/07/2020

Prise en compte de la liste des capacités fournies par le module afin de créer uniquement les commandes nécessaires.
Attention, il faut supprimer le ou les équipements pour recréer les bonnes commandes.

## 27/06/2020

Première version du plugin.
