<?php

/**
 * User SMS notification class
 */
class Reminder {

    /**
     * Contains all of available user logins with reminder tag
     *
     * @var array
     */
    protected $AllLogin = array();

    /**
     * Contains system alter config as key=>value
     *
     * @var array
     */
//    protected $AltCfg = array();

    /**
     * Contains all of available user phones data
     *
     * @var array
     */
    protected $AllPhones = array();

    /**
     * Placeholder for UbillingSMS object
     *
     * @var object
     */
    protected $sms = '';

    /**
     * Placeholder for FundsFlow object
     *
     * @var object
     */
    protected $money = '';

    /**
     * Contains data for native templating messages
     *
     * @var array
     */
    protected $AllTemplates = array();

    /**
     * Placeholder for REMINDER_ENABLED alter.ini option
     *
     * @var int
     */
    protected $rmdMode = 0;

    /**
     * Placeholder for REMINDER_TAGID alter.ini option
     *
     * @var int
     */
    protected $rmdTagID = 0;

    /**
     * Placeholder for REMINDER_DAYS_THRESHOLD alter.ini option
     *
     * @var int
     */
    protected $rmdDaysThreshold = 2;

    /**
     * Placeholder for REMINDER_PREFIX alter.ini option
     *
     * @var string
     */
    protected $rmdPhonePrefix = '';

    /**
     * Placeholder for REMINDER_TEMPLATE alter.ini option
     *
     * @var string
     */
    protected $rmdTemplate = '';

    /**
     * Placeholder for REMINDER_FORCE_TRANSLIT alter.ini option
     */
    protected $rmdForceTranslit = true;

    /**
     * Placeholder for REMINDER_USE_EXTMOBILES alter.ini option
     *
     * @var bool
     */
    protected $rmdUseExtMobiles = false;

    /**
     * Placeholder for REMINDER_CONSIDER_CREDIT alter.ini option
     *
     * @var bool
     */
    protected $rmdConsiderCredits = 0;

    /**
     * Placeholder for REMINDER_DAYS_THRESHOLD_CREDIT alter.ini option
     *
     * @var int
     */
    protected $rmdDaysThresholdCredit = 0;

    /**
     * Placeholder for REMINDER_TEMPLATE_CREDIT alter.ini option
     *
     * @var string
     */
    protected $rmdTemplateCredit = '';

    /**
     * Placeholder for REMINDER_CONSIDER_CAP alter.ini option
     *
     * @var bool
     */
    protected $rmdConsiderCAP = 0;

    /**
     * Placeholder for CAP_DAYLIMIT alter.ini option
     *
     * @var int
     */
    protected $rmdCAPDayLimit = 0;

    /**
     * Placeholder for REMINDER_DAYS_THRESHOLD_CAP alter.ini option
     *
     * @var int
     */
    protected $rmdDaysThresholdCAP = 0;

    /**
     * Placeholder for REMINDER_TEMPLATE_CAP alter.ini option
     *
     * @var string
     */
    protected $rmdTemplateCAP = '';

    /**
     * Placeholder for REMINDER_CONSIDER_FROZEN alter.ini option
     *
     * @var bool
     */
    protected $rmdConsiderFrozen = 0;

    /**
     * Placeholder for REMINDER_DAYS_THRESHOLD_FROZEN alter.ini option
     *
     * @var int
     */
    protected $rmdDaysThresholdFrozen = 0;

    /**
     * Placeholder for REMINDER_TEMPLATE_FROZEN alter.ini option
     *
     * @var string
     */
    protected $rmdTemplateFrozen = '';

    /**
     * Placeholder for REMINDER_DEBUG_ENABLED alter.ini option
     *
     * @var bool
     */
    protected $rmdDebugON = false;

    /**
     * Placeholder for UbillingConfig object
     *
     * @var null
     */
    protected $ubConfig = null;

    /**
     * Placeholder for MobilesExt object
     *
     * @var null
     */
    protected $extMobilesObj = null;

    const FLAGPREFIX = 'exports/REMINDER.';
    const CREDITPREFIX = 'CREDIT.';
    const CAPPREFIX = 'CAP.';
    const FROZENPREFIX = 'FROZEN.';

