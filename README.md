## sujet

c'est un projet pour module RH pour gerer les employer et leur paie,conger,contrat,pointage..

## prerequis

PHP version >=8.2.12
Composer version =2.9.5
git version =2.53
node =v24.14.0
npm =11.9.0

## Installation

1. **Cloner le projet :**

```bash
git clone https://github.com/jawharbenmariem45-source/ModulRH.git
cd ModulRH
```

2. **Installer les dépendances PHP via Composer :**

```bash
composer install
```

3. **Installer les dépendances front-end :**

```bash
npm install
npm run dev
```

4. **Copier le fichier d'environnement:**

```bash
cp .env.example .env
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

## Comptes par défaut

- Admin : admin@gmail.com / azerty
- RH : rh@gmail.com / 123456
- Manager : manager@gmail.com / 123456
- Employé : employer@gmail.com / 123456

## Fonctionnalités

### Gestion des Employés & Contrats

-Ajout, modification et suppression des employés
-Gestion des informations de profil (RIB à 23 caractères, CNSS, Département)
-Association d'un contrat par employé (CDI, CDD, CIVP)

### Gestion des Paiements (Fiche de Paie)

-Calcul automatique du salaire net à partir du brut
-Déduction des cotisations légales tunisiennes (CNSS, IRPP)
-Génération et téléchargement de la fiche de paie en PDF

### Pointage & Présence

-Enregistrement quotidien de la présence (Bouton "Pointer")
-Consultation de l'historique des pointages par l'employé
-Suivi global des présences par le RH

### Gestion des Rôles

- **Admin** : accès total
- **RH** : gestion des employés, paiements, congés, pointages
- **Manager** : gestion des congés
- **Employé** : espace personnel (pointage, congés, paiements, contrat)

### Configurations

- Date de paiement mensuelle
- Régime horaire (40h / 48h)

### Structure du projet

- app/Http/Controllers : contrôleurs
- database/migrations : migrations de la base de données
- resources/views : vues Blade
- routes/web.php : routes web
- routes/api.php : routes API
