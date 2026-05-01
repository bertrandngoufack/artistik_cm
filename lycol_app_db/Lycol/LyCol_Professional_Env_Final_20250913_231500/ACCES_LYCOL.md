# 🔐 ACCÈS ET IDENTIFIANTS LYCOL

## 🌐 **LIENS D'ACCÈS PRINCIPAUX**

### **1. Interface principale**
```
URL: http://localhost:8081/
Description: Page d'accueil avec présentation du système
```

### **2. Console d'administration**
```
URL: http://localhost:8081/admin
Description: Interface d'administration complète
```

### **3. Espace parents**
```
URL: http://localhost:8081/parents
Description: Accès pour les parents d'élèves
```

### **4. Interface mobile (enseignants)**
```
URL: http://localhost:8081/mobile
Description: Saisie des notes via mobile
```

### **5. API Documentation**
```
URL: http://localhost:8081/api/docs
Description: Documentation Swagger des API
```

---

## 👤 **COMPTES UTILISATEURS PAR DÉFAUT**

### **Administrateur principal**
```
URL: http://localhost:8081/admin
Utilisateur: admin
Mot de passe: admin123
Rôle: SUPER_ADMIN
Permissions: Tous les droits sur tous les modules
```

### **Directeur d'établissement**
```
URL: http://localhost:8081/admin
Utilisateur: directeur
Mot de passe: directeur123
Rôle: DIRECTEUR
Permissions: 
- Lecture sur Économat
- Tous droits sur Scolarité, Études, Examens, Statistiques
- Lecture sur Bibliothèque
- Tous droits sur Messagerie
```

### **Secrétaire administratif**
```
URL: http://localhost:8081/admin
Utilisateur: secretaire
Mot de passe: secretaire123
Rôle: SECRETAIRE
Permissions:
- Lecture/Écriture sur Économat
- Lecture/Écriture sur Scolarité
- Tous droits sur Bibliothèque
```

### **Enseignant**
```
URL: http://localhost:8081/admin
Utilisateur: enseignant
Mot de passe: enseignant123
Rôle: ENSEIGNANT
Permissions:
- Lecture sur Scolarité
- Lecture sur Études
- Lecture/Écriture sur Examens
- Lecture sur Bibliothèque
```

---

## 🔑 **ACCÈS PARENTS (PAR MATRICULE)**

### **Élèves de test créés**
```
URL: http://localhost:8081/parents

1. Alice Mvondo
   Matricule: 2024-001
   Année de naissance: 2008

2. Boris Etoa
   Matricule: 2024-002
   Année de naissance: 2007

3. Claire Nkoudou
   Matricule: 2024-003
   Année de naissance: 2008
```

### **Fonctionnalités parents**
- Consultation des bulletins
- Suivi des absences
- Communication avec l'école
- Historique des paiements

---

## 📱 **ACCÈS MOBILE ENSEIGNANTS**

### **Interface mobile**
```
URL: http://localhost:8081/mobile
Code enseignant: enseignant
```

### **Fonctionnalités mobile**
- Saisie rapide des notes
- Validation automatique
- Synchronisation en temps réel
- Interface optimisée pour mobile

---

## 🔧 **INITIALISATION DU SYSTÈME**

### **1. Exécution du script SQL**
```bash
# Connexion à MariaDB
mysql -h 100.69.65.33 -P 13306 -u root -p

# Exécution du script
source database/lycol_schema.sql
```

### **2. Création des utilisateurs par défaut**
```bash
# Exécution du script d'initialisation
php init_users.php
```

### **3. Démarrage du serveur**
```bash
# Serveur de développement
php spark serve --port 8081 --host 0.0.0.0
```

---

## 🔐 **SÉCURITÉ ET CONFIGURATION**

### **Changement des mots de passe**
⚠️ **IMPORTANT** : Changez les mots de passe après la première connexion

1. Connectez-vous en tant qu'admin
2. Allez dans Sécurité → Utilisateurs
3. Modifiez les mots de passe de tous les comptes

### **Configuration SMTP**
1. Console d'administration → Configuration → Messagerie
2. Configurez les paramètres SMTP :
   - Serveur SMTP
   - Port
   - Nom d'utilisateur
   - Mot de passe

### **Configuration SMS/WhatsApp**
1. Console d'administration → Configuration → Fournisseurs
2. Ajoutez vos fournisseurs :
   - API SMS
   - API WhatsApp
   - Clés d'authentification

### **Activation de licence**
1. Console d'administration → Sécurité → Licences
2. Générez une nouvelle licence
3. Activez la licence pour votre établissement

---

## 📊 **MODULES ET PERMISSIONS**

### **Module Économat**
- **Accès :** Admin, Directeur (lecture), Secrétaire
- **Fonctionnalités :** Inscriptions, pensions, budget, salaires

### **Module Scolarité**
- **Accès :** Admin, Directeur, Secrétaire, Enseignant (lecture)
- **Fonctionnalités :** Suivi élèves, absences, discipline

### **Module Études**
- **Accès :** Admin, Directeur, Enseignant (lecture)
- **Fonctionnalités :** Classes, matières, emplois du temps

### **Module Examens**
- **Accès :** Admin, Directeur, Enseignant
- **Fonctionnalités :** Notes, bulletins, conseils de classe

### **Module Statistiques**
- **Accès :** Admin, Directeur
- **Fonctionnalités :** Analyses, rapports, taux de réussite

### **Module Bibliothèque**
- **Accès :** Admin, Directeur (lecture), Secrétaire, Enseignant (lecture)
- **Fonctionnalités :** Livres, emprunts, abonnés

### **Module Messagerie**
- **Accès :** Admin, Directeur, Secrétaire
- **Fonctionnalités :** SMS, email, WhatsApp

### **Module Sécurité**
- **Accès :** Admin uniquement
- **Fonctionnalités :** Utilisateurs, rôles, licences

---

## 🆘 **DÉPANNAGE**

### **Problème de connexion**
```bash
# Vérifier que le serveur fonctionne
curl http://localhost:8081/

# Vérifier les logs
tail -f writable/logs/log-*.php
```

### **Problème de base de données**
```bash
# Tester la connexion
mysql -h 100.69.65.33 -P 13306 -u root -p -e "SELECT 1;"

# Vérifier les tables
mysql -h 100.69.65.33 -P 13306 -u root -p lycol_db -e "SHOW TABLES;"
```

### **Problème de permissions**
```bash
# Corriger les permissions
chmod -R 755 writable/
chown -R www-data:www-data writable/
```

---

## 📞 **SUPPORT**

### **Documentation**
- **Guide utilisateur :** `http://localhost:8081/docs`
- **API Documentation :** `http://localhost:8081/api/docs`
- **README complet :** `README_LYCOL.md`

### **Contact**
- **Email :** support@lycol.cm
- **Téléphone :** +237 XXX XXX XXX
- **Forum :** https://forum.lycol.cm

---

## ⚠️ **AVERTISSEMENTS IMPORTANTS**

1. **Sécurité** : Changez tous les mots de passe par défaut
2. **Licence** : Activez une licence valide avant utilisation
3. **Sauvegarde** : Effectuez des sauvegardes régulières
4. **Mise à jour** : Maintenez le système à jour
5. **Formation** : Formez les utilisateurs aux bonnes pratiques

---

**🎓 LyCol - Solution de Gestion Scolaire Complète**
*Développé pour le système éducatif camerounais*




