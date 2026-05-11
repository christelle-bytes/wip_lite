# Dev 6 — Partie décisionnelle & Reporting

---

## Commandes à exécuter pour le bon fonctionnement

Après avoir cloné le projet, exécuter dans l'ordre :

```bash
# 1. Installer les dépendances PHP
composer install

# 2. Installer les dépendances JavaScript
npm install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Configurer la base de données dans .env
# DB_DATABASE=wip_lite
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Générer la clé de l'application
php artisan key:generate

# 6. Lancer les migrations
php artisan migrate

# 7. Remplir la base avec les données de test
php artisan db:seed

# 8. Publier la config du package Excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

# 9. Lancer le projet (deux terminaux)
php artisan serve
npm run dev
```

> Important : activer l'extension `zip` dans `php.ini` (chercher `;extension=zip` et enlever le `;`)

---

## Ce qui a été fait

### 1. Redirection après login selon le rôle

Fichier : `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

Quand l'utilisateur se connecte, il est redirigé vers `/dashboard` :
```php
return redirect()->intended(route('dashboard'));
```

La route `/dashboard` est protégée — seuls les utilisateurs connectés y accèdent :
```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

---

### 2. Le DashboardController choisit la vue selon le rôle

Fichier : `app/Http/Controllers/DashboardController.php`

```php
public function index()
{
    $user     = Auth::user();
    $roleName = $user?->role?->name;

    return match ($roleName) {
        'Admin' => $this->adminDashboard(),
        'CP'    => $this->cpDashboard(),
        'SUP'   => $this->supDashboard(),
        'TC'    => $this->tcDashboard(),
    };
}
```

Chaque méthode fait ses requêtes SQL et envoie les données à la vue Vue via Inertia.

---

### 3. Les requêtes SQL (Query Builder Laravel)

Au lieu d'écrire du SQL brut, on utilise le Query Builder :

```php
// Compter les employés
$totalEmployees = DB::table('employees')->count();

// Campagnes actives
$activeCampaigns = DB::table('campaigns')->where('status', 'active')->count();

// Campagnes créées par mois sur 12 mois
$campaignsByMonth = DB::table('campaigns')
    ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as total'))
    ->where('created_at', '>=', now()->subMonths(12))
    ->groupBy('month')
    ->orderBy('month')
    ->get();
```

---

### 4. Les données deviennent des graphiques dans Vue

Fichiers : `resources/js/Pages/Dashboard*.vue`

**Étape A** — Vue reçoit les données comme props :
```js
const props = defineProps({ stats: Object, charts: Object });
```

**Étape B** — On transforme au format Chart.js avec un `computed` :
```js
const campaignsLineData = computed(() => ({
    labels: props.charts.campaignsByMonth.map(i => i.month),
    datasets: [{
        data: props.charts.campaignsByMonth.map(i => i.total),
        borderColor: '#6366F1',
    }]
}));
```

**Étape C** — Le graphique s'affiche dans le template :
```html
<Line :data="campaignsLineData" :options="lineOptions" />
```

---

### 5. Dashboards par rôle

| Rôle | Graphiques |
|------|-----------|
| Admin | Campagnes par mois (Line) + Évolution employés (Bar+Line) |
| CP | Taux de présence par employé (Bar) + Performance équipe (Bar groupé) |
| SUP | Écart par employé (Bar groupé) + Évolution heures 6 mois (Line double) |
| TC | Planning de la semaine (Bar) + Évolution heures perso (Line) + Campagnes en cours |

---

### 6. Page statistiques par campagne

Fichiers :
- `app/Http/Controllers/CampaignStatsController.php`
- `resources/js/Pages/Campaigns/CampaignStats.vue`
- Route : `GET /statistiques/campagnes`

Pour chaque campagne : nombre d'employés affectés, heures réelles, heures planifiées, écart en %.

---

### 7. Export Excel

Fichier : `app/Exports/CampaignStatsExport.php`

Package utilisé : `maatwebsite/excel`

```php
public function export()
{
    return Excel::download(new CampaignStatsExport, 'statistiques-campagnes.xlsx');
}
```

Route : `GET /statistiques/campagnes/export`

---

### 8. Export PDF

Fichiers :
- `app/Http/Controllers/CampaignStatsController.php` (méthode `exportPdf`)
- `resources/views/exports/campaigns-pdf.blade.php` (template HTML du PDF)

Package utilisé : `barryvdh/laravel-dompdf`

```php
public function exportPdf()
{
    $pdf = Pdf::loadView('exports.campaigns-pdf', ['campaigns' => $campaigns]);
    return $pdf->download('statistiques-campagnes.pdf');
}
```

Route : `GET /statistiques/campagnes/export-pdf`

---

## Flux complet résumé

```
Login
  → /dashboard
    → DashboardController lit le rôle
      → appelle la bonne méthode (admin/cp/sup/tc)
        → requêtes SQL (DB::table...)
          → Inertia::render envoie les données
            → Vue reçoit les données comme props
              → computed transforme au format Chart.js
                → graphiques affichés

Bouton "Exporter Excel" → /statistiques/campagnes/export → télécharge .xlsx
Bouton "Exporter PDF"   → /statistiques/campagnes/export-pdf → télécharge .pdf
```

---

## Fichiers créés / modifiés

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/DashboardController.php` | Logique des 4 dashboards |
| `app/Http/Controllers/ReportingController.php` | Stats globales |
| `app/Http/Controllers/CampaignStatsController.php` | Stats campagnes + exports |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Redirection login |
| `app/Exports/CampaignStatsExport.php` | Classe export Excel |
| `resources/views/exports/campaigns-pdf.blade.php` | Template HTML du PDF |
| `resources/js/Pages/DashboardAdmin.vue` | Dashboard Admin |
| `resources/js/Pages/DashboardCp.vue` | Dashboard Chef Plateau |
| `resources/js/Pages/DashboardSup.vue` | Dashboard Superviseur |
| `resources/js/Pages/DashboardTc.vue` | Dashboard Technicien |
| `resources/js/Pages/Campaigns/CampaignStats.vue` | Page stats campagnes |
| `resources/js/Pages/Reporting.vue` | Page reporting global |
| `routes/web.php` | Routes dashboard + exports |
