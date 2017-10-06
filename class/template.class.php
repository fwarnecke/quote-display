<?php
class Template implements TemplateInterface {

    protected $tmplDir = ROOT_DIR .'/templates/';
    protected $fileExtension = '.html';

    protected $leftDelimiter = '{';
    protected $rightDelimiter = '}';
    protected $replacePattern;

    protected $leftLoopDelimiter = '~';
    protected $rightLoopDelimiter = '~';
    protected $shortLoopPattern;

    protected $leftLoopContentDelimiter = '\[';
    protected $rightLoopContentDelimiter = '\]';
    protected $loopPattern;

    protected $includeKey = 'include:';
    protected $includePattern;

    protected $tmplFilePath = '';
    protected $tmplName = '';
    protected $template = '';

    protected $loops = array();

    
    /**
     * creates regex-patterns based on pre definded delimiters, and prepares template
     */
    public function __construct( String $fileName) {
        $this->replacePattern = '/' .$this->leftDelimiter
                                .'[a-zA-Z0-9_-]*'
                                .$this->rightDelimiter .'/';
        
        $this->loopPattern = '/' .$this->leftLoopDelimiter 
                    .'([a-zA-Z0-9_-]*)'
                    .$this->rightLoopDelimiter 
                    .$this->leftLoopContentDelimiter
                    .'([\W\w]*?)' 
                    .$this->rightLoopContentDelimiter .'/';

        $this->shortLoopPattern = '/' .$this->leftLoopDelimiter 
                    .'[a-zA-Z0-9_-]*'
                    .$this->rightLoopDelimiter .'/';

        $this->includePattern = '/' .$this->leftDelimiter
                                .'include:[a-zA-Z0-9_-]*'
                                .$this->rightDelimiter .'/';

        $this->loadFile($fileName);

        $this->getLoops();
    }

    /**
     * loads content of template file
     *
     * @throws Exception if file does not exist or is not given
     */
    protected function loadFile( String $fileName) {
        if ( !empty( $fileName)) {

            $this->tmplFilePath = $this->tmplDir .$fileName .$this->fileExtension;
            $this->tmplName = $fileName;

            if ( file_exists( $this->tmplFilePath)) {
                $this->template = file_get_contents( $this->tmplFilePath);
            } else {
                throw new Exception( 'File: ' .$fileName .' does not exist at ' .$this->tmplFilePath .'.', 4);
            }
        } else {
            throw new Exception( 'No file given.', 5);
        }
    }

    /**
     * searches for loop structures in template and saves them in array for later use
     */
    protected function getLoops() {
        if ( preg_match_all( $this->loopPattern, $this->template, $matches)) {
            for ($i=0; $i < (( count( $matches, 1)/3) -1); $i++) { 
                $this->loops[$matches[1][$i]] = $matches[2][$i];
                $this->template = str_replace( $matches[0][$i], '~' .$matches[1][$i] .'~', $this->template);
            }
        }
    }

    /**
     * basic replace a placeholder function
     * @var String  $placeholder    the placeholder which should be replaced
     * @var String  $text           the text with which the placeholder should be replaced
     * @throws Exception if you try to replace a include command or if placeholder was not found
     */
    public function replace( String $placeholder, String $text) {
        if ( strpos( $placeholder, $this->includeKey)) {
            throw new Exception( 'Please do not replace includes.', 6);
        }

        $placeholder = $this->leftDelimiter .$placeholder .$this->rightDelimiter;

        if ( strpos( $this->template, $placeholder)) {
            do {
                $this->template = str_replace( $placeholder, $text, $this->template);
            } while ( strpos( $this->template, $placeholder));
        } else {
            throw new Exception( 'Placeholder <b>' .$placeholder .'</b> not found.', 7);
        }
    }

    /**
     * adds the content of a loop one time to the template and replaces its placeholders
     * 
     * @var String  $loopKey        name of loop
     * @var Array   $loopContent    associative array of replacements for loop: placeholder => value
     * @throws Exception if loop or placeholder not found
     */
    public function addLoopIteration( String $loopKey, array $loopContent) {
        if ( array_key_exists( $loopKey, $this->loops)) {
            $loopIteration = $this->loops[$loopKey];
            foreach ($loopContent as $key => $value) {
                $key = $this->leftDelimiter .$key .$this->rightDelimiter;

                if ( strpos( $loopIteration, $key)) {
                    do {
                        $loopIteration = str_replace( $key, $value, $loopIteration);
                    } while ( strpos( $loopIteration, $key));
                } else {
                    throw new Exception( 'Placeholder <b>' .$key .'</b> not found in <b>' .$loopKey .'</b>.', 8);
                }

            }

            $replaceKey = $this->leftLoopDelimiter .$loopKey .$this->rightLoopDelimiter;
            $newContent = $loopIteration ."\n" .$replaceKey;

            $this->template = str_replace( $replaceKey, $newContent, $this->template);
        } else {
            throw new Exception( 'Loop ' .$loopKey .' does not exist.', 9);
        }
    }

    /**
     * includes another template used for sidebars, footer or similiar
     */
    public function include( String $type, TemplateInterface $includeObj = null) {
        if ($includeObj == null) {
            $includeObj = new Template($type);
        }

        $includeC = $this->leftDelimiter .$this->includeKey .$type .$this->rightDelimiter;

        if ( strpos( $this->template, $includeC)) {
            $this->template = str_replace( $includeC, $includeObj->getTemplate(), $this->template);
        }
    }

    // public function autoInclude() {
    //     if ( preg_match_all( $this->includePattern, $this->template, $matches)) {
    //         foreach ( $matches[0] as $value) {
    //             $value = str_replace( '{include:', '', $value);
    //             $value = str_replace( '}', '', $value);
    //             include($value);
    //         }
    //     }
    // }

    public function getTemplate(): String {
        return $this->template;
    }

    public function getReplacePattern(): String {
        return $this->replacePattern;
    }

    /**
     * checks if template is still missing replacements and if not removes loop commands and prints it to the screen
     * 
     * @throws Exception if template is still missing replacements
     */
    public function showSite() {
        //check if ready
        if ( preg_match_all( $this->includePattern, $this->template, $matches)) {
            $missing = '';
            foreach ($matches[0] as $value) {
                $missing .= $value .'<br>';
            }
            throw new Exception( 'Template not finished yet. Still <b>missing includes</b> for: <br><b>' .$missing .'</b>', 10);
        } elseif ( preg_match_all( $this->replacePattern, $this->template, $matches)) {
            $missing = '';
            foreach ($matches[0] as $value) {
                $missing .= $value .'<br>';
            }
            throw new Exception('Template not finished yet. Still <b>missing text</b> for: <br><b>' .$missing .'</b>', 10);
        } else {
            //loop removal
            $this->template = preg_replace( $this->shortLoopPattern, '', $this->template);
            
            echo $this->template;
        }

    }

    public function forceSite() {
        echo $this->template;
    }
}
