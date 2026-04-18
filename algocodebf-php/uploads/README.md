# Dossier Uploads

Ce dossier contient tous les fichiers uploadés par les utilisateurs.

## Structure

```
uploads/
├── profiles/         # Photos de profil
├── cvs/             # CV des utilisateurs (PDF)
├── documents/       # Documents de vérification
├── tutorials/       # Fichiers des tutoriels
└── blog/           # Images des articles de blog
```

## Sécurité

- ✅ Validation du type MIME
- ✅ Validation de la taille des fichiers
- ✅ Noms de fichiers sécurisés (hash unique)
- ✅ Dossier protégé par .htaccess (pour documents sensibles)

## Permissions

Ce dossier doit avoir les permissions d'écriture (755 ou 777 selon la configuration serveur).

## Tailles maximales

- Photos de profil : 5 MB
- CV : 2 MB
- Documents : 5 MB
- Tutoriels : 5 MB
- Images blog : 5 MB

## Types de fichiers autorisés

### Images
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WEBP (.webp)

### Documents
- PDF (.pdf)

### Vidéos
- MP4 (.mp4)
- MPEG (.mpeg)