    /**
     * it's a magic
     */
    public function __construct() {
        global $ubillingConfig;
        $this->ubConfig = $ubillingConfig;

        //$this->loadAlter();
        $this->loadOptions();
        $this->loadAllTemplates();
        $this->loadRemindLogin();
        $this->sms = new UbillingSMS();
        $this->extMobilesObj = new MobilesExt();
        $this->money = new FundsFlow();
        $this->money->runDataLoders();
    }

    /**
     * load alter.ini config
     *
     * @return void
     */
    /*    protected function loadAlter() {
      $this->AltCfg = $this->ubConfig->getAlter();
      $this->considerCredits = $this->ubConfig->getAlterParam('REMINDER_CONSIDER_CREDIT');
      // check if credits considering correct mode is set
      $this->considerCredits = (empty($this->considerCredits) or $this->considerCredits > 2) ? 0 : $this->considerCredits;

      }
     */

    /**
     * Loads essential options values
     *
     * @throws Exception
     */
    protected function loadOptions() {
        $this->rmdMode = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_ENABLED'), 'int');
        $this->rmdTagID = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_TAGID'), 'int');
        $this->rmdDaysThreshold = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_DAYS_THRESHOLD'), 'int');
        $this->rmdUseExtMobiles = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_USE_EXTMOBILES'), 'fi', FILTER_VALIDATE_BOOLEAN);
        $this->rmdDebugON = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_DEBUG_ENABLED'), 'fi', FILTER_VALIDATE_BOOLEAN);
        $this->rmdConsiderCredits = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_CONSIDER_CREDIT'), 'fi', FILTER_VALIDATE_BOOLEAN);
        $this->rmdDaysThresholdCredit = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_DAYS_THRESHOLD_CREDIT'), 'int');
        $this->rmdCAPDayLimit = ubRouting::filters($this->ubConfig->getAlterParam('CAP_DAYLIMIT'), 'int');
        $this->rmdConsiderCAP = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_CONSIDER_CAP'), 'fi', FILTER_VALIDATE_BOOLEAN);
        $this->rmdDaysThresholdCAP = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_DAYS_THRESHOLD_CAP'), 'int');
        $this->rmdConsiderFrozen = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_CONSIDER_FROZEN'), 'fi', FILTER_VALIDATE_BOOLEAN);
        $this->rmdDaysThresholdFrozen = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_DAYS_THRESHOLD_FROZEN'), 'int');
        $this->rmdPhonePrefix = $this->ubConfig->getAlterParam('REMINDER_PREFIX');
        $this->rmdPhonePrefix = empty($this->rmdPhonePrefix) ? '' : $this->rmdPhonePrefix;
        $this->rmdTemplate = $this->ubConfig->getAlterParam('REMINDER_TEMPLATE');
        $this->rmdTemplateCredit = $this->ubConfig->getAlterParam('REMINDER_TEMPLATE_CREDIT');
        $this->rmdTemplateCAP = $this->ubConfig->getAlterParam('REMINDER_TEMPLATE_CAP');
        $this->rmdTemplateFrozen = $this->ubConfig->getAlterParam('REMINDER_TEMPLATE_FROZEN');
        $this->rmdForceTranslit = ubRouting::filters($this->ubConfig->getAlterParam('REMINDER_FORCE_TRANSLIT', true), 'fi', FILTER_VALIDATE_BOOLEAN);

        if (!ubRouting::filters($this->ubConfig->getAlterParam('CAP_ENABLED'), 'fi', FILTER_VALIDATE_BOOLEAN)
                or empty($this->rmdCAPDayLimit)) {

            $this->rmdConsiderCAP = false;
            log_register('REMINDER WARNING: CAP CONSIDERING DISABLED BECAUSE CAP SERVICE IS OFF');
        }

        if (!ubRouting::filters($this->ubConfig->getAlterParam('FREEZE_DAYS_CHARGE_ENABLED'), 'fi', FILTER_VALIDATE_BOOLEAN)) {

            $this->rmdConsiderFrozen = false;
            log_register('REMINDER WARNING: FROZEN CONSIDERING DISABLED BECAUSE FREEZE DAYS CHARGE SERVICE IS OFF');
        }

        if (empty($this->rmdTemplate)) {
            $this->rmdTemplate = '';
            log_register('REMINDER WARNING: TEMPLATE IS EMPTY');
        }

        if (empty($this->rmdTemplateCredit)) {
            $this->rmdTemplateCredit = '';

            if ($this->rmdConsiderCredits) {
                log_register('REMINDER WARNING: CREDIT TEMPLATE IS EMPTY');
            }
        }

        if (empty($this->rmdTemplateCAP)) {
            $this->rmdTemplateCAP = '';

            if ($this->rmdConsiderCAP) {
                log_register('REMINDER WARNING: CAP TEMPLATE IS EMPTY');
            }
        }

        if (empty($this->rmdTemplateFrozen)) {
            $this->rmdTemplateFrozen = '';

            if ($this->rmdConsiderFrozen) {
                log_register('REMINDER WARNING: FROZEN TEMPLATE IS EMPTY');
            }
        }
    }

