<?php

namespace FluentForm\App;

use Composer\Script\Event;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ComposerScript
{
    public static function postInstall(Event $event)
    {
        static::postUpdate($event);
    }

    public static function postUpdate(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $composerJson = json_decode(file_get_contents($vendorDir . '/../composer.json'), true);
        $namespace = $composerJson['extra']['wpfluent']['namespace']['current'];

        if (!$namespace) {
            throw new InvalidArgumentException("Namespace not set in composer.json file.");
        }

        $itr = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $vendorDir.'/wpfluent/framework/src/', RecursiveDirectoryIterator::SKIP_DOTS
        ), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($itr as $file) {
            if ($file->isDir()) { 
                continue;
            }

            $fileName = $file->getPathname();

            $content = file_get_contents($fileName);
            $content = str_replace(
                'WPFluent\\',
                $namespace . '\\Framework\\',
                $content
            );

            file_put_contents($fileName, $content);
        }
    }
}
