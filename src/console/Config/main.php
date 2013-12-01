<?php

return array(
    /*
     * The directory to look in to find commands.
     */
    'directory' => 'Command',

    /*
     * The help text to display when no commands are specified.
     */
    'help_text' => "Usage: $ clip <command-name> [parameters...]\r\n\r\nThe following commands are available:\r\n",

    /*
     * A list of any commands that you don't want run via clip.
     * For instance abstract classes that implement the clip
     * command interface but aren't really meant to be run directly.
     */
    'exclude' => array(),
);