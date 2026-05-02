<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Notifications\Notifications;
use WPStaging\Staging\Sites;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Security\Capabilities;

use function WPStaging\functions\debug_log;

class EmailReminder
{
    /**
     * @var int $reminderInterval
     */
    private $reminderInterval = 2 * WEEK_IN_SECONDS; // 2 weeks

    /**
     * @var Sites
     */
    private $sites;

    public function __construct(Sites $sites)
    {
        $this->sites = $sites;
    }

    /**
     * @return void
     */
    public function disableRemindEmailPublicEndpoint()
    {
        /** @var Capabilities */
        $capabilities = WPStaging::make(Capabilities::class);
        if (!current_user_can($capabilities->manageWPSTG())) {
            wp_die(esc_html__('You don\'t have enough permissions to disable staging site email reminder. Log in to your WordPress account with an admin account, then open this link again.', 'wp-staging'));
        }

        $cloneId = empty($_GET['site']) ? '' : sanitize_text_field($_GET['site']);
        if (empty($cloneId)) {
            wp_die(esc_html__('Invalid request. Staging site is missing.', 'wp-staging'));
        }

        $availableClones = (array)get_option(Sites::STAGING_SITES_OPTION, []);
        if (empty($availableClones[$cloneId])) {
            wp_die(esc_html__('Invalid request. Staging site not found.', 'wp-staging'));
        }

        $availableClones[$cloneId]['isEmailsReminderEnabled'] = false;
        $this->sites->updateStagingSites($availableClones);
        wp_die(sprintf(esc_html__('The reminder for the staging site %s has been disabled successfully! If you want to activate it again, you need to update the staging site by going to WP Staging -> Staging Sites -> Actions -> Update', 'wp-staging'), '<a href=' . esc_url($availableClones[$cloneId]['url']) . ' target=_blank>' . esc_html($availableClones[$cloneId]['cloneName']) . '</a>'));
    }

    /**
     * Check if it's time to send the reminder and send it if necessary
     * @return void
     */
    public function maybeSendEmailReminder()
    {
        $availableClones = (array)get_option(Sites::STAGING_SITES_OPTION, []);
        foreach ($availableClones as $key => $clone) {
            if (empty($clone['isEmailsReminderEnabled'])) {
                continue;
            }

            $lastSent = empty($clone['datetime']) ? 0 : $clone['datetime'];
            if (!empty($clone['lastTimeReminderEmail'])) {
                $lastSent = $clone['lastTimeReminderEmail'];
            }

            if ((time() - $lastSent) < $this->reminderInterval) {
                continue;
            }

            $isMailSent = $this->sendEmailReminder($clone, $key);
            if ($isMailSent) {
                $clone['lastTimeReminderEmail'] = time();
                $availableClones[$key]          = $clone;

                $this->sites->updateStagingSites($availableClones);
            }
        }
    }

    /**
     * @return void
     */
    public function sendStagingEmailNotification()
    {
        if (!defined('WPSTG_DEV') || !WPSTG_DEV) {
            return;
        }

        if (!defined('WPSTG_EMAIL_REMINDER_PERIOD') || !is_int(WPSTG_EMAIL_REMINDER_PERIOD)) {
            return;
        }

        $this->reminderInterval = WPSTG_EMAIL_REMINDER_PERIOD * 60; // in minute.
        $this->maybeSendEmailReminder();
    }

    /**
     * @param  array $clone
     * @param  string $cloneKey
     * @return bool
     */
    private function sendEmailReminder(array $clone, string $cloneKey): bool
    {
        if (empty($clone['url'])) {
            debug_log('Fail to send email reminder because clone url is missing.');
            return false;
        }

        $reportEmail = get_option(Notifications::OPTION_BACKUP_SCHEDULE_REPORT_EMAIL, '');
        if (empty($reportEmail)) {
            debug_log('Fail to send email reminder because report email is missing.');
            return false;
        }

        $subject = "WP Staging Reminder - Once Every 2 weeks";

        $linkToDisableNotifications = add_query_arg([
            'action' => 'wpstg-disable-staging-reminder',
            'site'   => $cloneKey,
        ], admin_url('admin-post.php'));

        $message = sprintf(
            esc_html__("This is a reminder that the staging site %s is still available.
If you no longer need this staging site, you can delete it for safety purposes:

    - Open %s.
    - Click on %s.

If you do not wish to receive email reminders for this staging site any longer,
click this link: %s

You will receive the next reminder in 2 weeks!", 'wp-staging'),
            esc_url($clone['url']),
            'WP Staging > Staging Sites',
            'Actions > Delete',
            sanitize_url($linkToDisableNotifications)
        );
        $notifications = WPStaging::make(Notifications::class);
        if (get_option(Notifications::OPTION_SEND_EMAIL_AS_HTML, false) === 'true') {
            return $notifications->sendEmailAsHTML($reportEmail, $subject, $message);
        }

        return $notifications->sendEmail($reportEmail, $subject, $message);
    }
}
