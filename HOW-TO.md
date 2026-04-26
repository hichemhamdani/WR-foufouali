# Créer un nouveau projet client depuis BP

> Tout ce qui est sous `hichemhamdani` changera en WR une fois tout testé et fonctionnel.

Guide complet pour démarrer un nouveau site WordPress depuis le blueprint Webrocket.
À suivre dans l'ordre, étape par étape.

---

## Prérequis

Avant de commencer, tu dois avoir installé sur ton PC :

- [Local by Flywheel](https://localwp.com/) — pour le développement local
- [Git](https://git-scm.com/) — pour la gestion du code
- [FileZilla](https://filezilla-project.org/) — pour transférer des fichiers sur SiteGround
- Un terminal (Git Bash, ou le terminal intégré dans VS Code)
- Accès au GitHub de Webrocket (`hichemhamdani`)
- Accès au Site Tools du site SiteGround concerné

Tu dois aussi avoir reçu de WR :

- Le fichier `bp-starter.sql` (base de données de départ — trop gros pour Git, partagé en interne) : demander à Hichem
- La clé privée SSH `webrocket_sg` (à placer dans `C:\Users\TON_NOM\.ssh\webrocket_sg`) : demander à Hichem
- Le token GitHub `GH_TOKEN` : demander à Hichem

---

## Étape 1 — Créer le dépôt GitHub du projet client

1. Aller sur [github.com/hichemhamdani](https://github.com/hichemhamdani)
2. Cliquer **New repository**
3. Nommer le repo : `nom-du-client-webrocket` (ex : `dupont-webrocket`)
4. Laisser le repo **vide** (ne pas cocher README ni .gitignore)
5. Cliquer **Create repository**
6. Copier l'URL du repo (ex : `https://github.com/hichemhamdani/dupont-webrocket.git`)

---

## Étape 2 — Cloner BP

Ouvre un terminal et exécute :

```bash
git clone https://github.com/hichemhamdani/BP-Webrocket.git dupont-webrocket
```

---

## Étape 3 — Créer le site dans Local by Flywheel

1. Ouvrir **Local by Flywheel**
2. Cliquer **+** en bas à gauche → **Create a new site**
3. Nom du site : `dupont` (ou le nom du client)
4. Choisir un domaine local : `dupont.local`
5. Laisser les paramètres par défaut → **Create site**
6. Une fois créé, aller dans l'onglet **Database** → noter le **port MySQL** affiché (ex : `10045`)

> **Important** : ce port est unique pour chaque site Local. Tu en auras besoin dans l'étape 5.

---

## Étape 4 — Placer les fichiers du projet dans Local

Local a créé un dossier vide pour ton site. Tu dois y mettre les fichiers du projet.

1. Dans Local, clic droit sur le site → **Go to site folder**
2. Ouvrir le dossier `app/public/`
3. **Supprimer tout son contenu** (c'est une installation WordPress vide de Local)
4. **Copier-coller** à la place le contenu du dossier `dupont-webrocket/` cloné à l'étape 2
5. Ouvrir un terminal dans `app/public/` et exécuter :

```bash
git init
git remote add origin https://github.com/hichemhamdani/dupont-webrocket.git
git add .
git commit -m "Initial commit depuis BP-Webrocket"
git branch -M main
git push -u origin main
```

---

## Étape 5 — Créer le wp-config.php local

Le fichier `wp-config.php` n'est pas dans Git (il contient des mots de passe). Tu dois le créer manuellement.

1. Dans le dossier `app/public/`, copier `wp-config-sample.php` et renommer la copie en `wp-config.php`
2. Ouvrir `wp-config.php` avec VS Code
3. Modifier ces lignes :

```php
define( 'DB_NAME', 'local' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', '127.0.0.1:10045' );  // ← ton port MySQL noté à l'étape 3
$table_prefix = 'yqj_';
```

> **Erreur courante** : ne pas mettre `localhost` — Local by Flywheel n'accepte pas `localhost`,
> il faut obligatoirement `127.0.0.1:PORT`.

---

## Étape 6 — Vider et importer la base de données en local

**6a. Vider la base existante**

Dans Local, clic droit sur le site → **Open site shell**, puis :

```bash
mysql -u root -proot -e "DROP DATABASE local; CREATE DATABASE local;"
```

Vérifie dans AdminNeo que tout a bien été supprimé.

**6b. Importer la base BP**

Toujours dans le site shell :

```bash
mysql -u root -proot local < "C:\Users\TON_NOM\Downloads\bp-starter.sql"
```

Remplacer `TON_NOM` par ton nom de session Windows (ex : `hamda`).

L'import peut prendre quelques minutes — attends que le prompt revienne avant de continuer.

---

## Étape 7 — Mettre à jour les URLs en local

Dans AdminNeo → onglet **SQL**, commence par vérifier vers où pointent les URLs :

```sql
SELECT option_name, option_value FROM yqj_options WHERE option_name IN ('siteurl', 'home');
```

Tu verras un résultat du type :

| option_name | option_value |
|-------------|--------------|
| home | http://hich2.local |
| siteurl | http://hich2.local |

Remplace l'ancienne URL par celle de ton nouveau site local :

```sql
UPDATE yqj_options
SET option_value = REPLACE(option_value, 'hich2.local', 'dupont.local')
WHERE option_name IN ('siteurl', 'home');
```

> **Note** : remplace `hich2.local` par ce que tu as vu dans le SELECT, et `dupont.local` par le domaine de ton nouveau site.

Ça doit retourner **2 lignes modifiées**.

---

## Étape 8 — Vérifier que le site fonctionne en local

1. Dans Local, cliquer **Open site** → `dupont.local` doit s'afficher dans le navigateur
2. Aller sur `dupont.local/wp-admin`
3. Se connecter avec les credentials de la base BP

Si tu vois le site et l'admin → tout est bon, tu peux passer à la configuration SiteGround.

> **Si tu n'as pas les credentials**, réinitialise le mot de passe via AdminNeo :
> ```sql
> UPDATE yqj_users
> SET user_pass = MD5('nouveaumotdepasse')
> WHERE user_login = 'admin';
> ```

---

## Étape 9 — Configurer le site sur SiteGround

**9a. Créer l'instance et installer WordPress**

1. Créer une instance sur SiteGround et installer WordPress
2. Aller dans **Site Tools** → **Devs** → **SSH** → créer un utilisateur SSH
3. Aller dans **Devs** → **SSH Keys Manager** → **Create/Import** → **Import**
4. Coller la clé publique `webrocket_sg` (demander à Hichem)
5. Cliquer **Authorize**

**9b. Se connecter en SSH**

Dans un terminal, utilise les credentials affichés dans Site Tools → Devs → SSH :

```bash
ssh -p PORT -i C:\Users\TON_NOM\.ssh\webrocket_sg USERNAME@ssh.TONSITE.roxy.cloud
```

**9c. Vider la base et importer BP**

Une fois connecté en SSH :

```bash
# Voir les bases disponibles (noter le nom de la base)
mysql -u NOM_USER_DB -p -e "SHOW DATABASES;"

# Vider la base
mysql -u NOM_USER_DB -p -e "DROP DATABASE NOM_DB; CREATE DATABASE NOM_DB;"
```

Uploader `bp-starter.sql.gz` via **FileZilla** (SFTP) à côté de `public_html`, puis importer :

```bash
# Naviguer jusqu'au dossier contenant le fichier
cd ~/www/TONSITE.roxy.cloud/

# Vérifier que le fichier est là
ls

# Importer
zcat bp-starter.sql.gz | mysql -u NOM_USER_DB -p NOM_DB
```

**9d. Initialiser Git sur SiteGround**

```bash
cd ~/www/TONSITE.roxy.cloud/public_html
git init
git remote add origin https://hichemhamdani:GH_TOKEN@github.com/hichemhamdani/dupont-webrocket.git
git fetch origin main
git reset --hard origin/main
```

**9e. Corriger le table_prefix dans wp-config.php**

SiteGround génère un préfixe de table aléatoire (ex : `rle_`). Il faut le remplacer par `yqj_`.

```bash
# Voir le préfixe actuel
grep table_prefix wp-config.php
```

Puis remplacer (adapte `rle_` par ce que tu vois) :

```bash
python3 -c "
content = open('wp-config.php').read()
content = content.replace(\"'rle_'\", \"'yqj_'\")
open('wp-config.php', 'w').write(content)
print('OK')
"
```

> **Pourquoi ?** Sans ça, WordPress cherche les tables avec le mauvais préfixe et affiche
> "Error establishing a database connection" même si la base est correctement importée.

**9f. Mettre à jour les URLs sur SiteGround**

Dans phpMyAdmin → sélectionner la base → onglet **SQL** :

```sql
SELECT option_name, option_value FROM yqj_options WHERE option_name IN ('siteurl', 'home');
```

Puis mettre à jour (remplace l'ancienne URL par le domaine SiteGround) :

```sql
UPDATE yqj_options
SET option_value = REPLACE(option_value, 'LANCIENNE_URL', 'dupont.roxy.cloud')
WHERE option_name IN ('siteurl', 'home');
```

---

## Étape 10 — Transférer les médias sur SiteGround

Les images et médias ne sont pas dans Git (trop lourds). Il faut les transférer manuellement une fois via FileZilla.

1. Ouvrir **FileZilla** et se connecter en SFTP avec les credentials SSH du site
2. À gauche (local) : naviguer vers `C:\Users\TON_NOM\Local Sites\bp\app\public\wp-content\uploads\`
3. À droite (serveur) : naviguer vers `/home/USERNAME/www/TONSITE.roxy.cloud/public_html/wp-content/uploads/`
4. Sélectionner tout le contenu du dossier local et le glisser vers le serveur

> Ce transfert est unique — les nouveaux médias ajoutés via l'admin WordPress seront ensuite directement sur le serveur.

---

## Étape 11 — Configurer le déploiement automatique GitHub Actions


**10a. Mettre à jour deploy.yml**

Dans le projet, ouvrir `.github/workflows/deploy.yml` et remplacer `NOM-DU-REPO` par le vrai nom du repo. Adapter aussi la branche (`main` ou `dev` selon ton workflow) :

```yaml
git pull https://hichemhamdani:${{ secrets.GH_TOKEN }}@github.com/hichemhamdani/dupont-webrocket.git main
```

**10b. Ajouter les secrets GitHub**

Sur GitHub → repo → **Settings** → **Secrets and variables** → **Actions** :

| Secret | Valeur |
|--------|--------|
| `SSH_HOST` | Hostname SiteGround (dans Site Tools → Devs → SSH) |
| `SSH_USERNAME` | Username SSH du site SiteGround |
| `SSH_PORT` | Port SSH (généralement `18765`) |
| `SSH_PATH` | `/home/USERNAME/www/TONSITE.roxy.cloud/public_html` |
| `SSH_PRIVATE_KEY` | Contenu de `C:\Users\TON_NOM\.ssh\webrocket_sg` |
| `GH_TOKEN` | Token GitHub (demander à Hichem) |

> **Erreur courante pour `SSH_PRIVATE_KEY`** : copier la clé avec PowerShell pour éviter
> les problèmes de fins de ligne Windows :
> ```powershell
> Get-Content "C:\Users\TON_NOM\.ssh\webrocket_sg" -Raw | Set-Clipboard
> ```
> Puis coller directement dans le champ GitHub.

---

## Étape 12 — Tester le déploiement automatique

1. Faire une modification dans un fichier du thème en local
2. Commiter et pusher :

```bash
git add .
git commit -m "Test déploiement"
git push origin main
```

3. Aller sur GitHub → onglet **Actions** → vérifier que le workflow devient vert
4. Rafraîchir le site SiteGround pour voir la modification

---

## Résumé des erreurs courantes

| Erreur | Cause | Solution |
|--------|-------|----------|
| `Error establishing a database connection` en local | `DB_HOST = localhost` | Utiliser `127.0.0.1:PORT` (port dans Local → Database) |
| `Error establishing a database connection` sur SiteGround | Mauvais `table_prefix` ou credentials incorrects | Vérifier avec `grep table_prefix wp-config.php` via SSH |
| `0 lignes modifiées` dans la requête SQL des URLs | Les URLs pointent vers un autre domaine | Vérifier d'abord avec `SELECT option_value FROM yqj_options WHERE option_name = 'siteurl'` |
| `ssh: no key found` dans GitHub Actions | Clé copiée avec mauvaises fins de ligne | Copier avec PowerShell : `Get-Content ... -Raw \| Set-Clipboard` |
| `504 Gateway Timeout` sur import phpMyAdmin | Fichier trop gros | Passer par FileZilla (SFTP) + import SSH |
| `Permission denied` SSH SiteGround | Clé pas autorisée sur ce site | Importer la clé publique dans Site Tools → SSH Keys Manager |

---

## Travailler avec Claude Code

Claude lit automatiquement `CLAUDE.md` au démarrage. Pour reprendre le contexte après une pause :

```
Lis tous les commits git et résume ce qui a été fait et pourquoi.
```

Tous les commits faits avec Claude portent le tag `Co-Authored-By: Claude Sonnet 4.6` dans l'historique Git.
