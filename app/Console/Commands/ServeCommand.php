<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

class ServeCommand extends BaseServeCommand
{
    /**
     * @return list<string>
     */
    protected function serverCommand()
    {
        $command = parent::serverCommand();

        array_splice($command, 1, 0, [
            '-d', 'upload_max_filesize=64M',
            '-d', 'post_max_size=80M',
            '-d', 'memory_limit=512M',
        ]);

        return $command;
    }
}
