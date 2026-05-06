## sujet

c'est un projet pour module RH pour gerer les employer et leur paie,conger,contrat,pointage..

## Fonctionnalités

### Gestion des Employés

- Ajout, modification et suppression des employés
- Gestion des informations personnelles (RIB, CNSS, département...)
- Gestion des situations familiales (chef de famille, enfants à charge...)

### Gestion des Contrats

- Types de contrats : **CDI, CDD, CIVP, Karama**
- Alertes d'expiration de contrat (30 jours avant)
- Téléchargement des contrats en PDF

### Gestion des Paiements (Fiche de Paie)

- Calcul automatique du salaire selon le type de contrat
- Calcul **CNSS** (9.68% plafonné + 1% maladie)



- Calcul **IRPP** (barème progressif 2026 — 8 tranches)
- Calcul **CSS** (0.5%)
- Calcul des **heures supplémentaires** (régime 40h/48h)
- Proratisation du salaire selon les jours travaillés
- Génération de fiches de paie en **PDF**
- Exonérations spéciales CIVP et Karama

### Gestion des Congés

- Demande de congé par l'employé
- Approbation / Rejet par le RH
- Calcul automatique du solde selon :
    - Type de contrat
    - Ancienneté (date de début de contrat)
    - Normes tunisiennes du Code du Travail
- Vérification du solde avant validation

### Pointage

- Check-in / Check-out matin et après-midi
- Historique mensuel des présences
- Calcul automatique des heures supplémentaires
- Vue RH : pointages de tous les employés
- Vue Employé : son propre pointage

### Gestion des Rôles

- **Admin** : accès total
- **RH** : gestion des employés, paiements, congés, pointages
- **Manager** : gestion des congés
- **Employé** : espace personnel (pointage, congés, paiements, contrat)

### Configurations

- Date de paiement mensuelle
- Régime horaire (40h / 48h)
- Gestion multi-entreprises

### Structure du projet

- app/Http/Controllers : contrôleurs
- database/migrations : migrations de la base de données
- resources/views : vues Blade
- routes/web.php : routes web
- routes/api.php : routes API

## prerequis

PHP version >=8.2.12
Composer version =2.9.5
git version =2.53
node =v24.14.0
npm =11.9.0

## Installation

1. **Cloner le projet :**

```bash

git

cd salaire-employer

```

2. **Installer les dépendances PHP via Composer :**

```bash

composer install

```

3. **Installer les dépendances front-end (si nécessaire):**

```bash

npm install

npm run dev

```

4. **Copier le fichier d'environnement:**

```bash

git clone https://github.com/jawharbenmariem45-source/ModulRH.git



copy .env.example .env

```

5. **Configurer la base de données:**

Éditer le fichier .env pour définir vos informations de connexion db

6. **Générer la clé de l'application :**

```bash

php artisan key:generate

```

7. **Exécuter les migrations et seeders :**

```bash

php artisan migrate --seed

```

8. **Démarrer le serveur local :**

```bash

php artisan serve

```

L'application sera disponible sur http://127.0.0.1:8000
