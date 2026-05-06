# Identifiants de Connexion (Seeder)

## Rôles & Utilisateurs de Test

| Rôle | Nom | Email | Mot de passe |
|------|-----|-------|--------------|
| **Admin (RH)** | RH Admin | admin@rh.com | password123 |
| **CP (Chef Plateau)** | Chef Plateau | cp@rh.com | password123 |
| **SUP (Superviseur)** | Superviseur | sup@rh.com | password123 |
| **TC (Employé)** | Employé TC | tc@rh.com | password123 |

**Redirections après login :**
- Admin → /users (gestion utilisateurs)
- CP → /planning (placeholder)
- SUP → /timesheets (placeholder)
- TC → /dashboard

**Commandes pour réinitialiser :**
```bash
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

**Accès :**
- Login : http://localhost:8000/login
- Users (admin only) : http://localhost:8000/users

