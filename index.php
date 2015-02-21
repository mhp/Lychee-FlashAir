<?php
# @name         Lychee-FlashAir
# @author       Michael Procter

if (!defined('LYCHEE')) exit('Error: Direct access is not allowed!');

class FlashAirPlugin implements SplObserver {

    private $database = null;
    private $settings = null;

    private $flashAir = 'doxieflash';
    private $syncDir = '/DCIM/100DOXIE';

    public function __construct($database, $settings) {
        $this->database = $database;
        $this->settings = $settings;
        return true;
    }

    public function update(\SplSubject $subject) {
        if ($subject->action!=='Import::server:before') return false;

        $files = file_get_contents('http://' . $this->flashAir . '/command.cgi?op=100&DIR=' . $this->syncDir);

        if ($files)
        {
            $line = strtok($files, "\r\n");
            while ($line !== false)
            {
                $fields = explode(',', $line);
                if (count($fields) == 6)
                {
                  # Download the file into the import directory
                  Log::notice($this->database, __METHOD__, __LINE__, 'retrieving: ' . $fields[0] . '/' . $fields[1]);
                  file_put_contents($subject->args[1] . '/' . $fields[1], fopen('http://' . $this->flashAir . '/' . $fields[0] . '/' . $fields[1], 'r'));

                  # Now delete it from the FlashAir card
                  # We don't really mind if this fails, as we are just trying to avoid multiple uploads
                  Log::notice($this->database, __METHOD__, __LINE__, 'deleting: ' . $fields[0] . '/' . $fields[1]);
                  file_get_contents('http://' . $this->flashAir . '/upload.cgi?DEL=' . $fields[0] . '/' . $fields[1]);

                }
                $line = strtok("\r\n");
            }
        }
        else
        {
            Log::error($this->database, __METHOD__, __LINE__, 'FlashAir card not found, or not readable');
        }

        return true;
    }
}

# Register your plugin
$plugins->attach(new FlashAirPlugin($database, $settings));
