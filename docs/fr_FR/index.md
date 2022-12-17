---
layout: default
lang: fr_FR
---

# Plugin Sensibo Sky

Ce plugin permet de piloter le module Sensibo Sky / Air afin de contrôler vos réversibles.

## Configuration du plugin Sensibo Sky


Après l'installation du plugin, il vous suffit de l'activer.


## Configuration

Après activation, il sera nécessaire d'aller récupérer votre API key sur l'espace cloud de Sensibo Sky, le renseigner et sauvegarder.

Dans la minute qui suit, si la clé API est bien renseignée, le plugin va rapatrier tous les modules enregistrés dans votre espace Sensibo.

Il vous suffira enfin d'attribuer un objet à chaque équipement correspondant à un module et l'activer.

Une option pour le forçage du mode est disponible afin d'éviter de demander un mode climatisation si la température à atteindre est inférieure à la température ambiante. Il sera possible de définir un delta de +/- 3° afin de déterminer au moment où la commande est exécutée s'il faut forcer dans un autre mode que celui choisi.


## Les Commandes

Toutes les commandes actions sont accessibles et peuvent donc être exécutées afin de changer les différentes propriétés:

-   La consigne de température à atteindre

-   Le positionnement de la bouche d'aération.

-   Le mode (chaud, froid, auto, déshumidificateur)

-   La puissance du ventilateur

Toutes les commandes info sont accessibles et vous donneront:

-   La température de la pièce

-   L'humidité de la pièce

-   Le taux de CO2

-   Le taux de TVOC

-   La consigne configurée en cours

-   La puissance du ventilateur en cours

-   Le positionnement de la bouche d'aération en cours

-   Le mode en cours.


## Quoi de neuf pour la prochaine version?

- Amélioration du design de la tuile 