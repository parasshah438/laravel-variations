@echo off
echo Starting fresh Laravel migration...
cd /d "c:\wamp64\www\test\12\app-variations"

echo Dropping all tables and recreating...
php artisan migrate:fresh

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Migration completed successfully!
    echo.
    echo Checking migration status...
    php artisan migrate:status
    echo.
    echo Testing AI Recommendation tables...
    php -r "
    require 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo 'UserBehavior model: ' . (class_exists('App\Models\UserBehavior') ? '✅ Loaded' : '❌ Missing') . PHP_EOL;
    echo 'ProductRecommendation model: ' . (class_exists('App\Models\ProductRecommendation') ? '✅ Loaded' : '❌ Missing') . PHP_EOL;
    echo 'AIRecommendationService: ' . (class_exists('App\Services\AIRecommendationService') ? '✅ Loaded' : '❌ Missing') . PHP_EOL;
    
    try {
        $behaviorCount = App\Models\UserBehavior::count();
        echo 'UserBehavior table: ✅ Connected (0 records)' . PHP_EOL;
    } catch (Exception $e) {
        echo 'UserBehavior table: ❌ Error - ' . $e->getMessage() . PHP_EOL;
    }
    
    try {
        $recommendationCount = App\Models\ProductRecommendation::count();
        echo 'ProductRecommendation table: ✅ Connected (0 records)' . PHP_EOL;
    } catch (Exception $e) {
        echo 'ProductRecommendation table: ❌ Error - ' . $e->getMessage() . PHP_EOL;
    }
    "
) else (
    echo.
    echo ❌ Migration failed with error code %ERRORLEVEL%
)

echo.
pause
