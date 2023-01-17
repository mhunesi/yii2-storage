<?php


namespace mhunesi\storage\filesystem;

use League\Flysystem\Adapter\NullAdapter;

/**
 * NullFilesystem
*/
class NullFilesystem extends Filesystem
{
    public function getTitle()
    {
        return 'Null/Test';
    }

    public function getDescription()
    {
        return 'Null File System';
    }

    /**
     * @return NullAdapter
     */
    protected function prepareAdapter()
    {
        return new NullAdapter();
    }

    protected function getUrl($path, $options = [])
    {
        return null;
    }
}
