<?php
require_once 'vendor/autoload.php';
require_once 'config/config.php';

use Ecoride\Ecoride\Database\Seeder;

echo "🚗 === Générateur de données Ecoride === 🚗 \n";
echo "==========================================\n\n";

echo "⚠️  ATTENTION : Cette opération va :\n";
echo "   • Effacer TOUTES les données existantes\n";
echo "   • Recréer les schémas de base\n";
echo "   • Générer de nouvelles données de test\n\n";

echo "Voulez-vous continuer ? (yes/no): ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
if ($line != 'yes') {
    echo "❌ Opération annulée.\n";
    exit;
}
fclose($handle);

echo "\n";

try {
    $startTime = microtime(true);

    $seeder = new Seeder();
    $seeder->run();

    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);

    echo "\n🎉 Base de données peuplée avec succès ! \n";
    echo "⏱️  Temps d'exécution : {$executionTime}s\n";
    echo "📊 Vous pouvez maintenant tester votre application Ecoride !\n";

} catch (Exception $e) {
    echo "❌ Erreur lors de la génération : " . $e->getMessage() . "\n";
    echo "📁 Fichier : " . $e->getFile() . "\n";
    echo "📍 Ligne : " . $e->getLine() . "\n";

    if ($e->getPrevious()) {
        echo "🔍 Cause : " . $e->getPrevious()->getMessage() . "\n";
    }
}