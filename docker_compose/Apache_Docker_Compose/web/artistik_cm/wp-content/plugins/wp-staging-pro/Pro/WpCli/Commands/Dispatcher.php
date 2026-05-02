<?php

/**
 * Routes WP-CLI commands to their respective handlers.
 *
 * For full documentation of available options and examples, see:
 * dev/docs/cli/wp-cli-commands.md
 */

namespace WPStaging\Pro\WpCli\Commands;

use WP_CLI\ExitException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Facades\Hooks;

class Dispatcher
{
    /** @var string */
    const FILTER_WPCLI_SUBCOMMAND_MAP = 'wpstg_wpcli_subcommand_map';

    /** @var bool */
    protected static $setUp = false;

    /**
     * @return array
     */
    public static function registrationArgs()
    {
        return [
            'shortdesc' => 'Manages WP STAGING | PRO cloning and pushing operations.',
        ];
    }

    /**
     * Creates a site backup.
     *
     * @subcommand backup-create
     * @throws ExitException
     */
    public function backupCreate(array $args = [], array $assocArgs = [])
    {
        /** @var BackupCreateCommand $command */
        $command = static::getSubcommand('backup-create');
        return $command($args, $assocArgs);
    }

    /**
     * Checks the status of a backup job.
     * @deprecated
     * @subcommand backup-status
     * @throws ExitException
     */
    public function backupStatus(array $args = [], array $assocArgs = [])
    {
        /** @var BackupStatusCommand $command */
        $command = static::getSubcommand('backup-status');
        return $command($args, $assocArgs);
    }

    /**
     * Checks the status of a backup or staging job.
     *
     * @subcommand status
     * @throws ExitException
     */
    public function status(array $args = [], array $assocArgs = [])
    {
        /** @var StatusCommand $command */
        $command = static::getSubcommand('status');
        return $command($args, $assocArgs);
    }

    /**
     * Deletes a staging site by ID.
     *
     * @subcommand staging-site-delete
     * @throws ExitException
     */
    public function stagingSiteDelete(array $args = [], array $assocArgs = [])
    {
        /** @var StagingSiteDeleteCommand $command */
        $command = static::getSubcommand('staging-site-delete');
        return $command($args, $assocArgs);
    }

    /**
     * Creates a staging site with advanced options, exclusions, and multisite support.
     *
     * @subcommand staging-site-create
     * @throws ExitException
     */
    public function stagingSiteCreate(array $args = [], array $assocArgs = [])
    {
        /** @var StagingSiteCreateCommand $command */
        $command = static::getSubcommand('staging-site-create');
        return $command($args, $assocArgs);
    }

    /**
     * Resets an existing staging site to match production.
     *
     * @subcommand staging-site-reset
     * @throws ExitException
     */
    public function stagingSiteReset(array $args = [], array $assocArgs = [])
    {
        /** @var StagingSiteResetCommand $command */
        $command = static::getSubcommand('staging-site-reset');
        return $command($args, $assocArgs);
    }

    /**
     * Returns the sub-command mapped to the sub-command slug.
     *
     * @param string $subCommand The sub-command slug.
     * @return CommandInterface A command instance reference.
     */
    public static function getSubcommand($subCommand)
    {
        $subCommandMap = self::getSubCommandMap();

        $commandClass = isset($subCommandMap[$subCommand]) ? $subCommandMap[$subCommand] : static::class;

        if ($commandClass === false) {
            throw new \LogicException("No command class is mapped to the {$subCommand} sub-command!");
        }

        if (!in_array(CommandInterface::class, class_implements($commandClass) ?: [], true)) {
            throw new \LogicException(
                "The class {$commandClass} MUST implement the " . CommandInterface::class . ' interface.'
            );
        }

        return WPStaging::make($commandClass);
    }

    /**
     * Returns the map from sub-command slugs to their implementing classes.
     *
     * @return array<string,string>
     */
    protected static function getSubCommandMap()
    {
        $subCommandMap = [
            'backup-create'       => BackupCreateCommand::class,
            'backup-status'       => BackupStatusCommand::class,
            'status'              => StatusCommand::class,
            'staging-site-delete' => StagingSiteDeleteCommand::class,
            'staging-site-create' => StagingSiteCreateCommand::class,
            'staging-site-reset'  => StagingSiteResetCommand::class,
        ];

        /**
         * Allows filtering the map from command slugs to the classes implementing them.
         *
         * @param array<string,string> $subCommandMap
         */
        return Hooks::applyFilters(self::FILTER_WPCLI_SUBCOMMAND_MAP, $subCommandMap);
    }
}