    /**
     * Load all users templates
     *
     * @return void
     */
    protected function loadAllTemplates() {
        $this->AllTemplates = zb_TemplateGetAllUserData();
    }

    /**
     * load all logins whith cash >=0 and with set tagid to $alllogin
     *
     * @return void
     */
    protected function loadRemindLogin() {
        if (!empty($this->rmdTagID)) {
            $creditFields = '';
            $capFields = '';
            $capJOIN = '';
            $frozenFields = '';
            $frozenJOIN = '';
            $whereString = " WHERE `users`.`Passive` != '1' ";

            // check if credits considering enabled
            if ($this->rmdConsiderCredits > 0) {
                $creditFields = " `users`.`Credit`, `users`.`CreditExpire`, ";
            }

            // check if CAP considering enabled
            if ($this->rmdConsiderCAP) {
                $capFields = " `capdata`.`days`, ";
                $capJOIN = " LEFT JOIN `capdata` ON `t_login`.`login` = `capdata`.`login`  
                                            AND `capdata`.`days` < " . $this->rmdCAPDayLimit;
            }

            // check if frozen considering enabled
            if ($this->rmdConsiderFrozen) {
                $whereString = "";
                $frozenFields = " `users`.`Passive`, `frozen_charge_days`.`freeze_days_amount`, `frozen_charge_days`.`freeze_days_used`, ";
                $frozenJOIN = " LEFT JOIN `frozen_charge_days` ON `t_login`.`login` = `frozen_charge_days`.`login` 
                                             AND `frozen_charge_days`.`freeze_days_used` < `frozen_charge_days`.`freeze_days_amount` ";
            }

            $query = "
                SELECT `users`.`login`, `users`.`Cash`, " . $creditFields . $capFields . $frozenFields . " `phones`.`mobile`
                    FROM (SELECT `tags`.`login` FROM `tags` WHERE tags.tagid = '" . $this->rmdTagID . "') as t_login 
                        INNER JOIN `users` ON `t_login`.`login` = `users`.`login`
                        INNER JOIN (SELECT `phones`.`login`, `phones`.`mobile` FROM `phones`) `phones` ON `t_login`.`login` = `phones`.`login` "
                    . $capJOIN
                    . $frozenJOIN
                    . $whereString;

            $tmp = simple_queryall($query);

            if (!empty($tmp)) {
                $this->AllLogin = $tmp;
            }
        } else {
            log_register('REMINDER FAILED: EMPTY TAG ID');
        }
    }

    /**
     * Creates a new remind message actually
     *
     * @param string $login
     * @param array $numbers
     * @param string $filePrefix
     * @param bool $forced
     * @param string $remindTemplate
     */
    protected function createRemindMsg($login, $numbers, $filePrefix, $forced = false, $remindTemplate = '') {
        if (!empty($numbers)) {
            $template = (empty($remindTemplate)) ? $this->rmdTemplate : $remindTemplate;

            if (!empty($template)) {
                $message = zb_TemplateReplace($login, $template, $this->AllTemplates);

                if (!empty($message)) {
                    foreach ($numbers as $number) {
                        $number = trim($number);
                        $number = str_replace($this->rmdPhonePrefix, '', $number);
                        $number = ubRouting::filters($number, 'int');
                        $number = $this->rmdPhonePrefix . $number;

                        $queueFile = $this->sms->sendSMS($number, $message, $this->rmdForceTranslit, 'REMINDER');
                        $this->sms->setDirection($queueFile, 'user_login', $login);

                        if ($forced) {
                            log_register('REMINDER FORCE SEND SMS (' . $login . ') NUMBER `' . $number . '`');
                        } else {
                            file_put_contents($filePrefix . $login, '');
                        }
                    }
                }
            }
        } else {
            log_register('REMINDER EMPTY NUMBER (' . $login . ')');
        }
    }

