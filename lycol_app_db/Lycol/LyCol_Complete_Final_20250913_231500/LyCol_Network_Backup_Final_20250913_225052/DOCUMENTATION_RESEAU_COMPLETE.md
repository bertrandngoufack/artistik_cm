# DOCUMENTATION RÉSEAU COMPLÈTE - LYCCOL
## Système de Gestion Scolaire Intégré - Configuration Réseau

**Date de création :** 13 Septembre 2025  
**Version :** Configuration Réseau Finalisée  
**Port de fonctionnement :** 8080 (toutes interfaces)  
**Adresses IP testées :** 192.168.1.12, 192.168.1.11  

---

## 🌐 CONFIGURATION RÉSEAU

### Accès via Adresse IP
L'application est maintenant configurée pour être accessible via toutes les interfaces réseau :

**Commande de démarrage réseau :**
```bash
cd codeigniter4-framework-68d1a58/public
php -S 0.0.0.0:8080 -t . ../system/rewrite.php
```

**URLs d'accès :**
- Local : `http://localhost:8080`
- Réseau : `http://192.168.1.12:8080` (ou toute autre IP du serveur)
- Authentification : `http://192.168.1.12:8080/auth/login`
- Dashboard admin : `http://192.168.1.12:8080/admin/dashboard`

### Ressources Statiques Vérifiées
Toutes les ressources se chargent correctement via IP :

**CSS :**
- ✅ Bulma CSS : `http://192.168.1.12:8080/assets/bulma/css/bulma.min.css` (207KB)
- ✅ FontAwesome : `http://192.168.1.12:8080/assets/fontawesome/css/all.min.css` (89KB)
- ✅ Style personnalisé : `http://192.168.1.12:8080/assets/css/style.css` (6KB)

**JavaScript :**
- ✅ Bulma JS : `http://192.168.1.12:8080/assets/bulma/js/bulma.js` (8KB)

**Images :**
- ✅ Logo : `http://192.168.1.12:8080/assets/images/logo.png`
- ✅ Favicon : `http://192.168.1.12:8080/favicon.ico` (5KB)

---

## 🔐 SÉCURITÉ RÉSEAU

### Authentification Réseau
L'authentification fonctionne parfaitement via l'adresse IP :

**Test d'authentification :**
```bash
curl -c cookies.txt -X POST -d "username=admin&password=admin123" \
     http://192.168.1.12:8080/auth/authenticate
```

**Accès dashboard avec session :**
```bash
curl -b cookies.txt http://192.168.1.12:8080/admin/dashboard
```

### Filtre d'Authentification
Le filtre `AuthFilter` protège toutes les routes admin même via l'accès réseau :
- Vérification du `user_id` et `user_role` dans la session
- Redirection automatique vers `/auth/login` si non authentifié
- Rôles autorisés : `admin`, `directeur`, `secretaire`, `enseignant`

---

## 📊 LOGS D'ACCÈS RÉSEAU

Le serveur PHP développement affiche les accès réseau en temps réel :
```
[Sat Sep 13 22:49:11 2025] 192.168.1.11:12336 Accepted
[Sat Sep 13 22:49:15 2025] 192.168.1.11:12349 [200]: GET /favicon.ico
[Sat Sep 13 22:49:17 2025] 192.168.1.11:12351 Accepted
[Sat Sep 13 22:50:06 2025] 192.168.1.11:12434 Accepted
```

**IPs confirmées :**
- 192.168.1.11 : Accès client confirmé
- 192.168.1.12 : IP du serveur confirmée

---

## 🚀 DÉPLOIEMENT RÉSEAU

### Étapes de Configuration
1. **Arrêter les services existants :**
   ```bash
   pkill -f "php.*-S"
   pkill -f "php spark serve"
   ```

2. **Démarrer le serveur réseau :**
   ```bash
   cd public
   php -S 0.0.0.0:8080 -t . ../system/rewrite.php
   ```

3. **Vérifier les interfaces :**
   ```bash
   netstat -tlnp | grep 8080
   # Devrait afficher : tcp 0 0 0.0.0.0:8080
   ```

4. **Obtenir l'IP du serveur :**
   ```bash
   hostname -I
   # Exemple : 192.168.1.12 172.18.0.1 172.17.0.1
   ```

### Configuration Firewall (si nécessaire)
```bash
# Ubuntu/Debian
sudo ufw allow 8080/tcp

# CentOS/RHEL
sudo firewall-cmd --add-port=8080/tcp --permanent
sudo firewall-cmd --reload
```

---

## 🔧 DÉPANNAGE RÉSEAU

### Problèmes Courants
1. **Port déjà utilisé :**
   ```bash
   netstat -tlnp | grep 8080
   pkill -f "php.*8080"
   ```

2. **Accès refusé depuis d'autres machines :**
   - Vérifier que le serveur écoute sur `0.0.0.0:8080`
   - Contrôler le firewall
   - Vérifier la configuration du router/switch

3. **Ressources CSS/JS non chargées :**
   - Tous les assets sont testés et fonctionnels
   - Vérifier les chemins dans les templates (utilisation de `localhost`)

### Tests de Connectivité
```bash
# Depuis le serveur
curl -I http://localhost:8080

# Depuis une autre machine du réseau
curl -I http://192.168.1.12:8080

# Test de l'authentification
curl -X POST -d "username=admin&password=admin123" \
     http://192.168.1.12:8080/auth/authenticate
```

---

## 📋 MODULES RÉSEAU TESTÉS

### Modules Fonctionnels via IP
- ✅ **Accueil** : `http://192.168.1.12:8080/`
- ✅ **Authentification** : `http://192.168.1.12:8080/auth/login`
- ✅ **Dashboard Admin** : `http://192.168.1.12:8080/admin/dashboard`
- ✅ **Économat** : `http://192.168.1.12:8080/admin/economat`
- ✅ **Scolarité** : `http://192.168.1.12:8080/admin/scolarite`
- ✅ **Études** : `http://192.168.1.12:8080/admin/etudes`
- ✅ **Examens** : `http://192.168.1.12:8080/admin/examens`

### Ressources Statiques
- ✅ **CSS Bulma** : Framework CSS principal
- ✅ **CSS FontAwesome** : Icônes
- ✅ **CSS Personnalisé** : Styles de l'application
- ✅ **JavaScript Bulma** : Interactions utilisateur
- ✅ **Images et Favicon** : Assets graphiques

---

## 🎯 PERFORMANCE RÉSEAU

### Tailles des Ressources
- CSS Bulma : 207KB
- CSS FontAwesome : 89KB
- CSS Style : 6KB
- JavaScript Bulma : 8KB
- Favicon : 5KB

### Temps de Réponse
Tous les assets répondent instantanément avec un code HTTP 200 OK.

---

## 📞 SUPPORT

Pour toute assistance technique concernant la configuration réseau :
1. Vérifier les logs du serveur PHP
2. Contrôler la connectivité réseau
3. Valider l'authentification via IP
4. Tester le chargement des ressources statiques

**Configuration testée et validée le 13 Septembre 2025**
