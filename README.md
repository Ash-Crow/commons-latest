commons-latest
==============

Projet archivé, pas mis à jour depuis des années.

Wordpress plugin, shows a gallery of the latest files uploaded to a given category from Wikimedia Commons.

Commons Latest est un plugin pour WordPress permettant d’afficher les dernières images d’une catégorie donnée de Wikimedia Commons sur une page WordPress ou dans un widget.


## Installation

- Télécharger le plugin sur Github.
- Placer le répertoire dans <racine_wordpress>/wp-content/plugins/

## Réglages

Le plugin est paramétrable via `Réglages > Commons Latest`

## Utilisation au sein d’une page

Il suffit d’utiliser le shortcode suivant :

```
[[commonslatest category="nom de la catégorie"]]
```

Le shortcode accepte les options suivantes :

- `category` : La catégorie Commons à utiliser
- `width` : la largeur des vignettes
- `quantity` : le nombre de vignettes à afficher

Si un de ces paramètres n’est pas défini, c’est celui qui est défini par défaut dans les réglages du plugin qui est utilisé.

## Widget

Le widget « Dernière image sur Commons » affiche la dernière image publiée dans la catégorie définie dans les réglages du plugin.

