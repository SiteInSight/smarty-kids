<?php
class IexForm {

    public $uid = '';           // UID формы для параллельного хранения данных нескольких форм и др. задач. Требование к многошаговым формам: к каждому data-pform-id должно быть привязано НЕ БОЛЕЕ ОДНОЙ формы на странице (!!!)
    public $ts = '';
    public $session = [];       // Cессию используем только для многошаговости
    public $phpsesid = '';
    public $newSession = false;

    public $pformid = '';       // Значение атрибута data-pform-id враппера/кнопки формы, используемоев JS-инициализации в объекте formsParams.
    public $inline = false;     // инлайн-форма, не всплывает и не закрывается, но подгружается в созданный вручную контейнер <div class="js-pform-wrap" data-pform-id="callback"></div>
    public $submited = false;   // false - запрос на получение HMTL-кода формы без POST-данных полей, true - запрос с POST-данными полей формы (выполнена отправка формы)
    public $srvload = false;    // если true - предполагается что первая загрузка формы выполняется на серверной стороне с помощью include/require (см. пример в начале index.php)

    public $multistep = false;
    public $step = 1;
    public $stepsCnt = 0;
    public $fieldsStep = [];

    public $params;
    /**
     * Массив объектов полей
     * @var FieldEntity[] $fields
     */
    public $fields = [];
    public $fieldsFromTemplate;
    private $parseInputTypes = ['input', 'textarea', 'select'];

    /**
     * @var array Массив объектов расширений
     */
    public $exts = [];

    public $state = [
        'isValidated' => false,     // валидация данных формы была выполнена
        'isValid' => false,         // данных формы прошли валидацию
        'errCode' => self::ERR_FIELD_NONE // код общей ошибки формы (индивидуальные ошибки валидации каждого поля сохраняются в $this->fields)
    ];

    /**
     * Допустимые PHP-коллбэки ядра и расширений.
     * Перечислены в порядке выполнения.
     *
     * @var array
     */
    public $callbacks = [
        'beforeParseTemplate' => true,  // поля еще не созданы и не инициализированы (массив $this->fields пустой)
        'beforeShow' => true,           // перед выводом HTML-кода формы
        'beforeValidate' => true,       // шаблон распарсен, поля созданы и инициализированы (в т.ч. расширениями)
        'afterValidate' => true,        // после каждого выполнения валидации, в т.ч. неуспешной
        'onSuccess' => true,            // после успешной валидации и последнего шага для многошаговых форм
        'onFinish' => true,             // конец выполнения скрипта
    ];

    public $arJson = [
        'multistep' => false,
        'status' => 'ok',
        'common' => '',
        'fields' => [],
        'newSession' => false,
        'phpsesid' => ''
    ];

    const ERR_FIELD_NONE = 0;
    const ERR_FIELD_EMPTY = 1;
    const ERR_FIELD_URL = 2;
    const ERR_FIELD_RUS = 3;
    const ERR_FIELD_EMAIL = 4;
    const ERR_FIELD_FILE_SIZE = 5;
    const ERR_FIELD_FILE_TYPE = 6;
    const ERR_FIELD_FILE_SAVE = 7;
    const ERR_FIELD_DATE = 8;
    const ERR_FIELD_TYPE = 9;
    const ERR_FIELD_POLICY = 12;
    const ERR_FIELD_VALIDATION_TYPE = 29;
    const ERR_COMMON_SPAM = 10;
    const ERR_COMMON_AUTH = 11;
    const ERR_COMMON_SESSION = 13;
    const ERR_SERVICE = 20;

    public $errCommon = [
        self::ERR_COMMON_SPAM,
        self::ERR_COMMON_AUTH,
        self::ERR_COMMON_SESSION,
    ];

    public $errCodes = [
        self::ERR_FIELD_NONE => '',

        // ошибки отдельных полей
        self::ERR_FIELD_EMPTY => 'поле не заполнено',
        self::ERR_FIELD_URL => 'поле содержит ссылку',
        self::ERR_FIELD_RUS => 'отсутствуют русские буквы',
        self::ERR_FIELD_EMAIL => 'неправильный e-mail',
        self::ERR_FIELD_FILE_SIZE => 'файл слишком большой',
        self::ERR_FIELD_FILE_TYPE => 'данный тип файла не разрещен, для обхода ограничения положите файл в архив (zip, rar, 7z)',
        self::ERR_FIELD_FILE_SAVE => 'ошибка сохранения файла',
        self::ERR_FIELD_DATE => 'неправильная дата',
        self::ERR_FIELD_TYPE => 'неправильный тип поля',
        self::ERR_FIELD_POLICY => 'необходимо согласие',
        self::ERR_FIELD_VALIDATION_TYPE => 'неизвестный тип валидации',

        // общие ошибки (не важно в каком поле они произошли)
        self::ERR_COMMON_SPAM => 'ошибка безопасности', // СПАМ
        self::ERR_COMMON_AUTH => 'Неверный логин или пароль!',
        self::ERR_COMMON_SESSION => 'ваша сессия истекла, перед отправкой формы еще раз проверьте правильность введенных данных',

        // для вывода-просмотра служебных данных вместо ошибок
        self::ERR_SERVICE => 'обслуживание'
        /* например
        $this->errCodes[20] = 'Любое нужное сообщение';
        $error = 20;
        */

        // пользовательские сообщения добавляем (метод $this->initUserValidator) начиная с кода $this->errCodeLast
    ];

