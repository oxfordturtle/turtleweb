<?php

namespace App\Model\AddFile;

class AddFile
{
    /** --- FILE
     */
    private $file;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(File $file)
    {
        $this->file = $file;
        return $this;
    }
}
