<?php

namespace Idephix\Console\Descriptor;

use Symfony\Component\Console\Application as SymfonyApp;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Descriptor\Descriptor;
use Symfony\Component\Console\Descriptor\TextDescriptor as SymfonyTextDescriptior;
use Symfony\Component\Console\Descriptor\ApplicationDescription;

class TextDescriptor extends SymfonyTextDescriptior
{
    /**
     * {@inheritdoc}
     */
    protected function describeApplication(SymfonyApp $application, array $options = array())
    {
        $describedNamespace = isset($options['namespace']) ? $options['namespace'] : null;
        $description = new ApplicationDescription($application, $describedNamespace);

        if (isset($options['raw_text']) && $options['raw_text']) {
            $width = $this->getColumnWidth($description->getCommands());

            foreach ($description->getCommands() as $command) {
                $this->writeText(sprintf("%-${width}s %s", $command->getName(), $command->getDescription()), $options);
                $this->writeText("\n");
            }
        } else {
            $width = $this->getColumnWidth($description->getCommands());

            $this->writeText($application->getHelp(), $options);
            $this->writeText("\n\n");

            if ($describedNamespace) {
                $this->writeText(sprintf("<comment>Available commands for the \"%s\" namespace:</comment>", $describedNamespace), $options);
            } else {
                $this->writeText('<comment>Available commands:</comment>', $options);
            }

            $default_cmds = array();
            $user_cmds = array();

            // add commands by namespace
            foreach ($description->getNamespaces() as $namespace) {
                foreach ($namespace['commands'] as $name) {
                    if (in_array($name, array('help', 'list', 'initFile', 'selfupdate'))) {
                        $default_cmds[] = $name;
                    } else {
                        $user_cmds[] = $name;
                    }
                }
            }

            $this->writeText("\n");

            foreach ($default_cmds as $name) {
                $this->writeText(sprintf("  <info>%-${width}s</info> %s", $name, $description->getCommand($name)->getDescription()), $options);
            $this->writeText("\n");

            }

            $this->writeText("\n");
            $this->writeText('<comment>User tasks:</comment>', $options);
            $this->writeText("\n");

            foreach ($user_cmds as $name) {
                $this->writeText(sprintf("  <info>%-${width}s</info> %s", $name, $description->getCommand($name)->getDescription()), $options);
                $this->writeText("\n");

            }
        }
    }

    /**
     * {@inheritdoc}
     */
    private function writeText($content, array $options = array())
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            isset($options['raw_output']) ? !$options['raw_output'] : true
        );
    }

    /**
     * Formats input option/argument default value.
     *
     * @param mixed $default
     *
     * @return string
     */
    private function formatDefaultValue($default)
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            return str_replace('\/', '/', json_encode($default));
        }

        return json_encode($default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param Command[] $commands
     *
     * @return int
     */
    private function getColumnWidth(array $commands)
    {
        $width = 0;
        foreach ($commands as $command) {
            $width = strlen($command->getName()) > $width ? strlen($command->getName()) : $width;
        }

        return $width + 2;
    }
}
