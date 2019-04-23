<? /** IexForm Router */
if (!defined('IEXFORM')) { define('IEXFORM', true); }
if (!defined('IEXFORM_DIR')) { define('IEXFORM_DIR', __DIR__); }

$configDir = isset($configDir) ? $configDir : IEXFORM_DIR . '/config/';

$allFiles = isset($allFiles) ? $allFiles : array_filter(scandir($configDir, SCANDIR_SORT_ASCENDING ), function($fileName){
    return preg_match('#^[A-Za-z0-9_\-]+\.php$#', $fileName) === 1;
});

$rawName = isset($_GET['config']) ? $_GET['config'] : '';

$configFile = '';
foreach ($allFiles as $safeName) {
    if ( $safeName !== $rawName.'.php' ) continue;
    $configFile = $safeName;
}

if ( $configFile ) {
    include $configDir.$configFile;
} else {
    http_response_code(400);
    $msg = 'iexForm: Config file wasn\'t specified!';
    if ( $rawName ) {
        $name = htmlspecialchars(trim($rawName), ENT_QUOTES, 'UTF-8').'.php';
        $msg = 'iexForm: Config file not found: "'.$configDir.$name.'"';
    }
    die($msg);
}

require_once IEXFORM_DIR . '/class/IexForm.class.php';
$iexForm = new IexForm($params, $configFile);
unset($iexForm);