    /**
     * @var int Код последней добавленной кастомной ошибки
     */
    public $errCodeLast = 100;

    public $validationTypes = [
        'required' => 'required', // обязательное поле
        'html' => 'html',         // поле может содержать html-теги (по-умолчанию все теги очищаются функцией strip_tags())
        'hasRUS' => 'hasRUS',     // поле должно содержать хотябы один кирилический символ
        'denyURL' => 'denyURL',   // поле не должно содержать урл (ищется наличие строки 'http:' или 'https:' или 'mailto:' или 'ftp:')
        'isEmail' => 'isEmail',   // поле должно содержать валидный email
        'isDate' => 'isDate'      // дата в формате ДД.ММ.ГГГГ
    ];

    public $validators = [];  // пока только для текстовых полей

    public $documentRoot; // для хранения $_SERVER['DOCUMENT_ROOT'] без закрывающего слэша

    public $templatesDir = '';
    public $templatesSubDir = '/';
    public $templatePath = '';
    public $templateHTML = '';
    public $templateGetParams = [];
    public $templatesDynamic = [];

    public $filesUploadPath = '/iexform-upload/';
    public $filesMkdirMode = 0755;
    //public $filesMkfileMode = 0644;
    public $filesMaxSize; // по-умолчанию берется из php.ini (параметр post_max_size), k - килобайты, m - мегабайты, g - гигабайты, регистр любой
    /*
    public $filesAllowedTypes = array(
        // картинки (gif - не включен из соображений безопасности)
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        // документы
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc' => 'application/msword',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'pdf' => 'application/pdf',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'odf' => 'application/vnd.oasis.opendocument.spreadsheet',
        'rtf' => 'text/rtf',
        // архивы
        'zip' => 'application/zip',
        'rar' => 'application/x-rar',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        'gz' => 'application/x-gzip',
        'tgz' => 'application/x-gzip'
    );
    */
    public $filesAllowedTypes = array(
        // картинки (gif - не включен из соображений безопасности)
        'jpg', 'jpeg', 'png',
        // документы, презентации, таблицы
        'docx', 'doc', 'pdf', 'pptx', 'ppt', 'pps', 'ppsx', 'odt', 'odf', 'odp', 'rtf', 'xlsx', 'xls',
        // архивы
        'zip', 'rar', '7z', 'tar', 'gz', 'tgz'
    );

    public $isSpam = false; // если true, письмо не отправляем, а флаг добавляем в JSON для обработки в JS

    public $blackList = [];

    public $configName = '';

    public $fieldsCaching = true;

    /**
     * Поле для метода getFieldPriority()
     *
     * Рекомендации по диаппазонам значений:
     * - системные поля:        0..999
     * - пользовательские поля: 1000...бесконечность
     *
     * @var array
     */
    public $newFieldPriority = [
        'sys' => 1,
        'user' => 1000,
    ];

    /**
     * Метод для автоладера spl_autoload_register()
     * @param $file string
     * @return bool
     */
    static function includeFile($file){
        if ( file_exists($file) !== false ) {
            //echo 'Loaded: ' . $file . "<br>\n";
            require_once $file;
            return true;
        }
        return false;
    }

    public function __construct($params, $configName = null) {
        $this->params = $params;
        $this->configName = str_replace('.php', '', $configName);

        $this->pformid = isset($_POST['pformid']) ? $_POST['pformid'] : '';
        $this->submited = isset($_POST['submited']) && $_POST['submited'] === 'yes';
        if ( $this->submited ) {
            $this->uid = isset($_POST['iexuid']) ? $_POST['iexuid'] : '';
            $this->ts = isset($_POST['iexts']) ? $_POST['iexts'] : '';
            $this->isSpam = !( $this->uid && $this->ts && $this->validateUid() );
        } else {
            $this->ts = str_replace('.', '', microtime(true));
            $this->uid = $this->generateUid();
        }

        $this->multistep = isset($this->params['multistep']) && $this->params['multistep'] === true;
        $this->step = isset($_POST['iexstep']) ? (int)$_POST['iexstep'] : 1;
        $this->step = $this->step < 1 ? 1 : $this->step;

        $this->initSession();

        $this->srvload = isset($_POST['srvload']) && $_POST['srvload'] === 'true';
        $this->inline = isset($_POST['inline']) && $_POST['inline'] === 'true';

        // кодировка html-кода формы (письма всегда отсылаем в UTF-8)
        if (!isset($this->params['formEncoding'])) {
            $this->params['formEncoding'] = 'UTF-8';
        }

        if (isset($this->params['blackList'])) {
            $this->blackList = $this->params['blackList'];
        }

        // файлы
        if (isset($this->params['filesUploadPath']) && !empty($this->params['filesUploadPath'])) {
            $this->filesUploadPath = $this->params['filesUploadPath'];
        }
        if (isset($this->params['filesMkdirMode']) && !empty($this->params['filesMkdirMode'])) {
            $this->filesMkdirMode = $this->params['filesMkdirMode'];
        }
        $this->filesMaxSize = $this->sizeInBytes(ini_get('post_max_size'));
        if (isset($this->params['filesMaxSize']) && !empty($this->params['filesMaxSize'])) {
            $this->filesMaxSize = min($this->filesMaxSize, $this->sizeInBytes($this->params['filesMaxSize']));
        }
        if (isset($this->params['filesAllowedTypes']) && !empty($this->params['filesAllowedTypes'])) {
            $this->filesAllowedTypes = $this->params['filesAllowedTypes'];
        }

        if (!isset($this->params['exts'])) {
            $this->params['exts'] = [];
        }

        $this->documentRoot = preg_replace('#^(.*)/$#', '$1', $_SERVER['DOCUMENT_ROOT']); // убираем закрывающий слэш, если он присутсвует (бывали случаи) в $_SERVER['DOCUMENT_ROOT']

        if (isset($this->params['fieldsCaching'])) {
            $this->fieldsCaching = (bool)$this->params['fieldsCaching'];
        }

        $this->initExts();

        if ( !$this->isSpam ) {
            $this->initValidators();
            $this->initTemplates();
            $this->initFields();
        }

        $this->show();
    }

