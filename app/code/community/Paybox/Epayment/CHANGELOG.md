# Change Log
## [3.0.5] 2017-10-03
### Corrections
- IPN : gestion IPN successifs pour paiement en 3 fois (sauvegarde de la commande pour mise à jour des informations)
- Actions Back Office : gestion du multi-devise sur les remboursements
- Cron nettoyage des commandes : correction de la requête SQL

## [3.0.4] 2017-01-30
### Ajouts
- Moyens de paiement : ANCV, MasterPass, Illicado

### Modifications
- Code : nettoyage PSR-2

## [3.0.3] 2016-10-12
### Ajouts
- Paiement : possibilité d'utiliser la page de paiement Verifone e-commerce RWD
- Back Office : actions d'annulation des paiements en 3 fois

### Modifications
- PayPal : paramétrage spécifique lors de l'appel à la plateforme de paiement
- Code : nettoyage

## [3.0.2] 2016-10-12
### Ajouts
- Configuration : gestion du multi-devise pour le paiement avec possibilité de forcer le paiement avec la devise par défaut ou de laisser le choix au client parmi les devises disponibles

## [3.0.1] 2016-07-29
### Ajouts
- Paiement : ajout du paramètre de version pour suivi des transactions par Verifone e-commerce

### Corrections
- IPN : mise en conformité des paramètres "Call number" / "Transaction"
- IPN : modification de l'enregistrements des transactions non valides (saisie de coordonnées bancaires invalides, ...) pour création de transaction vide => correction du problème d'actions Back Office qui avant cela utilisaient la 1ère transaction invalide de capture comme transaction parente

## [3.0.0] 2016-04-05
### Ajouts
- Configuration : nouvelle formule "Pack Flexible"
- Moyens de paiement: paiement en 3 fois

### Modifications
- Code : nettoyage

## [2.0.8] 2016-03-31
### Modifications
- Dossier data : suppression
- Versions : nettoyage des versions des fichiers

## [2.0.7] 2016-03-16
### Modifications
- IPN : suppression du filtrage des adresses IP

## [2.0.6] 2016-03-03
### Ajouts
- 3-D Secure : ajout du statut dans les détails de la transaction

### Corrections
- Compilation : correction de la validation de la signature
- IPN : correction du filtrage des adresses IP avec Proxy