    /**
     * Make queue for sms send
     *
     * @return void
     */
    public function remindUsers() {
        foreach ($this->AllLogin as $userLoginData) {
            // yep, we evaluate $liveDays, $liveTime and $cacheTime on every iteration
            // 'cause they may be re-assigned below, depending on processing type
            $liveDays = $this->rmdDaysThreshold;
            $liveTime = $liveDays * 24 * 60 * 60;
            $cacheTime = time() - $liveTime;
            $eachLogin = $userLoginData['login'];
            $numbers = array($userLoginData['mobile']);
            $onlineDaysLeft = $this->money->getOnlineLeftCountFast($eachLogin);

            if ($this->rmdUseExtMobiles) {
                $userExtMobs = $this->extMobilesObj->getUserMobiles($eachLogin, true);
                $userExtMobs = (empty($userExtMobs[$eachLogin])) ? array() : $userExtMobs[$eachLogin];
                $numbers = $numbers + $userExtMobs;
            }

            // process base service expiration
            // certain user must not be a debtor and not to be frozen and processing mode must not be equal to 2
            if ($onlineDaysLeft <= $liveDays and $onlineDaysLeft >= 0 and $this->rmdMode != 2 and empty($userLoginData['Passive'])) {

                if (!file_exists(self::FLAGPREFIX . $eachLogin)) {
                    $this->createRemindMsg($eachLogin, $numbers, self::FLAGPREFIX);
                    $this->debugReminder('CONSIDER BASE SERVICE', $eachLogin, $userLoginData['Cash'], print_r($numbers, true), $liveDays, $liveTime, $cacheTime, 'online days left: ' . $onlineDaysLeft);
                }
            }

            // process credit expiration date
            // certain user must be a debtor, and must have active non-expired and non-eternal credit
            if ($this->rmdConsiderCredits and $onlineDaysLeft == -1 and empty($userLoginData['Passive'])) {
                if (!empty($this->rmdDaysThresholdCredit)) {
                    $liveDays = $this->rmdDaysThresholdCredit;
                    $liveTime = $liveDays * 24 * 60 * 60;
                    $cacheTime = time() - $liveTime;
                }

                if (!file_exists(self::FLAGPREFIX . self::CREDITPREFIX . $eachLogin)) {
                    $creditSum = $userLoginData['Credit'];
                    $creditExpireTime = $userLoginData['CreditExpire'];

                    if (!empty($creditSum) and empty($creditExpireTime)) {
                        log_register('REMINDER IGNORING ETERNAL CREDIT FOR (' . $eachLogin . ')');
                    } else {
                        $remindStartTime = $creditExpireTime - $liveTime;
                        $curTime = time();

                        if ($remindStartTime <= $curTime and $creditExpireTime > $curTime) {
                            $this->createRemindMsg($eachLogin, $numbers, self::FLAGPREFIX . self::CREDITPREFIX, false, $this->rmdTemplateCredit);
                            $this->debugReminder('CONSIDER CREDIT', $eachLogin, $userLoginData['Cash'], print_r($numbers, true), $liveDays, $liveTime, $cacheTime, 'remind start time: ' . $remindStartTime . ' current time: ' . $curTime . ' credit expire time: ' . $creditExpireTime);
                        }
                    }
                }
            }

            // process CAP users
            // certain user must be a debtor, not to be frozen and to have a CAP record
            if ($this->rmdConsiderCAP and $onlineDaysLeft == -1 and ! empty($userLoginData['days']) and empty($userLoginData['Passive'])) {
                if (!empty($this->rmdDaysThresholdCAP)) {
                    $liveDays = $this->rmdDaysThresholdCAP;
                    $liveTime = $liveDays * 24 * 60 * 60;
                    $cacheTime = time() - $liveTime;
                }

                if (!file_exists(self::FLAGPREFIX . self::CAPPREFIX . $eachLogin)) {
                    $capDaysLeft = $this->rmdCAPDayLimit - $userLoginData['days'];

                    if ($capDaysLeft <= $liveDays) {
                        $this->createRemindMsg($eachLogin, $numbers, self::FLAGPREFIX . self::CAPPREFIX, false, $this->rmdTemplateCAP);
                        $this->debugReminder('CONSIDER CAP', $eachLogin, $userLoginData['Cash'], print_r($numbers, true), $liveDays, $liveTime, $cacheTime, 'days till CAP left: ' . $capDaysLeft);
                    }
                }
            }

            // process frozen users
            // certain user must be frozen
            if ($this->rmdConsiderFrozen and ! empty($userLoginData['Passive'])
                    and ! empty($userLoginData['freeze_days_amount']) and ! empty($userLoginData['freeze_days_used'])) {

                if (!empty($this->rmdDaysThresholdFrozen)) {
                    $liveDays = $this->rmdDaysThresholdFrozen;
                    $liveTime = $liveDays * 24 * 60 * 60;
                    $cacheTime = time() - $liveTime;
                }

                if (!file_exists(self::FLAGPREFIX . self::FROZENPREFIX . $eachLogin)) {
                    $freezeDaysLeft = $userLoginData['freeze_days_amount'] - $userLoginData['freeze_days_used'];

                    if ($freezeDaysLeft <= $liveDays) {
                        $this->createRemindMsg($eachLogin, $numbers, self::FLAGPREFIX . self::FROZENPREFIX, false, $this->rmdTemplateFrozen);
                        $this->debugReminder('CONSIDER FROZEN', $eachLogin, $userLoginData['Cash'], print_r($numbers, true), $liveDays, $liveTime, $cacheTime, 'freeze days left: ' . $freezeDaysLeft);
                    }
                }
            }

            // make free tariff ignorance notice
            if ($onlineDaysLeft == -2) {
                log_register('REMINDER IGNORING FREE TARIFF (' . $eachLogin . ')');
            }

            $this->checkFlagFiles($eachLogin, $cacheTime);
        }
    }

