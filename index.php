<?php
# @name         Lychee-FlashAir
# @author       Michael Procter

if (!defined('LYCHEE')) exit('Error: Direct access is not allowed!');

class FlashAirPlugin implements SplObserver {

    private $database = null;
    private $settings = null;

    private $flashAir = 'doxieflash';
    private $syncDir = '/DCIM/100DOXIE';

    private $importedFiles = null;

    public function __construct($database, $settings) {
        $this->database = $database;
        $this->settings = $settings;
        return true;
    }

    public function update(\SplSubject $subject) {
        if ($subject->action==='Import::server:before') {
            if ($this->importedFiles !== null) {
                Log::notice($this->database, __METHOD__, __LINE__, 'imported files not null!');
            }
            $this->importedFiles = array();

            if ($subject->args[1].'/' !== LYCHEE_UPLOADS_IMPORT) {
                # Attempts to import from non-default directory should proceed,
                # but without attempting to download files into a user-specified
                # location first!
                return true;
            }

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
                        $srcFilename = $fields[0] . '/' . $fields[1];
                        $dstFilename = $subject->args[1] . '/' . $fields[1];

                        Log::notice($this->database, __METHOD__, __LINE__, 'retrieving: ' . $srcFilename);
                        file_put_contents($dstFilename, fopen('http://' . $this->flashAir . '/' . $srcFilename, 'r'));

                        $this->importedFiles[] = array('src' => $srcFilename, 'dst' => $dstFilename);
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
        else if ($subject->action==='Import::server:after') {
            foreach($this->importedFiles as $filenames) {
                Log::notice($this->database, __METHOD__, __LINE__, 'deleting: ' . $filenames['src']);

                # Delete image from the FlashAir card and also our import directory
                file_get_contents('http://' . $this->flashAir . '/upload.cgi?DEL=' . $filenames['src']);
                unlink($filenames['dst']);
            }
            $this->importedFiles = null;
    
            return true;
	}

        return false;
    }
}

# Register your plugin
$plugins->attach(new FlashAirPlugin($database, $settings));
