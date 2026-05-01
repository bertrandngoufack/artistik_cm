<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Email Configuration
 */
class Email extends BaseConfig
{
    /**
     * @var string
     */
    public string $fromEmail = '';

    /**
     * @var string
     */
    public string $fromName = 'KISSAI SCHOOL';

    /**
     * @var string
     */
    public string $protocol = 'smtp';

    /**
     * @var string
     */
    public string $SMTPHost = '';

    /**
     * @var int
     */
    public int $SMTPPort = 587;

    /**
     * @var string
     */
    public string $SMTPUser = '';

    /**
     * @var string
     */
    public string $SMTPPass = '';

    /**
     * @var string
     */
    public string $SMTPCrypto = 'tls';

    /**
     * @var bool
     */
    public bool $SMTPAuth = true;

    /**
     * @var bool
     */
    public bool $SMTPKeepAlive = false;

    /**
     * @var string
     */
    public string $mailType = 'html';

    /**
     * @var string
     */
    public string $charset = 'UTF-8';

    /**
     * @var bool
     */
    public bool $validate = false;

    /**
     * @var int
     */
    public int $priority = 3;

    /**
     * @var bool
     */
    public bool $BCCBatchMode = false;

    /**
     * @var int
     */
    public int $BCCBatchSize = 200;

    /**
     * @var bool
     */
    public bool $DSN = false;

    /**
     * --------------------------------------------------------------------------
     * Email Debug
     * --------------------------------------------------------------------------
     *
     * When true, this will enable email debugging.
     */
    public bool $debug = true;

    /**
     * --------------------------------------------------------------------------
     * Email Logging
     * --------------------------------------------------------------------------
     *
     * When true, this will log email information.
     */
    public bool $logging = true;
}
