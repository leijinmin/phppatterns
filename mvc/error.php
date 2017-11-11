<?php
        return( "<p><strong>Error number:</strong> <span>" . $errno .  "</span></p>"
                . "<p><strong>Error message:</strong> <span>" . $errstr . "</span></p>"
                . "<p><strong>Error file:</strong> <span>" . $errfile . "</span></p>"
                . "<p><strong>Error line number:</strong> <span>" . $errline . "</span></p>"
                . "<p><a href=\"".$_SERVER['HTTP_REFERER']."\">Return to the previous page</a></p>");  
?>