    public function pr($var, $comment = false, $vardump = false, $styles = false) {
        echo '<pre class="iexdump"' . ($styles ? ' style="' . $styles . '"' : '') . '>';
        if ($comment) {
            echo $comment, '<br/>';
        }
        if ($vardump || !isset($var) || empty($var)) {
            var_dump($var);
        } else {
            print_r($var);
        }
        echo '</pre>';
    }

    public function generateUid(){
        $uid = '';
        if ( isset($_SERVER['HTTP_REFERER']) ) {
            $uid = md5($this->pformid . $_SERVER['HTTP_REFERER'] . $_SERVER['REQUEST_URI'] . $this->ts);
        }
        return $uid;
    }

    public function validateUid(){
        return $this->uid === $this->generateUid();
    }

    private function initSession(){
        $curSessionId = session_id();
        if ( !$curSessionId ) {
            session_start();
        }
        $curSessionId = session_id();
        $this->phpsesid = isset($_POST['iexphpsesid']) ? $_POST['iexphpsesid'] : $curSessionId;
        if ( $this->multistep && !$this->isSpam ) {
            if ( $this->phpsesid !== $curSessionId ) {
                $this->newSession = true;
                $this->phpsesid = $curSessionId;
            }
            if ( !isset($_SESSION['iexform']) ) {
                $_SESSION['iexform'] = [];
            }
            $this->session = &$_SESSION['iexform'];
            if ( !isset($this->session[$this->uid]) ) {
                $this->session[$this->uid] = [];
            }
            $this->session = &$this->session[$this->uid];
        }
        $this->arJson['phpsesid'] = $this->phpsesid;
    }

    /**
     * Возвращает priority для выбранного типа поля
     * и следит за соблюдением рекомендаций в $this->newFieldPriority
     *
     * @param string $type 'sys' | 'ext' | 'user' (по-умолчанию 'user')
     * @return mixed
     */
    public function getFieldPriority($type = 'user'){
        $priority = $this->newFieldPriority[$type];
        $this->newFieldPriority[$type] += $type === 'user' ? 10 : 5;
        return $priority;
    }

    /**
     * Инициализация расширений и их сортировка в порядке приоритетов (заданы в классе каждого расширения)
     */
    private function initExts() {
        $exts = [];
        foreach ($this->params['exts'] as $extName => $extParams) {
            if ( class_exists($extName) ) { // require делает автозагрузчик (spl_autoload_register() в начале скрипта)
                $exts[$extName] = new $extName($this, $extParams);
            } else {
                echo 'iexForm: Не найдено расширение "' . $extName . "\"<br>\n";
            }
        }

        // Сортировка рисширений по приоритету (по возрастанию)
        usort($exts, function (ExtensionBase $a, ExtensionBase $b) {
            return
                ( $a->priority === $b->priority ) ? 0 : ( ($a->priority > $b->priority) ? 1 : -1 );
        });

        foreach ($exts as $ext) {
            $this->exts[ get_class($ext) ] = $ext;
        }
    }

    /**
     * При включенном расширении Log добавляет в журнал текст или переменную.
     * Массивы выводятся в лог с помощью print_r()
     *
     * @param mixed $content Переменная или текст для добавления лог
     */
    public function log($content){
        if ( isset($this->exts['Log']) ) {
            $this->exts['Log']->add($content);
        }
    }

    /**
     * Выполняет коллбэки с именем $cbName для расширений и ядра.
     * Результат выполнения коллбэка сравниваем с false, чтобы сделать необязательным добавление в коллбэк return.
     * Колбэки расширений выполняются в порядке их приоритета.
     *
     * @param string $cbName Название коллбэка
     * @param array $params Индексированный массив параметров
     * @return bool
     */
    private function runCallback($cbName, $params = []) {
        if ( !isset($this->callbacks[$cbName]) ) return true;

        $this->log('Запущен '.$cbName.'()');

        // 1) для расширений

        foreach ( $this->exts as $extInstance ){
            if ( !method_exists($extInstance, $cbName) ) continue;
            try {
                $success = $extInstance->{$cbName}(...$params) !== false;
                $this->log( '+ ' . get_class($extInstance) . '::' . $cbName );
            } catch ( Exception $e ) {
                echo 'iexForm: Ошибка выполнения коллбэка ' . $cbName . ' в расширении ' . get_class($extInstance) . ': ' . $e->getMessage() . "<br>\n";
                $success = false;
            }
            if ( !$success ) return false;
        }

        // 2) для конфиг-файла

        if (
            isset($this->params[$cbName]) &&
            is_callable($this->params[$cbName])
        ) {
            try {
                $success = $this->params[$cbName]($this, ...$params) !== false;
                $this->log( '+ ' . $cbName );
            } catch ( Exception $e ) {
                echo 'iexForm: Ошибка выполнения коллбэка ' . $cbName . ' в конфиг-файле: ' . $e->getMessage() . "<br>\n";
                $success = false;
            }
            return $success;
        }
        
        return true;
    }

