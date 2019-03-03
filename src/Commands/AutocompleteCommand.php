<?php

namespace TerminusPluginProject\TerminusAutocomplete\Commands;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Exceptions\TerminusNotFoundException;

// Define the operating system used by Terminus
if (!defined('TERMINUS_OS')) {
    define('TERMINUS_OS', strtoupper(substr(PHP_OS, 0, 3)));
}

/**
 * Class AutocompleteCommand
 * Terminus plugin to help provide tab completion for commands.
 */
class AutocompleteCommand extends TerminusCommand
{
    /**
     * Provide tab completion for commands.
     *
     * @command autocomplete:install
     *
     * @aliases ac:install
     */
    public function install()
    {
        $this->checkRequirements();
        $message = "To complete the installation, paste in the terminal the following:\n\n";
        $message .= "terminus autocomplete:update\n";
        if (TERMINUS_OS == 'DAR') {
            $message .= "echo 'source \$(brew --prefix)/etc/bash_completion' >> ~/.bash_profile\n";
            $message .= "echo 'source ~/.terminus-autocomplete' >> ~/.bash_profile\n";
            $message .= "source ~/.bash_profile\n";
        } else {
            $message .= "echo 'source /etc/bash_completion' >> ~/.bashrc\n";
            $message .= "echo 'source ~/.terminus-autocomplete' >> ~/.bashrc\n";
            $message .= "source ~/.bashrc\n";
        }
        $message .= "terminus autocomplete:test";
        $this->log()->notice($message);
    }

    /**
     * Check requirements for tab completion.
     *
     * @command autocomplete:check
     *
     * @aliases ac:check
     */
    public function check()
    {
        $this->checkRequirements();
        $message = "All requirements are installed.  Type 'terminus autocomplete:install' to complete.";
        $this->log()->notice($message);
    }

    /**
     * Test if tab completion is working.
     *
     * @command autocomplete:test
     *
     * @aliases ac:test
     */
    public function test()
    {
        $message = "To test if autocomplete is working, type 'terminus auto<TAB>' and it should expand to 'terminus autocomplete:'.";
        $this->log()->notice($message);
    }

    /**
     * Update commands for tab completion.
     *
     * @command autocomplete:update
     *
     * @aliases ac:update
     */
    public function update()
    {
        $this->checkRequirements();
        $command = 'symfony-autocomplete terminus > ~/.terminus-autocomplete';
        $this->execute($command);
        $message = 'Terminus autocomplete commands have been updated.';
        $this->log()->notice($message);
    }

    /**
     * Executes the command.
     */
    protected function execute($cmd)
    {
        $process = proc_open(
            $cmd,
            [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ],
            $pipes
        );
        proc_close($process);
    }

    /**
     * Platform independent check whether a command exists.
     *
     * @param string $command Command to check
     * @return bool True if exists, false otherwise
     */
    protected function commandExists($command)
    {
        $windows = (php_uname('s') == 'Windows NT');
        $test_command = $windows ? 'where' : 'command -v';
        $file = popen("$test_command $command", 'r');
        $result = fgets($file, 255);
        return $windows ? !preg_match('#Could not find files#', $result) : !empty($result);
    }

    /**
     * Check for plugin command requirements.
     */
    protected function checkRequirements()
    {
        $prefix = '';
        if (TERMINUS_OS == 'DAR') {
            if (!$this->commandExists('brew')) {
                $message = 'Please install brew to continue.  See https://brew.sh.';
                throw new TerminusNotFoundException($message);
            }
            $prefix = $this->execute("$(brew --prefix)");
        }
        $bash_completion = "${prefix}/etc/bash_completion";
        if (!file_exists("$bash_completion")) {
            $message = "Please install bash-completion to continue.\n";
            $message .= "       macOS: brew install bash-completion\n";
            $message .= "  Arch-based: sudo pacman -S bash-completion\n";
            $message .= "Debian-based: sudo apt install bash-completion\n";
            $message .= "Redhat-based: sudo yum install bash-completion";
            throw new TerminusNotFoundException($message);
        }
        if (!$this->commandExists('composer')) {
            $message = 'Please install composer to continue.  See https://getcomposer.org/download/.';
            throw new TerminusNotFoundException($message);
        }
        if (!$this->commandExists('cgr')) {
            $message = 'Please install cgr to continue.  See https://github.com/consolidation/cgr.';
            throw new TerminusNotFoundException($message);
        }
        if (!$this->commandExists('symfony-autocomplete')) {
            $message = "Please install symfony-autocomplete to continue.\n";
            $message .= "Install with 'cgr bamarni/symfony-console-autocomplete'.\n";
            $message .= "Make sure '\$HOME/.config/composer/vendor/bin' is in your PATH.";
            throw new TerminusNotFoundException($message);
        }
    }
}
