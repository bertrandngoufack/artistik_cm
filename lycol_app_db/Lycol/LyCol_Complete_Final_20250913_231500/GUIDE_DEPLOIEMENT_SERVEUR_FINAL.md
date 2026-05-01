# 🚀 LYCCOL - GUIDE DE DÉPLOIEMENT SERVEUR FINAL
## Déploiement Professionnel sur Nouveau Serveur

**Date :** 13 Septembre 2025  
**Version :** Guide Expert Final  
**Auteur :** Expert CodeIgniter 4, PHP, MariaDB Senior  
**Niveau :** Production-Ready  

---

## 🎯 OBJECTIF DU GUIDE

Ce guide permet de déployer l'application LyCol sur **n'importe quel serveur** avec une configuration réseau professionnelle qui s'adapte automatiquement aux changements d'IP.

### ✅ Avantages de ce Déploiement
- **Configuration automatique** : Adaptation intelligente aux IPs
- **Installation simplifiée** : Scripts automatisés
- **Sécurité renforcée** : Configuration production-ready
- **Maintenance facilitée** : Gestion via .env

---

## 📋 PRÉREQUIS SERVEUR

### Configuration Système Minimale
- **OS** : Linux (Ubuntu 20.04+, CentOS 8+, Debian 11+)
- **RAM** : 2GB minimum (4GB recommandé)
- **CPU** : 2 cœurs minimum
- **Stockage** : 10GB minimum
- **Réseau** : Accès internet + port 8080 ouvert

### Logiciels Requis
- **PHP** : 8.4+ avec extensions
- **MariaDB** : 10.11+ ou MySQL 8.0+
- **Git** : Pour le versioning
- **cURL** : Pour les tests
- **Unzip** : Pour l'extraction

---

## 🔧 INSTALLATION DES PRÉREQUIS

### 1. Installation PHP 8.4+
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4 php8.4-cli php8.4-mysql php8.4-gd php8.4-curl php8.4-zip php8.4-mbstring php8.4-xml

# CentOS/RHEL
sudo dnf install -y epel-release
sudo dnf install -y php84 php84-cli php84-mysql php84-gd php84-curl php84-zip php84-mbstring php84-xml
```

### 2. Installation MariaDB
```bash
# Ubuntu/Debian
sudo apt install -y mariadb-server mariadb-client

# CentOS/RHEL
sudo dnf install -y mariadb-server mariadb
sudo systemctl enable mariadb
sudo systemctl start mariadb
```

### 3. Configuration MariaDB
```bash
# Sécurisation de l'installation
sudo mysql_secure_installation

# Création de la base de données
sudo mysql -u root -p
CREATE DATABASE lyscol CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'lycol_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE_SECURISE';
GRANT ALL PRIVILEGES ON lyscol.* TO 'lycol_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Installation des Outils
```bash
# Git et outils de développement
sudo apt install -y git curl wget unzip

# Vérification des installations
php --version
mysql --version
git --version
```

---

## 📦 DÉPLOIEMENT DE L'APPLICATION

### 1. Extraction de l'Archive
```bash
# Télécharger l'archive sur le serveur
# Puis extraire
tar -xzf LyCol_Complete_Final_20250913_231500.tar.gz
cd codeigniter4-framework-68d1a58

# Vérifier les permissions
chmod +x *.sh
chmod -R 755 app/
chmod -R 755 public/
```

### 2. Configuration de la Base de Données
```bash
# Restauration de la base de données
mysql -u root -p < backup_database_final_20250913_231500.sql

# Ou avec utilisateur dédié
mysql -u lycol_user -p lyscol < backup_database_final_20250913_231500.sql
```

### 3. Configuration de l'Application
```bash
# Configuration réseau automatique
./manage_network_config.sh --auto-detect

# Vérification de la configuration
./manage_network_config.sh --show-config
```

### 4. Test de l'Installation
```bash
# Test complet multi-IP
./test_env_network_config.sh

# Si tous les tests passent, l'installation est réussie
```

---

## 🌐 CONFIGURATION RÉSEAU

### Mode Auto-Détection (Recommandé)
```bash
# Configuration automatique
./manage_network_config.sh --auto-detect

# L'application s'adaptera automatiquement à toute IP du serveur
```

### Mode IP Fixe
```bash
# Configuration IP fixe
./manage_network_config.sh --set-ip 192.168.1.100

# L'application utilisera cette IP spécifique
```

### Vérification de la Configuration
```bash
# Afficher la configuration actuelle
./manage_network_config.sh --show-config

# Tester toutes les IPs
./test_env_network_config.sh
```

---

## 🚀 DÉMARRAGE DU SERVEUR

### Démarrage Simple
```bash
# Démarrage avec configuration .env
./manage_network_config.sh --start-server
```