    // Каждый валидатор должен возвращать 0 или код ошибки из $this->errCodes
    private function initValidators() {  // пока только для текстовых полей

        // 'required' и 'html' реализованы непосредственно в $this->validateFields()

        $this->validators['hasRUS'] = function ($value) { // https://itworkarounds.blogspot.ru/2011/08/validating-cyrillic-utf8-alphanumeric.html
            $regexp = '/[\p{Cyrillic}]/u';
            return (preg_match($regexp, $value) === 1) ? self::ERR_FIELD_NONE : self::ERR_FIELD_RUS;
        };

        $this->validators['denyURL'] = function ($value) {
            $regexp = '/(http:)|(https:)|(mailto:)|(ftp:)/i';
            return (preg_match($regexp, $value) !== 1) ? self::ERR_FIELD_NONE : self::ERR_FIELD_URL;
        };

        $this->validators['isEmail'] = function ($value) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) ? self::ERR_FIELD_NONE : self::ERR_FIELD_EMAIL;
        };

        $this->validators['isDate'] = function ($value, $format = 'd.m.Y') { // взято отсюда http://php.net/manual/ru/function.checkdate.php#113205
            $d = DateTime::createFromFormat($format, $value);
            return ($d && $d->format($format) == $value) ? self::ERR_FIELD_NONE : self::ERR_FIELD_DATE;
        };

        /**
         * от Павлюкова Ильи
         * Функция для проверки значения поля, пришедшего от клиента,
         * на корректность с помощью маски
         *
         * @param string $mask Маска в виде строки из шаблона (Пример: "+7 (599) 999-9999{1,10}")
         * @param string $value Значение поля, полученное от клиента (Пример: "+7 (916) 565-7424")
         *
         * @var array $convert Массив пар строк для подготовки маски к regexp
         * @var string $preg_mask Готовая для preg_match маска
         *
         * @since iexForm v8.2.3
         *
         * @return boolean Если значение параметра $value удовлетворяет маску - вернет истину, иначе ложь
         */
        /*
        function validateByMask($mask, $value) {
            $convert = array(
                '9' => '\d',
                '5' => '[0-68-9]',
                '(' => '\(',
                ')' => '\)',
                '-' => '\-',
                ' ' => '\s',
                '+' => '\+',
            );

            $preg_mask = '/^' . strtr($mask, $convert) . '$/';
            if (preg_match($preg_mask, $value) == 1)
                return true;
            else
                return false;
        }
        */

        $this->initUserValidator();
    }

    private function initUserValidator() {
        if (
            !isset($this->params['validators']) ||
            !is_array($this->params['validators'])
        ) return;

        foreach ( $this->params['validators'] as $ruleName => $ruleParams ) {
            if ( !(
                isset($ruleParams['err'], $ruleParams['func']) &&
                is_callable($ruleParams['func'])
            )) continue;

            $errCode = $this->addCustomError( $ruleParams['err'] );
            $this->validationTypes[$ruleName] = $ruleName;

            $this->validators[$ruleName] = function ($value) use ($ruleParams, $errCode) {
                return $ruleParams['func']($value) ? self::ERR_FIELD_NONE : $errCode;
            };
        }
    }

    private function inBlackList($name, $value) {
        return (
            isset($this->blackList[$name]) &&
            is_array($this->blackList[$name]) &&
            in_array($value, $this->blackList[$name], true)
        );
    }

    private function initTemplates() {

        if ( isset($this->params['templatesSubDir']) && $this->params['templatesSubDir'] ) {
            $this->templatesSubDir = '/'.preg_replace('#(^[\./]+)|(/$)#', '', $this->params['templatesSubDir']).'/';
        }
        if (isset($this->params['templatesDir']) && !empty($this->params['templatesDir'])) {
            $this->templatesDir = preg_replace('#/$#', '', $this->params['templatesDir']);
        } else {
            $this->templatesDir = IEXFORM_DIR.'/template';
        }
        $this->templatesDir .= $this->templatesSubDir;

        if ( isset($this->params['templatesDynamic']) && is_array($this->params['templatesDynamic']) ) {
            $this->templatesDynamic = $this->params['templatesDynamic'];
        }

        if ( !file_exists($this->templatesDir) ) {
            die('iexForm: Не найдена папка шаблонов форм "' . $this->templatesDir . '"');
        }

        $arFoundTemplates = glob($this->templatesDir . '*.php');
        foreach ($arFoundTemplates as $fileName) {
            $getParamName = preg_replace('#^(' . $this->templatesDir . ')(.*)(\.php)$#', '$2', $fileName);
            if ($getParamName) {
                $this->templateGetParams[$getParamName] = $fileName;
            }
        }
        if (!count($this->templateGetParams)) {
            die('iexForm: Не найдено ни одного шаблона формы (файла вида "*.php") в папке "' . $this->templatesDir . '"');
        }

        $templateCurParam = isset($_GET['template']) ? $_GET['template'] : '';
        if (preg_match('/^[A-Za-z0-9_\-]{1,}$/', trim($templateCurParam)) === 1) {
            if (isset($this->templateGetParams[$templateCurParam])) {
                $this->templatePath = $this->templateGetParams[$templateCurParam];
            } else {
                die('iexForm: Не найден шаблон формы "' . $this->templatesDir . $templateCurParam . '.php"');
            }
        } elseif (isset($this->templateGetParams['universal'])) { // eсли get-параметр с шаблоном не передан, то пытаемся подключить шаблон universal.php
            $this->templatePath = $this->templateGetParams['universal'];
        } else {
            die('iexForm: Для формы не задан кастомный шаблон и не найден универсальный шаблон "' . $this->templatesDir . 'universal.php"');
        }
    }

    private function getDomFieldName($inputNode) {
        $name = trim($inputNode->getAttribute('name'));
        if ($name && strpos($name, '[')) {
            $name = preg_replace('/^(.+?)(\[.+)$/', '$1', $name);
        }
        return $name;
    }

    private function parseDomFieldParams($inputNode) {
        $name = $this->getDomFieldName($inputNode);
        $type = ($inputNode->tagName === 'input') ? trim($inputNode->getAttribute('type')) : $inputNode->tagName;
        if (
            !$name ||
            !in_array($type, ['hidden', 'text', 'textarea', 'password', 'file', 'radio', 'checkbox', 'select'], true)
        )
            return;

        $field = &$this->fields[$name];

        /* Документация по изменению аттрибутов http://php.net/manual/en/domelement.setattribute.php */
        $value = ($inputNode->tagName === 'input') ? $inputNode->getAttribute('value') : $inputNode->nodeValue;
        $header = trim($inputNode->getAttribute('data-pform-header'));
        $notsend = trim($inputNode->getAttribute('data-pform-notsend'));
        $mask = trim($inputNode->getAttribute('data-pform-mask'));

        $validation = trim($inputNode->getAttribute('data-pform-validation'));
        if (strpos($validation, ',')) {
            $validation = explode(',', $validation);
            foreach ($validation as &$item) {
                $item = trim($item);
            }
        } else {
            $validation = [$validation];
        }
        $validation = array_intersect($validation, $this->validationTypes);

        if ( !in_array($type, ['radio', 'checkbox', 'select'], true) ) {
            $field = new FieldEntity($name, $value, $type);
            $field->header = $header;
            $field->validation = $validation;
            $field->mask = $mask;
            $field->notsend = !empty($notsend);
        } else { // учитываем что поля типа checkbox и radio в HTML-коде шаблона могут быть расположены в разных местах (не одно за другим)
            if($field === null){
                $field = new FieldEntity($name, '', $type);
            }
            if ($header) {
                $field->header = $header;
            }
            if ($validation) {
                $field->validation = $validation;
            }
            if ($notsend) {
                $field->notsend = $notsend;
            }
            if ($type === 'select') {
                foreach ($inputNode->childNodes as $option) {
                    if (!($option instanceof DOMElement)) continue; //  для избежания ошибок в более новых версиях DomDocument
                    if ($option->hasAttribute('disabled')) continue;
                    $value = $option->getAttribute('value');
                    if (empty($value)) $value = $option->nodeValue;
                    if ($value) {
                        $field['options'][] = $value;
                    }
                }
            } else {
                if ($value) {
                    $field['options'][] = $value;
                }
            }
        }
        $field->priority = $this->getFieldPriority();

        if ($field->sys === false) {
            $field['step'] = isset($this->fieldsStep[$name]) ? $this->fieldsStep[$name] : 1;
            $this->stepsCnt = max($this->stepsCnt, $field['step']);
        }

        unset($field);
    }

    private function getAllFieldsXpath() {
        return '//' . implode(' | //', $this->parseInputTypes);
    }

    private function getStepFieldsXpath($step) {
        return '//*[@data-pform-step=' . $step . ']/descendant::' . implode(' | //*[@data-pform-step=' . $step . ']/descendant::', $this->parseInputTypes);
    }

    private function getFieldsStep($xpathObject) {
        $stepsNodeList = $xpathObject->query("//*[@data-pform-step]");
        foreach ($stepsNodeList as $stepNode) {
            $step = (int)trim($stepNode->getAttribute('data-pform-step'));
            $step = $step ? $step : 1;
            $inputNodeList = $xpathObject->query($this->getStepFieldsXpath($step));
            foreach ($inputNodeList as $inputNode) {
                $name = $this->getDomFieldName($inputNode);
                if (!$name) continue;                     // поля с пустым атрибутом name игнорируем
                if (!isset($this->fieldsStep[$name])) { // при наличии в шаблоне нескольких полей с одинаковым name шаг будет браться из первого по порядку
                    $this->fieldsStep[$name] = $step;
                }
            }
        }
    }

    private function parseTemplate() {

        $this->runCallback('beforeParseTemplate');

        //$curErrRep = ini_get('error_reporting');
        //ini_set('error_reporting', E_ALL); // для вывода ошибок парсинга в случае невалидного HTML-кода шаблона

        ob_start();

        $arData = [
            'pformid' => $this->pformid,
            'uid' => substr($this->uid, 0, 4),
            'inline' => $this->inline
        ];
        $arData = array_merge($arData, $this->templatesDynamic);

        include $this->templatePath;

        $this->templateHTML = ob_get_clean();

        $dom = new DomDocument();
        $dom->loadHTML($this->templateHTML . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'); // без <meta> кодировка определяется неверно (кирилица отображается кракозябрами)
        $xPath = new DomXPath($dom);

        if ($this->multistep) {
            $this->getFieldsStep($xPath);
        }

        $inputNodeList = $xPath->query($this->getAllFieldsXpath());
        foreach ($inputNodeList as $inputNode) {
            $this->parseDomFieldParams($inputNode);
        }

        foreach ($this->fields as &$field) {
            if (in_array($field['type'], ['radio', 'checkbox', 'select']))
                $field['options'] = array_unique($field['options']);
        }
        unset($field);
    }

    private function sessionSaveFields() {
        foreach ( $this->fields as $name => $field ) {
            if (
                isset($field['step']) &&
                $field['step'] === $this->step
            ) { // сохраняем поля только текущего шага
                $this->session[$name] = $field['value'];
            }
        }
    }

    private function sessionRestoreFields() {
        foreach ( $this->fields as $name => $field ) {
            if (
                isset($field['step']) &&
                $field['step'] < $this->step && // достаем поля только с предыдущих шагов
                isset($this->session[$name])
            ) {
                $this->fields[$name]['value'] = $this->session[$name];
            }
        }
    }

    private function sessionClean() {
        $this->session = &$_SESSION['iexform'];
        unset($this->session[$this->uid]);
    }

    /**
     * Добавление системного поля, которое:
     *  - в HTML-код добавляется как *type="hidden"* (за исключением поля iexbait)
     *  - не проходит валидацию (за исключением поля iexbait)
     *
     * @param string $name Атрибут name поля
     * @param string $value Атрибут value поля
     * @param bool $notsend Значение поля объекта FieldEntity, если true, то некоторые расширения будут это учитывать
     * @return bool
     */
    public function addSysField($name, $value = '', $notsend = true) {
        $this->fields[$name] = new FieldEntity($name, $value);
        $this->fields[$name]->notsend = $notsend;
        $this->fields[$name]->sys = true;
        $this->fields[$name]->priority = $this->getFieldPriority('sys');
        return true;
    }

    private function initFields() {
        $this->addSysField('iexuid', $this->uid);
        $this->addSysField('iexts', $this->ts);
        $this->addSysField('iexphpsesid', $this->phpsesid);
        $this->addSysField('iexbait', ''); // антиспам (поле-приманка для СПАМ-ботов, должно всегда оставаться пустым, если заполнено - это СПАМ-бот)
        $this->addSysField('iexfieldscaching', ($this->fieldsCaching ? 'true' : 'false'));
        $this->addSysField('iexdevelopermode', 'false'); // Расширение для хрома
        if ($this->multistep) {
            $this->addSysField('iexstep', 1);
        }

        $this->parseTemplate();

        if ($this->multistep && ($this->step > 1) && !$this->isSpam) {
            $this->sessionRestoreFields();
        }
    }

    public function convertEncoding($src, $to_encoding) {
        if (is_string($src)) {
            return mb_convert_encoding($src, $to_encoding, 'UTF-8');
        }
        if (is_array($src)) {
            foreach ($src as $key => $item)
                $src[$key] = $this->convertEncoding($item, $to_encoding);
            return $src;
        }
        return $src;
    }

    public function sizeInBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $newval = (int)$val;
        switch ($last) {
            case 'g':
                $newval *= 1024;
            case 'm':
                $newval *= 1024;
            case 'k':
                $newval *= 1024;
        }

        return $newval;
    }

    private function getFormHtml() { // динамические данные передаются в шаблон в $this->parseTemplate()

        $sysFieldsHtml = '';
        foreach ($this->fields as $name => $field) {
            if (!$field->sys) { continue; }
            $type = $name === 'iexbait' ? 'text' : $field['type'];
            $html = '<input type="' . $type . '" name="' . $name . '" value="' . $field['value'] . '" data-pform-cache="false"/>';
            $sysFieldsHtml .= $name === 'iexbait' ? '<div style="display:none">' . $html . '</div>' : $html;
        }

        $maskedFields = [];
        foreach ($this->fields as $fname => $field) {
            if (isset($field['mask']) && $field['mask']) {
                $maskedFields[$fname] = $field['mask'];
            }
        }

        $jsHtml = '<script id="script-'.$this->pformid.'" data-pform-uid="'.$this->uid.'">'."\n";
        if ($maskedFields) {
            $jsHtml .= "    window.iexFormsMasks = window.iexFormsMasks || {};\n";
            $jsHtml .= "    window.iexFormsMasks['" . $this->pformid . "'] = " . json_encode($maskedFields) . ";\n";
        }
        if ($this->exts) {
            $jsHtml .= "    window.iexFormsExts = window.iexFormsExts || {};\n";
            $jsHtml .= "    window.iexFormsExts['" . $this->pformid . "'] = {}\n";
            foreach ($this->exts as $extInstance){
                $extName = get_class($extInstance);
                $js = '';
                $file = IEXFORM_DIR . '/ext/' . $extName . '/client.js';
                if (file_exists($file)) {
                    $js = file_get_contents($file);
                }
                if ($js) {
                    $jsHtml .= "    window.iexFormsExts['" . $this->pformid . "']['" . $extName . "'] = " . $js . "\n";
                }
            }
        }
        $jsHtml .= "</script>\n";

        ob_start();

        echo '<form method="post" accept-charset="UTF-8" enctype="multipart/form-data">' . "\n";
        echo $sysFieldsHtml . "\n";
        echo $this->templateHTML . "\n";
        echo '</form>' . "\n";
        echo $jsHtml;

        return ob_get_clean();
    }


    /**
     * Добавление кастомной ошибки
     *
     * @param $errorText string Текст добавляемой кастомной ошибки
     * @return mixed Код добавленной ошибки или false
     */
    private function addCustomError ($errorText) {
        if ( !$errorText ) return false;
        if ( isset( $this->errCodes[$this->errCodeLast] ) ) {
            $this->errCodeLast++;
        }
        $this->errCodes[$this->errCodeLast] = $errorText;
        return $this->errCodeLast;
    }

    /**
     * Для установки кастомной ошибки полю из PHP-коллбэка afterValidate
     *
     * @param $errorText string Текст добавляемой кастомной ошибки
     * @param $fieldName string Атрибут name поля для установки ошибки
     * @return bool
     */
    public function setCustomFieldError ($errorText, $fieldName){
        if (
            isset($this->fields[$fieldName]) &&
            $this->addCustomError($errorText)
        ) {
            $this->errCodes[ $this->errCodeLast ] = $errorText;
            $this->fields[$fieldName]['error'] = $this->errCodeLast;
            $this->state['isValid'] = false;
            return true;
        }
        return false;
    }

    /**
     * Для установки кастомной общей ошибки из PHP-коллбэка afterValidate
     *
     * @param $errorText string Текст добавляемой кастомной ошибки
     * @param $field FieldEntity Поле для установки ошибки
     * @return bool
     */
    public function setCustomCommonError ($errorText){
        if ( $this->addCustomError($errorText) ) {
            $this->errCommon[] = $this->errCodeLast;
            $this->state['errCode'] = $this->errCodeLast;
            $this->state['isValid'] = false;
            return true;
        }
        return false;
    }

    private function validateField($field) { // пока только для текстовых полей
        $error = self::ERR_FIELD_NONE;

        foreach ( $field['validation'] as $rule ) {
            if ($rule === 'required' || $rule === 'html') continue;
            $error = isset($this->validators[$rule]) ? self::ERR_FIELD_NONE : self::ERR_FIELD_VALIDATION_TYPE;
            if ($error) break;
            $error = $this->validators[$rule]($field['value']);
            if ($error) break;
        }

        return $error;
    }

    // Прошедшие валидацию данные сохраняются
    // в $this->fields и в $this->session (если форма многошаговая)
    private function validateFields() {
        $errFound = false;

        foreach ($this->fields as $i => &$field) {
            $error = self::ERR_FIELD_NONE;
            $required = $field->isRequired() ? self::ERR_FIELD_EMPTY : self::ERR_FIELD_NONE;

            if ($field->sys === true && $i !== 'iexbait')
                continue;

            if ($i === 'iexbait') {

                if (!isset($_POST[$i]) || strlen($_POST[$i]) > 0) {
                    $this->isSpam = true;
                }

            } elseif (
                $this->multistep &&
                (!isset($field['step']) || $field['step'] != $this->step)
            ) {

                continue;

                // hidden, text, textarea, password
            } elseif (in_array($field['type'], ['hidden', 'text', 'textarea', 'password'], true)) {

                if (isset($_POST[$i])) {
                    if (mb_strlen(trim($_POST[$i])) > 0) {
                        if (in_array('html', $field['validation'], true)) {
                            $field['value'] = trim($_POST[$i]);

                        } else {
                            $field['value'] = trim(strip_tags($_POST[$i]));
                        }
                        $error = $this->validateField($field);
                        $this->isSpam = $this->inBlackList($i, $field['value']);
                    } else {
                        $field['value'] = ''; // чтобы можно было перезаписать (стереть) value, забитое в шаблоне формы
                        $error = $required;
                    }
                } else {
                    $error = $required;
                }

                // select, checkbox
            } elseif (in_array($field['type'], ['select', 'checkbox'], true)) {

                if (isset($_POST[$i]) && is_array($_POST[$i]) && count($_POST[$i])) {
                    $validOptions = array_intersect($_POST[$i], $field['options']);
                    if (count($validOptions)) {
                        $field['value'] = implode(', ', $validOptions);
                    } else {
                        $error = $i === 'iexpolicy' ? self::ERR_FIELD_POLICY : $required;
                    }
                } else {
                    $error = $i === 'iexpolicy' ? self::ERR_FIELD_POLICY : $required;
                }

                // radio
            } elseif ($field['type'] === 'radio') {

                if (isset($_POST[$i]) && in_array($_POST[$i], $field['options'], true)) {
                    $field['value'] = $_POST[$i];
                } else {
                    $error = $required;
                }

                // file
            } elseif ($field['type'] === 'file') {

                if (!isset($_FILES[$i]['name']) || empty($_FILES[$i]['name'])) {
                    $error = $required;
                } else {
                    $file_name = $_FILES[$i]['name'];
                    $file_tmp_name = $_FILES[$i]['tmp_name'];

                    if ($_FILES[$i]['error'] || ((int)$_FILES[$i]['size'] > (int)$this->filesMaxSize)) {
                        $error = self::ERR_FIELD_FILE_SIZE;
                    } else {
                        $exploded = explode('.', $file_name);
                        $file_ext = strtolower(end($exploded));
                        /*
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $file_mime = $finfo->file($file_tmp_name);
                        */
                        $allowedTypes = $this->filesAllowedTypes;

                        if (count($exploded) < 2 || !in_array($file_ext, $allowedTypes) /*|| ($file_mime !== $this->filesAllowedTypes[$file_ext])*/) { // расширение и MIME-типом файла должны соответсвовать
                            $error = self::ERR_FIELD_FILE_TYPE;
                        } else {
                            //$this->errCodes[20] = $file_mime;
                            //$error = 20;

                            if (!file_exists($this->documentRoot . $this->filesUploadPath)) {
                                /** @noinspection MkdirRaceConditionInspection */
                                mkdir($this->documentRoot . $this->filesUploadPath, $this->filesMkdirMode, true);
                            }

                            $web_path = $this->filesUploadPath . md5($file_name . $_SERVER['REMOTE_ADDR'] . microtime(true)) . '.' . $file_ext;

                            if (!is_dir($this->documentRoot . $this->filesUploadPath) || !move_uploaded_file($file_tmp_name, $this->documentRoot . $web_path)) {
                                $error = self::ERR_FIELD_FILE_SAVE;
                            }

                            if (!$error) {
                                $field['value'] = $web_path;
                                $field['name'] = $file_name;
                            }
                        }
                    }
                }

                // неизвестный тип поля
            } else {
                $error = self::ERR_FIELD_TYPE;
            }

            // обработка ошибок
            if ($error) {
                if ($field['type'] === 'hidden') {
                    $this->isSpam = true; // ошибки в скрытом поле считаем спамом
                }
                if ( in_array($error, $this->errCommon, true) ) {
                    $this->state['errCode'] = $error;
                } else {
                    $this->fields[$i]['error'] = $error;
                }
                $errFound = true;
            }

            // если спам, остальные поля не обрабатываем, экономим ресурсы
            if ($this->isSpam)
                break;

        }
        unset($field);

        if ( $this->newSession ) {
            $this->state['errCode'] = self::ERR_COMMON_SESSION;
            $errFound = true;
        }

        $this->state['isValid'] = !$errFound;

        $this->runCallback('afterValidate', [$errFound]);

        return $this->isValid();
    }

    public function validate() {
        if ( !$this->isSpam ) {
            $this->validateFields();
        }

        if (
            $this->isValid() &&
            $this->multistep &&
            ($this->step < $this->stepsCnt)
        ) {
            $this->sessionSaveFields();
        }

        $this->state['isValidated'] = true;

        return $this->isValid();
    }

    public function isValidated() {
        return $this->state['isValidated'];
    }

    public function isValid() {
        return $this->state['isValid'];
    }

    private function show() {
        if (!$this->srvload) {
            header('Content-Type: text/html; charset=' . $this->params['formEncoding']);
        }

        if ($this->submited) {
            $this->showJson();
        } else {
            $this->showHtml();
        }

        $this->finish();
    }

    private function showHtml() {
        $success = $this->runCallback('beforeShow');

        if ($success) {
            $html = $this->getFormHtml();
            if ($this->params['formEncoding'] !== 'UTF-8') {
                $html = $this->convertEncoding($html, $this->params['formEncoding']);
            }
            echo $html;
        }
    }

    private function showJson() {

//        if ( $this->debugMode ) {
//            $this->arJson['spam'] = $this->isSpam;
//        }

        if ( !$this->isSpam ) {
            $success = $this->runCallback('beforeValidate');

            $this->arJson['multistep'] = $this->multistep;

            if ( $success ) {
                $this->validate();
            }

            if ( !$this->isValid() ) {
                $this->arJson['newSession'] = $this->newSession;
                $this->arJson['status'] = 'err';
                $this->arJson['common'] = $this->state['errCode'] ? $this->errCodes[$this->state['errCode']] : '';

                foreach ( $this->fields as $name => $field ) {
                    if ( isset($field['error']) && $field['error'] ) {
                        $this->arJson['fields'][$name] = $this->errCodes[$field['error']];
                    }
                }
            }

            if (
                ($this->isValid() && !$this->multistep) ||
                ($this->isValid() && $this->multistep && $this->step === $this->stepsCnt)
            ) {
                $this->sessionClean();
                $this->runCallback('onSuccess');
            }
        }

        header('Content-type: application/json; charset=utf-8');
        echo json_encode($this->arJson);
    }

    /**
     * Функция используется вместо __destructor(), т.к. последний при серверной
     * загрузке форм выполняется не тогда когда необходимо
     */
    public function finish() {
        $this->runCallback('onFinish');
    }
}

spl_autoload_register(function ($class) {
    if ( !IexForm::includeFile(IEXFORM_DIR .'/ext/' . $class . '/' . $class . '.class.php') ) {
        IexForm::includeFile(IEXFORM_DIR.'/class/' . $class . '.class.php');
    }
});