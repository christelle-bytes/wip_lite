<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 20px; }
        h1 { font-size: 20px; font-weight: 900; color: #0f172a; margin-bottom: 4px; }
        p.subtitle { font-size: 11px; color: #94a3b8; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        thead tr { background-color: #0f172a; color: #fff; }
        thead th { padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; }
        .badge { padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .badge-terminee { background: #f1f5f9; color: #475569; }
        .gap-pos { color: #059669; font-weight: bold; }
        .gap-neg { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 32px; font-size: 10px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>

    <h1>Statistiques par campagne</h1>
    <p class="subtitle">Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Campagne</th>
                <th>Statut</th>
                <th>Employés</th>
                <th>Heures réelles</th>
                <th>Heures planifiées</th>
                <th>Écart</th>
            </tr>
        </thead>
        <tbody>
            @foreach($campaigns as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>
                    <span class="badge badge-{{ $c->status === 'active' ? 'active' : ($c->status === 'terminée' ? 'terminee' : 'inactive') }}">
                        {{ $c->status }}
                    </span>
                </td>
                <td>{{ $c->total_employees }}</td>
                <td>{{ $c->real_hours }}h</td>
                <td>{{ $c->planned_hours }}h</td>
                <td class="{{ $c->gap < 0 ? 'gap-neg' : 'gap-pos' }}">
                    {{ $c->gap > 0 ? '+' : '' }}{{ $c->gap }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">GRH Gestion RH — Rapport confidentiel</div>

</body>
</html>
