<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class File
{
    /** --- constructor
     */
    public function __construct(User $user)
    {
        $this->setOwner($user);
        $this->setUploadedDate(new \DateTime());
        $this->folders = new ArrayCollection();
    }

    /** --- ID
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    /** --- OWNER
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="files")
     */
    private $owner;

    public function setOwner(User $user)
    {
        $this->owner = $user;
        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    /** --- NAME (name as it appears to the user)
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /** --- EXT
     * @ORM\Column(type="string", length=255)
     */
    private $ext;

    public function setExt($ext)
    {
        $this->ext = $ext;
        return $this;
    }

    public function getExt()
    {
        return $this->ext;
    }

    /** --- FULLNAME (derivative property)
     */
    public function getFullname()
    {
        return $this->getName().'.'.$this->getExt();
    }

    /** --- TYPE (derivative property)
     */
    public function getType()
    {
        switch ($this->getExt()) {
            case 'tbas':
                return 'Turtle BASIC Program';
            case 'tpas':
                return 'Turtle Pascal Program';
            case 'tpy':
                return 'Turtle Python Program';
            case 'tgx':
                return 'Precompiled Turtle Program';
            case 'doc': // fallthrough
            case 'docx':
                return 'Microsoft Word Document';
            case 'xls': // fallthrough
            case 'xlsx':
                return 'Microsoft Excel Spreadsheet';
            case 'ppt': // fallthrough
            case 'pptx':
                return 'Microsoft PowerPoint Presentation';
            case 'pdf':
                return 'Adobe Portable Document Format';
            case 'txt':
                return 'Text File';
            case 'jpg': // fallthrough
            case 'jpeg': // fallthrough
            case 'png': // fallthrough
            case 'gif': // fallthrough
            case 'tiff': // fallthrough
            case 'bmp':
                return 'Image File';
            case 'zip': // fallthrough
            case 'rar': // fallthrough
            case 'tar': // fallthrough
            case 'gzip':
                return 'Archive File';
            default:
                return '.'.$this->getExt().' File';
        }
    }

    /** --- SIZE (in bytes)
     * @ORM\Column(type="integer")
     */
    private $size;

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function getSize($formatted = false, $precision = 2)
    {
        $bytes = $this->size;
        if ($formatted) {
            $kilobyte = 1024;
            $megabyte = $kilobyte * 1024;
            if ($bytes < $kilobyte) {
                $bytes = $bytes.' B';
            } elseif ($bytes < $megabyte) {
                $bytes = round($bytes / $kilobyte, $precision).' KB';
            } else {
                $bytes = round($bytes / $megabyte, $precision).' MB';
            }
        }
        return $bytes;
    }

    /** --- UPLOADED_DATE
     * @ORM\Column(type="date")
     */
    private $uploadedDate;

    public function setUploadedDate(\DateTime $datetime)
    {
        $this->uploadedDate = $datetime;
        return $this;
    }

    public function getUploadedDate()
    {
        return $this->uploadedDate;
    }

    /** --- PUBLIC (derivative property)
     */
    public function isPublic()
    {
        return $this->getOwner()->getUsername() === 'public';
    }

    /** --- LINK (link to copy of file on OneDrive or Google Docs)
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url(message="This is not a valid URL")
     * @Assert\Regex(
     *     pattern="/^https:\/\/(1drv.ms|onedrive.live.com|docs.google.com)/",
     *     message="This must be a link to OneDrive or Google Docs"
     * )
     */
    private $link;

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    /** --- FOLDERS
     * @ORM\ManyToMany(targetEntity="App\Entity\Folder", mappedBy="files")
     * @ORM\OrderBy({"type"="DESC", "number"="ASC"})
     */
    private $folders;

    public function addFolder(Folder $folder)
    {
        $this->folders->add($folder);
        return $this;
    }

    public function removeFolder(Folder $folder)
    {
        $this->folders->removeElement($folder);
        return $this;
    }

    public function getFolders()
    {
        return $this->folders;
    }

    /** --- FILE (temporary property for uploading the file)
     * @Assert\File(maxSize="4096k", maxSizeMessage="This file is too big")
     */
    private $file;

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    /** --- TEMPORARY PATH (used to delete the file after removing)
     */
    private $tempPath;

    public function setTempPath($path)
    {
        $this->tempPath = $path;
        return $this;
    }

    public function getTempPath()
    {
        return $this->tempPath;
    }

    /** --- FILENAME (derivative property, filename as stored on the server)
     */
    public function getFilename()
    {
        return $this->getId().'.'.$this->getExt();
    }

    /** --- UPLOAD DIRECTORY (derivative property, directory path relative to uploads directory)
     */
    public function getUploadDirectory()
    {
        return $this->getOwner()->getType().'/user'.$this->getOwner()->getId().'/';
    }

    /** --- DIRECTORY (derivative property, directory path relative to this directory)
     */
    public function getDirectory()
    {
        return __DIR__.'/../../files/uploads/'.$this->getUploadDirectory();
    }

    /** --- PATH (derivative property, file path relative to this directory)
     */
    public function getPath()
    {
        return $this->getDirectory().$this->getFilename();
    }

    /** --- IS PROGRAM (derivative property, true if file is a Turtle program)
     */
    public function isProgram()
    {
        $exts = ['tbas', 'tpas', 'tpy', 'tgx'];
        if ($this->getExt()) {
            return in_array($this->getExt(), $exts);
        }
        if ($this->getFile()) {
            $nameparts = pathinfo($this->getFile()->getClientOriginalName());
            return in_array($nameparts['extension'], $exts);
        }
        return false;
    }

    /** --- USER CAN VIEW (permissions check)
     */
    public function userCanView(?User $user)
    {
        if ($this->getOwner()->getUsername() == 'public') {
            return true; // everyone can view public files
        }
        if ($user === null) {
            return false; // must be logged on to view
        }
        if ($this->getOwner() == $user) {
            return true; // everyone can view their own files
        }
        if ($user->getType() === 'teacher') {
            foreach ($user->getCourses() as $course) {
                if ($course->getStudents()->contains($this->getOwner())) {
                    return true; // teachers can view their students' files
                }
            }
            return false; // teachers can't view anything else
        }
        if ($user->getType() === 'student') {
            foreach ($this->getFolders() as $folder) {
                if ($user->getSubscriptions()->contains($folder->getCourse())) {
                    return true; // students can view files associated with their subscriptions
                }
            }
            return false; // students can't view anything else
        }
        return false;
    }

    /** set name, extension, and size before persisting
     * @ORM\PrePersist()
     */
    public function preUpload()
    {
        if ($this->getFile() === null) {
            return;
        }
        $nameparts = pathinfo($this->getFile()->getClientOriginalName());
        $this->setName($nameparts['filename']);
        $this->setExt($nameparts['extension']);
        $this->setSize($this->getFile()->getSize());
    }

    /** upload after persisting
     * @ORM\PostPersist()
     */
    public function upload()
    {
        if ($this->getFile() === null) {
            return;
        }
        $this->getFile()->move($this->getDirectory(), $this->getFilename());
        $this->setFile(null);
    }

    /** temporarily save the path before removing (for deleting the file afterwards)
     * @ORM\PreRemove()
     */
    public function savePath()
    {
        $this->setTempPath($this->getPath());
    }

    /** delete file after removing the entity
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getTempPath();
        if ($file) {
            unlink($file);
        }
    }
}
