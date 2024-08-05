<?php

namespace FluentForm\Database;

use FluentForm\Database\Migrations\Logs;
use FluentForm\Database\Migrations\Forms;
use FluentForm\Database\Migrations\FormMeta;
use FluentForm\Database\Migrations\Submissions;
use FluentForm\Database\Migrations\FormAnalytics;
use FluentForm\Database\Migrations\SubmissionMeta;
use FluentForm\Database\Migrations\ScheduledActions;
use FluentForm\Database\Migrations\SubmissionDetails;

class DBMigrator
{
    public static function run($network_wide = false)
    {
        Forms::migrate();
        FormMeta::migrate();
        Submissions::migrate(true);
        SubmissionMeta::migrate();
        FormAnalytics::migrate();
        SubmissionDetails::migrate();
        Logs::migrate();
        ScheduledActions::migrate();
    }
}
