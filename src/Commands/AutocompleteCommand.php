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
        $prefix = (TERMINUS_OS == 'DAR') ? '\$(brew --prefix)' : '';
        $bashrc = (TERMINUS_OS == 'DAR') ? '.bash_profile' : '.bashrc';
        $message = "To complete the installation, paste in the terminal the following:" . PHP_EOL;
        $message .= PHP_EOL . "echo 'source ${prefix}/etc/bash_completion' >> ~/${bashrc}" . PHP_EOL;
        $terminus_autocomplete = getenv('HOME') . '/.terminus-autocomplete';
        if (file_exists($terminus_autocomplete)) {
            $message .= "echo 'source ~/.terminus-autocomplete' >> ~/${bashrc}" . PHP_EOL;
        }
        $this->log()->notice($message);
        $this->update();
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
        $message = PHP_EOL . "All requirements are installed.  Type 'terminus autocomplete:install' to complete." . PHP_EOL;
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
        $message = PHP_EOL . "To test if autocomplete is working, type 'terminus auto<TAB>' and it should expand to 'terminus autocomplete:'." . PHP_EOL;
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
        $this->pantheon_site_environments();
        $bashrc = (TERMINUS_OS == 'DAR') ? '.bash_profile' : '.bashrc';
        $message = "To complete the update, execute the following:" . PHP_EOL;
        $message .= PHP_EOL . "source ~/${bashrc}" . PHP_EOL;
        $this->log()->notice($message);
        $this->test();
    }

    /**
     * Add Pantheon site environments to autocomplete.
     */
    protected function pantheon_site_environments()
    {
        $pantheon_aliases = getenv('HOME') . '/.drush/site-aliases/pantheon.aliases.drushrc.php';
        if (!file_exists($pantheon_aliases)) {
            $message = PHP_EOL . "Optional: Install Drush site aliases to add Pantheon site environments to autocomplete." . PHP_EOL;
            $message .= "See https://pantheon.io/docs/drush/#download-drush-aliases-locally for more information." . PHP_EOL;
            $this->log()->notice($message);
        } else {
            if (!$this->commandExists('drush')) {
                $message = "Please install Drush to add Pantheon site environments to autocomplete. Run 'cgr drush/drush' to install." . PHP_EOL;
                $message .= "See http://docs.drush.org/en/master/install/ for alternative ways to install.";
                throw new TerminusNotFoundException($message);
            }
            $sites = shell_exec("drush sa | grep @pantheon. | cut -d'.' -f2,3 | xargs");
            $terminus_autocomplete = getenv('HOME') . '/.terminus-autocomplete';
            $lines = file($terminus_autocomplete, FILE_IGNORE_NEW_LINES);
            $line = shell_exec("rgrep -n '^}' ${terminus_autocomplete} | cut -d':' -f1");
            $line = trim(preg_replace('/[\n|\r]/', '', $line)) - 1;
            $lines[$line] = "    prev=\${COMP_WORDS[COMP_CWORD-1]}";
            $lines[$line + 1] = "    if [[ \$prev == \"drush\" ]]; then";
            $lines[$line + 2] = "        sites=\"${sites}\"";
            $lines[$line + 3] = "        COMPREPLY=(\$(compgen -W \"\${sites}\" -- \${cur}))";
            $lines[$line + 4] = "        return 0";
            $lines[$line + 5] = "    fi";
            $lines[$line + 6] = "}";
            $lines[$line + 7] = "complete -o default -F _terminus terminus";
            file_put_contents($terminus_autocomplete, implode(PHP_EOL, $lines));
            $message = "Site environments have been added to autocomplete.";
            $this->log()->notice($message);
        }
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
     * @param  string $command Command to check
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
            $prefix = shell_exec("$(brew --prefix)");
            $prefix = trim(preg_replace('/[\n|\r]/', '', $prefix));
        }
        $bash_completion = "${prefix}/etc/bash_completion";
        if (!file_exists("$bash_completion")) {
            $message = "Please install bash-completion to continue." . PHP_EOL;
            $message .= "       MacOS: brew install bash-completion" . PHP_EOL;
            $message .= "  Arch-based: sudo pacman -S bash-completion" . PHP_EOL;
            $message .= "Debian-based: sudo apt install bash-completion" . PHP_EOL;
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
            $message = "Please install symfony-autocomplete to continue." . PHP_EOL;
            $message .= "Install with 'cgr bamarni/symfony-console-autocomplete'." . PHP_EOL;
            $message .= "Make sure '\$HOME/.config/composer/vendor/bin' is in your PATH.";
            throw new TerminusNotFoundException($message);
        }
    }
}
