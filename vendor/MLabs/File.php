<?php
namespace MLabs;

class File {
    
    /**
     * moves a file from to
     * it uses unix command line tool mv
     * 
     * @param string $file
     * @param string $to
     */
    public static function move($file, $to) { exec("mv $file $to"); }
    
    /**
     * delete a file
     * it uses unix command line tool rm
     * 
     * @param string $file
     */
    public static function delete($file) { exec("rm $file"); }
    
    /**
     * copy a file
     * it uses unix command line tool cp
     * 
     * @param string $file
     * @param string $to
     */
    public static function copy($file, $to) { exec("cp $file $to"); }
    
    /**
     * delete a folder recursivly
     * it uses unix command line tool rm with option -R
     * 
     * @param string $folder
     */
    public static function deleteFolder($folder) { exec("rm $folder/ -R"); }
    
    /**
     * add some content to file
     * 
     * @param string $filename
     * @param string $content
     * @param string $after is string given $content is added in a new line after $after string in file
     */
    public static function addContent($filename, $content, $after = null) {
        if(!is_null($after)) self::editContent($filename, $content, $after);
        else {
            // append content to file
            file_put_contents($filename, $content.PHP_EOL, FILE_APPEND);

            Installer::getComposerEvent()->getIO()->write(":: added $content to $filename");
        }
    }
    
    /**
     * replace content with other content
     * 
     * @param string $filename
     * @param string $search
     * @param string $replace
     */
    public static function replaceContent($filename, $search, $replace) {
        self::editContent($filename, $replace, $search, true);
    }
    
    /**
     * file editing
     * 
     * @param string $filename
     * @param string $content
     * @param string $search
     * @param boolean $replace true if the given content should replace the $search string
     */
    public static function editContent($filename, $content, $search, $replace = false) {
        $lines      = array();
        $linesTmp   = array();
        $changed    = false;    // flag to set true when $search found in content
        
        // open file to read
        if(($fileHandle = fopen($filename, "r")) !== false) {
            // buffer every line
            while(($buffer = fgets($fileHandle)) !== false) {
                $lines[] = $buffer;
            }
            // close file
            fclose($fileHandle);
        }
        else {
            Installer::getComposerEvent()->getIO()->write(":: failure reading $filename perhaps not exists");
        }
        
        foreach($lines as $line) {
            // if searched string found
            if(strpos($line, $search) !== false) {
                // when search string should be replaced
                if($replace) {
                    $linesTmp[] = str_replace($search, $content, $line);
                    Installer::getComposerEvent()->getIO()->write(":: replaced $search with $content in $filename");
                }
                else {
                    $linesTmp[] = $line;
                    $linesTmp[] = $content.PHP_EOL;
                    Installer::getComposerEvent()->getIO()->write(":: added $content after $search to $filename");
                }
                
                $changed = true;
            } else {
                $linesTmp[] = $line;
            }
        }
        
        // has file changed
        if($changed) {
            // overwrite file with new content
            file_put_contents($filename, implode('', $linesTmp));
        }
        else {
            Installer::getComposerEvent()->getIO()->write(":: not added $content to $filename because $search not found");
        }
    }
    
}
?>