    /**
     * Make queue for sms send for all users with remind tag
     *
     * @return void
     */
    public function forceRemind() {
        foreach ($this->AllLogin as $userLoginData) {
            $eachLogin = $userLoginData['login'];
            $numbers = array($userLoginData['mobile']);

            if ($this->rmdUseExtMobiles) {
                $userExtMobs = $this->extMobilesObj->getUserMobiles($eachLogin, true);
                $userExtMobs = (empty($userExtMobs[$eachLogin])) ? array() : $userExtMobs[$eachLogin];
                $numbers = $numbers + $userExtMobs;
            }

            $this->createRemindMsg($eachLogin, $numbers, '', true);
        }
    }

    /**
     * Checks if user's flag file exists and it's lifetime expired and the file needs to be removed
     *
     * @param $login
     * @param $cacheTime
     *
     * @return void
     */
    public function checkFlagFiles($login, $cacheTime) {
        $flagFilePaths = array(self::FLAGPREFIX . $login,
            self::FLAGPREFIX . self::CREDITPREFIX . $login,
            self::FLAGPREFIX . self::CAPPREFIX . $login,
            self::FLAGPREFIX . self::FROZENPREFIX . $login
        );

        foreach ($flagFilePaths as $filePath) {
            if (file_exists($filePath)) {
                if ($cacheTime > filemtime($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    /**
     * Provides debugging of reminder processing
     *
     * @param $topic
     * @param $login
     * @param $cash
     * @param $phones
     * @param $liveDays
     * @param $liveTime
     * @param $cacheTime
     * @param $leftTime
     */
    protected function debugReminder($topic, $login, $cash, $phones, $liveDays, $liveTime, $cacheTime, $leftTime) {
        if ($this->rmdDebugON) {
            log_register('REMINDER ' . $topic);
            log_register('REMINDER login: (' . $login . '). Cash: ' . $cash);
            log_register('REMINDER phones: ' . print_r($phones, true));
            log_register('REMINDER liveDays: ' . $liveDays);
            log_register('REMINDER liveTime: ' . $liveTime);
            log_register('REMINDER cacheTime: ' . $cacheTime);
            log_register('REMINDER ' . $leftTime);
        }
    }

}
