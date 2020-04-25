<?php
namespace Berryqr;

/**
 * This is the DirWalker class.
 *
 * It's responsible for traversing a directory.
 */
class DirWalker {
    private $directories = [];

    /**
     * Walk the root directory. By default the walk will start from the deepest
     * directory then work its way up. Set $topdown true to walk from the top
     * directory then down.
     *
     * @param string $dir
     * @param boolean $topdown
     * @throws \Exception
     * @return void
     */
    public function __construct($dir, $topdown=false)
    {
        if(!is_dir(realpath($dir))) {
            throw new \Exception("The directory '$dir' does not exist!");
        }

        // Populate $this->directories.
        $this->find_directories(realpath($dir));

        // Sort the $directories according to the value, which is the depth.
        asort($this->directories);

        // By default traverse the directory from the greatest depth up.
        if(!$topdown) {
            $this->directories = array_reverse($this->directories);
        }

        // At this point $this->directories should hold all the directories to
        // be traversed. It should also be sorted top to bottom or bottom to top.
    }

    /**
     * This method will populate the array $this->directories with all the
     * directories found under the root. This is not a public method. It's used
     * during object construction.
     *
     * @param string $dir
     * @return void
     */
    private function find_directories($dir)
    {
        $this->directories[$dir] = substr_count($dir, '/');

        // Open the current working directory.
        $handle = opendir($dir);

        // Loop the directory. Call the $callback function.
        while (false !== ($entry = readdir($handle))) {
            // Skip dot directories.
            if ($entry === '.' || $entry === '..') continue;

            // Get the absolute path.
            $node = sprintf("%s%s%s", $dir, DIRECTORY_SEPARATOR, $entry);

            // Recursive call to dive into the child folder.
            if(is_dir($node)) {
                $this->find_directories($node);
            }
        }

        // Close up shop.
        closedir($handle);
    }

    public function getDirectories()
    {
        return array_keys($this->directories);
    }

    /**
     * This method will call the $callback function/method passing the file or
     * directory node to $callback. The $callback must accept a \SplFileInfo
     * object.
     *
     * @param callback $callback
     * @return void
     */
    public function walk($callback)
    {
        foreach($this->directories as $dir => $depth) {
            // Callback with the directory node.
            $callback(new \SplFileInfo($dir));

            $handle = opendir($dir);

            // Loop the directory. Call the $callback function.
            while (false !== ($entry = readdir($handle))) {
                // Skip dot directories.
                if ($entry === '.' || $entry === '..') continue;

                // Get the absolute path.
                $node = sprintf("%s%s%s", $dir, DIRECTORY_SEPARATOR, $entry);

                // We can skip directories since we are already looping thru
                // $this->directories.
                if(is_dir($node)) {
                    continue;
                }

                // This is the meat and potatoes.
                $callback( new \SplFileInfo($node) );
            }

            closedir($handle);
        }
    }
}
