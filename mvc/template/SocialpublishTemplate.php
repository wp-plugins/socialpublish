<?php

class SocialpublishTemplate
{
    protected $attributes;

    public function __construct($filename) {
        $this->attributes = array();
        $this->filename = $filename;
    }

    public function setAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function render() {

        set_error_handler(array("SocialpublishTemplate", "errorHandler"));

        // Make attributes accessible in the template
	    foreach ($this->attributes as $key => $value) {
	        $$key = $value;
	    }

        ob_start();

        include $this->filename;

	    $content = ob_get_contents();

        ob_end_clean();

	    restore_error_handler();

	    return $content;
    }

    public static function errorHandler($number, $message, $file, $line, $context) {
	    $types = array(
	        E_ERROR => 'Fatal Error',
	        E_WARNING => 'Warning',
	        E_PARSE => 'Parse Error',
	        E_NOTICE => 'Notice',
	        E_CORE_ERROR => 'Fatal Core Error',
	        E_CORE_WARNING => 'Core Warning',
	        E_COMPILE_ERROR => 'Compilation Error',
	        E_COMPILE_WARNING => 'Compilation Warning',
	        E_USER_ERROR => 'Triggered Error',
	        E_USER_WARNING => 'Triggered Warning',
	        E_USER_NOTICE => 'Triggered Notice',
	        E_STRICT => 'Deprecation Notice',
	        E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
	    );

		if ($number != E_NOTICE) {
	    	echo "<strong>" . (isset($types[$number]) ? $types[$number] : 'Unknown') . "</strong>: " . $message . " in file '" . $file . "' on line " . $line . "\n";
		}

	    return false;
    }
}

?>