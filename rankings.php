<?php
require_once 'php/db.php';

$pageTitle = 'Rankings - L2 Savior';

// Tab kezelés
$tab = $_GET['tab'] ?? 'level';

// Rankings lekérése
try {
    $pdo = getDBConnection(DB_GS);
    
    if ($tab === 'level') {
        $stmt = $pdo->query("
            SELECT char_name, level, base_class, online, account_name 
            FROM characters 
            ORDER BY level DESC, exp DESC 
            LIMIT 50
        ");
        $rankings = $stmt->fetchAll();
    } elseif ($tab === 'pvp') {
        $stmt = $pdo->query("
            SELECT char_name, level, base_class, online, pvpkills, account_name 
            FROM characters 
            WHERE pvpkills > 0 
            ORDER BY pvpkills DESC 
            LIMIT 50
        ");
        $rankings = $stmt->fetchAll();
    } elseif ($tab === 'pk') {
        $stmt = $pdo->query("
            SELECT char_name, level, base_class, online, pkkills, account_name 
            FROM characters 
            WHERE pkkills > 0 
            ORDER BY pkkills DESC 
            LIMIT 50
        ");
        $rankings = $stmt->fetchAll();
    } elseif ($tab === 'clan') {
        $stmt = $pdo->query("
            SELECT cd.clan_name, cd.clan_level, cd.reputation_score, COUNT(c.charId) as member_count, cd.ally_name
            FROM clan_data cd
            LEFT JOIN characters c ON c.clanid = cd.clan_id
            GROUP BY cd.clan_id
            ORDER BY cd.reputation_score DESC, cd.clan_level DESC
            LIMIT 50
        ");
        $rankings = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $rankings = [];
    $error = "Hiba az adatok betöltésekor.";
}

// Class names mapping
$classNames = [
    0 => 'Human Fighter', 1 => 'Warrior', 2 => 'Gladiator', 3 => 'Warlord',
    // ... (teljes lista a régi fájlból)
];

function getClassName($classId, $classNames) {
    return $classNames[$classId] ?? "Unknown (ID: $classId)";
}

include 'includes/header.php';
?>

<style>
    .rankings-container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
    .rankings-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .rankings-header h1 {
        background: linear-gradient(90deg, #ffd700, #50c878);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    .filter-tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 0.8rem 1.5rem;
        background: rgba(21, 21, 35, 0.6);
        border: 1px solid rgba(255, 215, 0, 0.2);
        border-radius: 8px;
        color: #b8b8c8;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 600;
    }
    .filter-tab:hover, .filter-tab.active {
        background: rgba(255, 215, 0, 0.2);
        border-color: #ffd700;
        color: #ffd700;
    }
    .data-table {
        background: rgba(21, 21, 35, 0.6);
        border: 1px solid rgba(255, 215, 0, 0.15);
        border-radius: 12px;
        overflow-x: auto;
    }
    .data-table table { width: 100%; border-collapse: collapse; }
    .data-table th {
        background: rgba(255, 215, 0, 0.1);
        color: #ffd700;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
    }
    .data-table td {
        padding: 1rem;
        color: #b8b8c8;
        border-bottom: 1px solid rgba(255, 215, 0, 0.05);
    }
    .data-table tr:hover { background: rgba(255, 215, 0, 0.05); }
    .rank-medal { font-size: 1.5rem; }
</style>

<div class="rankings-container">
    <div class="rankings-header">
        <h1>🏆 Rankings</h1>
        <p style="color: #b8b8c8;">A szerver legjobb játékosai</p>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="rankings.php?tab=level" class="filter-tab <?php echo $tab === 'level' ? 'active' : ''; ?>">
            📊 Top Level
        </a>
        <a href="rankings.php?tab=pvp" class="filter-tab <?php echo $tab === 'pvp' ? 'active' : ''; ?>">
            ⚔️ Top PvP
        </a>
        <a href="rankings.php?tab=pk" class="filter-tab <?php echo $tab === 'pk' ? 'active' : ''; ?>">
            ☠️ Top PK
        </a>
        <a href="rankings.php?tab=clan" class="filter-tab <?php echo $tab === 'clan' ? 'active' : ''; ?>">
            🏰 Top Clan
        </a>
    </div>

    <!-- Rankings Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Helyezés</th>
                    <?php if ($tab === 'clan'): ?>
                        <th>Clan név</th>
                        <th>Level</th>
                        <th>Tagok</th>
                        <th>Reputation</th>
                        <th>Alliance</th>
                    <?php else: ?>
                        <th>Karakter</th>
                        <th>Osztály</th>
                        <th>Level</th>
                        <?php if ($tab === 'pvp'): ?>
                            <th>PvP Kills</th>
                        <?php elseif ($tab === 'pk'): ?>
                            <th>PK Kills</th>
                        <?php endif; ?>
                        <th>Státusz</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rankings)): ?>
                    <?php $position = 1; ?>
                    <?php foreach ($rankings as $rank): ?>
                        <tr>
                            <td>
                                <?php if ($position === 1): ?>
                                    <span class="rank-medal">🥇</span>
                                <?php elseif ($position === 2): ?>
                                    <span class="rank-medal">🥈</span>
                                <?php elseif ($position === 3): ?>
                                    <span class="rank-medal">🥉</span>
                                <?php else: ?>
                                    <strong>#<?php echo $position; ?></strong>
                                <?php endif; ?>
                            </td>
                            
                            <?php if ($tab === 'clan'): ?>
                                <td><strong style="color: #ffd700;"><?php echo htmlspecialchars($rank['clan_name']); ?></strong></td>
                                <td><?php echo $rank['clan_level']; ?></td>
                                <td><?php echo $rank['member_count']; ?> fő</td>
                                <td style="color: #50c878;"><?php echo number_format($rank['reputation_score']); ?></td>
                                <td><?php echo $rank['ally_name'] ? htmlspecialchars($rank['ally_name']) : '-'; ?></td>
                            <?php else: ?>
                                <td><strong style="color: #ffd700;"><?php echo htmlspecialchars($rank['char_name']); ?></strong></td>
                                <td><?php echo getClassName($rank['base_class'], $classNames); ?></td>
                                <td><strong><?php echo $rank['level']; ?></strong></td>
                                <?php if ($tab === 'pvp'): ?>
                                    <td style="color: #50c878; font-weight: 600;"><?php echo number_format($rank['pvpkills']); ?></td>
                                <?php elseif ($tab === 'pk'): ?>
                                    <td style="color: #dc2626; font-weight: 600;"><?php echo number_format($rank['pkkills']); ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($rank['online']): ?>
                                        <span style="color: #50c878;">🟢 Online</span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">⚫ Offline</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php $position++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem;">
                            Jelenleg nincs adat ebben a kategóriában.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="text-align: center; color: #9ca3af; margin-top: 2rem; font-size: 0.9rem;">
        ℹ️ A ranglisták 10 percenként frissülnek automatikusan.<br>
        Utolsó frissítés: <?php echo date('Y-m-d H:i'); ?> CET
    </p>
</div>

<?php include 'includes/footer.php'; ?>