### Démarrage Manuel
```bash
# Arrêter les processus existants
pkill -f "php.*-S"

# Démarrer le serveur
cd public
php -S 0.0.0.0:8080 -t . ../system/rewrite.php
```

### Démarrage en Arrière-Plan
```bash
# Démarrer en arrière-plan
nohup ./manage_network_config.sh --start-server > server.log 2>&1 &

# Vérifier le processus
ps aux | grep php
```

---

## 🔒 CONFIGURATION SÉCURISÉE

### Variables d'Environnement de Production
```bash
# Éditer le fichier .env
nano .env

# Configuration sécurisée
CI_ENVIRONMENT=production
CI_DEBUG=false
LOG_LEVEL=error
APP_AUTO_DETECT_IP=true

# Base de données sécurisée
database.default.hostname=localhost
database.default.database=lyscol
database.default.username=lycol_user
database.default.password=VOTRE_MOT_DE_PASSE_SECURISE
database.default.port=3306

# Sécurité
encryption.key=hex2bin:VOTRE_CLE_UNIQUE_64_CARACTERES
security.csrfProtection=cookie
app.cookieSecure=true
app.cookieHTTPOnly=true
```

### Configuration Firewall
```bash
# Ouvrir le port 8080
sudo ufw allow 8080

# Ou avec iptables
sudo iptables -A INPUT -p tcp --dport 8080 -j ACCEPT
```

---

## 📊 MONITORING ET MAINTENANCE

### Scripts de Maintenance
```bash
# Sauvegarde automatique
./backup_database.sh

# Test de santé
./test_env_network_config.sh

# Redémarrage du serveur
pkill -f "php.*-S" && ./manage_network_config.sh --start-server
```

### Logs et Monitoring
```bash
# Vérifier les logs
tail -f server.log

# Vérifier les processus
ps aux | grep php

# Vérifier les connexions
netstat -tlnp | grep 8080
```

---

## 🔧 DÉPANNAGE

### Problèmes Courants

#### 1. Port 8080 Occupé
```bash
# Identifier le processus
sudo lsof -i :8080

# Arrêter le processus
sudo kill -9 PID

# Redémarrer
./manage_network_config.sh --start-server
```

#### 2. Erreur de Base de Données
```bash
# Vérifier la connexion
mysql -u lycol_user -p lyscol -e "SELECT 1;"

# Vérifier la configuration
./manage_network_config.sh --show-config
```

#### 3. Problème de Permissions
```bash
# Corriger les permissions
chmod -R 755 app/
chmod -R 755 public/
chmod +x *.sh
```

#### 4. Ressources Non Chargées
```bash
# Tester la configuration réseau
./test_env_network_config.sh

# Vérifier les logs
tail -f server.log
```

---

## 📈 OPTIMISATION PRODUCTION

### Configuration Apache/Nginx (Optionnel)
```apache
# Apache Virtual Host
<VirtualHost *:80>
    ServerName votre-domaine.com
    DocumentRoot /chemin/vers/codeigniter4-framework-68d1a58/public
    
    <Directory /chemin/vers/codeigniter4-framework-68d1a58/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Configuration SSL (Optionnel)
```bash
# Installation Certbot
sudo apt install -y certbot python3-certbot-apache

# Génération certificat
sudo certbot --apache -d votre-domaine.com
```

---

## ✅ CHECKLIST DE DÉPLOIEMENT

### Prérequis
- [ ] PHP 8.4+ installé avec extensions
- [ ] MariaDB/MySQL installé et configuré
- [ ] Port 8080 disponible
- [ ] Firewall configuré
- [ ] Archive LyCol extraite

### Installation
- [ ] Base de données restaurée
- [ ] Configuration .env appliquée
- [ ] Permissions corrigées
- [ ] Tests de validation réussis

### Production
- [ ] Mode production activé
- [ ] Sécurité renforcée
- [ ] Monitoring en place
- [ ] Sauvegardes automatiques

---

## 🎯 RÉSULTAT ATTENDU

Après déploiement, l'application sera accessible sur :
- **Toutes les IPs du serveur** : http://IP_SERVEUR:8080
- **Auto-adaptation** : Changement d'IP transparent
- **Sécurité** : Authentification obligatoire
- **Performance** : Optimisée pour la production

### Test Final
```bash
# Test complet
./test_env_network_config.sh

# Résultat attendu
✅ Configuration .env parfaite - Toutes les IPs fonctionnent
🏆 L'application s'adapte automatiquement aux changements d'IP
```

---

## 🏆 CONCLUSION

Ce guide permet un **déploiement professionnel** de LyCol sur n'importe quel serveur avec une configuration réseau intelligente qui s'adapte automatiquement aux changements d'IP.

**Mission accomplie : Déploiement expert et évolutif !** 🚀
