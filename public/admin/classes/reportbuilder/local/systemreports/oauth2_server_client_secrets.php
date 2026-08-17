<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace core_admin\reportbuilder\local\systemreports;

use core_reportbuilder\local\report\column;
use core_reportbuilder\system_report;
use lang_string;

/**
 * OAuth2 server clients system report class.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Gehoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class oauth2_server_client_secrets extends system_report {
    /**
     * Initialise report parameters, table, columns and filters.
     */
    protected function initialise(): void {
        // Main DB table definition from oauth2_server_client_secrets schema.
        $this->set_main_table('oauth2_server_client_secrets', 'client_secret');
        $clientidentifier = $this->get_parameter('clientidentifier', '', PARAM_TEXT);
        $this->add_base_condition_simple("client_secret.clientidentifier", $clientidentifier);
        // Register entity name for columns and filters.
        $this->annotate_entity('client_secret', new lang_string('oauth2server_client', 'admin'));
        $this->add_columns();
    }

    /**
     * Validates capability to view report.
     */
    public function can_view(): bool {
        $composer = \core\di::get(\core\composer::class);
        // The 'league/oauth2-server' composer package needs to be installed and the user has to have a capability to
        // manage OAuth 2 clients.
        $iscomposerpackageinstalled = $composer->get_package_status('league/oauth2-server')->installed;
        $canmanageoauth2clients = has_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        return $iscomposerpackageinstalled && $canmanageoauth2clients;
    }

    /**
     * Add report columns.
     */
    protected function add_columns(): void {
        // Client Name.
        $this->add_column((new column(
            'id',
            new lang_string('oauth2server_clientsecretid', 'admin'),
            'client_secret'
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields('client_secret.id')
            ->set_is_sortable(true)
            ->add_callback(function ($value) {
                return $value;
            })
        );

        // Time Created.
        $this->add_column((new column(
            'timecreated',
            new lang_string('timecreated', 'core'),
            'client_secret'
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields('client_secret.timecreated')
            ->set_is_sortable(true)
            ->add_callback(function($value) {
                return $value ? userdate($value, '%d %b %Y') : '-';
            })
        );

        // Expiry time.
        $this->add_column((new column(
            'expirytime',
            new lang_string('oauth2server_clientsecretexpires', 'admin'),
            'client_secret'
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields('client_secret.expirytime')
            ->set_is_sortable(true)
            ->add_callback(function($value) {
                return $value ? userdate($value, '%d %b %Y') : '-';
            })
        );

        // Status.
        $this->add_column((new column(
            'status',
            new lang_string('status', 'core'),
            'client_secret'
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields('client_secret.revoked')
            ->set_is_sortable(true)
            ->add_callback(function ($value) {
                $isrevoked = (bool) $value;

                $statusstring = $isrevoked
                    ? get_string('oauth2server_clientstatusrevoked', 'admin')
                    : get_string('oauth2server_clientstatusactive', 'admin');

                // Set the badge type based on the 'revoked' state.
                $badgetype = $isrevoked ? 'danger' : 'success';

                return \html_writer::tag(
                    'span',
                    $statusstring,
                    ['class' => "badge bg-{$badgetype}-subtle text-{$badgetype}-emphasis"],
                );
            })
        );

        // Last Accessed.
        $this->add_column((new column(
            'lastaccessed',
            new lang_string('lastaccess', 'core'),
            'client_secret'
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields('client_secret.lastaccessed')
            ->set_is_sortable(true)
            ->add_callback(function($value) {
                return $value ? userdate($value, '%d %b %Y') : '-';
            })
        );
    }
}