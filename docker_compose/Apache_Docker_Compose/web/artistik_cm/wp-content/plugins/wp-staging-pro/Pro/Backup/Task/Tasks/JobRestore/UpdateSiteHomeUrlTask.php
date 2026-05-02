<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobRestore;

use WPStaging\Backup\Task\RestoreTask;
use WPStaging\Framework\Adapter\DatabaseInterface;
use WPStaging\Framework\Job\Dto\StepsDto;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class UpdateSiteHomeUrlTask extends RestoreTask
{
    /**
     * @var string $tmpOptionsTable Temporary options table name.
     */
    private $tmpOptionsTable;

    /**
     * @var object $database Database connection object.
     */
    private $database;

    public function __construct(
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        DatabaseInterface $database
    ) {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->database = $database;
    }


    /**
     * Retrieves the name of the task.
     *
     * @return string
     */
    public static function getTaskName()
    {
        return 'backup_restore_update_site_and_home_url';
    }

    /**
     * Get the title of the task.
     *
     * @return string
     */
    public static function getTaskTitle()
    {
        return 'Updating Site and Home URL';
    }

    /**
     * Executes the task to update the site URL and home URL in the temporary options table.
     *
     * @return mixed
     */
    public function execute()
    {
        $this->tmpOptionsTable  = 'wpstgtmp_options';

        $currentHomeUrl = home_url();
        $currentSiteUrl = site_url();
        $tmpHomeUrl     = $this->getTmpOption('home');
        $tmpSiteUrl     = $this->getTmpOption('siteurl');

        if ($currentHomeUrl === $tmpHomeUrl && $currentSiteUrl === $tmpSiteUrl) {
            $this->logger->info(esc_html__('Skipped updating site URL and home URL, as they already same.', 'wp-staging'));
            return $this->generateResponse(true);
        }

        try {
            $this->updateTmpOption('home', $currentHomeUrl);
            $this->updateTmpOption('siteurl', $currentSiteUrl);
        } catch (\Throwable $th) {
            $this->logger->warning(esc_html__('Failed to update site URL and home URL in database.', "wp-staging"));
        }

        $this->logger->info(esc_html__('Updating site URL and home URL in database finished.', 'wp-staging'));

        return $this->generateResponse(true);
    }

    /**
     * Retrieves the temporary option value from the temporary options table.
     *
     * @param string $optionName The name of the option to retrieve.
     * @return mixed The value of the specified option.
     */
    private function getTmpOption($optionName)
    {
        $result = $this->database->getClient()->query("SELECT option_value FROM `$this->tmpOptionsTable` WHERE option_name = '$optionName'");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['option_value'];
        }

        return null;
    }

    /**
     * Updates a temporary option in the database.
     *
     * @param string $optionName The name of the option to update.
     * @param mixed $optionValue The new value for the option.
     *
     * @return void
     */
    private function updateTmpOption($optionName, $optionValue)
    {
        $this->database->getClient()->query("UPDATE `$this->tmpOptionsTable` SET option_value = '$optionValue' WHERE option_name = '$optionName'");
    }
}
